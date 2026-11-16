<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceRequest;
use App\Models\Attendance;
use App\Models\EventSession;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\UserAccount;
use App\Support\PermissionMatrix;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class AttendanceController extends Controller
{
    /**
     * Merekam kehadiran peserta ke dalam suatu Sesi Acara (QR Sesi) (KF-12, KF-14 & Revisi 1).
     */
    #[OA\Post(
        path: '/api/sessions/{id_session}/attendance',
        summary: 'Catat Kehadiran Peserta pada Sesi',
        description: 'Mencatat presensi kehadiran peserta ke sesi kegiatan tertentu melalui pemindaian QR code sesi.',
        tags: ['Kehadiran'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id_session',
                in: 'path',
                required: true,
                description: 'ID Sesi Acara',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['id_registration', 'qr_token'],
                properties: [
                    new OA\Property(property: 'id_registration', type: 'integer', example: 1),
                    new OA\Property(property: 'qr_token', type: 'string', example: 'ASIC-SESSION-1-ABCD123456'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Kehadiran berhasil dicatat',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Akses ditolak, invoice belum terverifikasi, token tidak valid, atau presensi di luar rentang waktu sesi'),
            new OA\Response(response: 422, description: 'Input tidak valid'),
        ]
    )]
    public function recordAttendance(StoreAttendanceRequest $request, int $id_session): JsonResponse
    {
        $session = EventSession::with('event')->findOrFail($id_session);
        $registration = Registration::with(['participant.userAccount', 'invoice'])
            ->findOrFail($request->integer('id_registration'));

        /** @var UserAccount $user */
        $user = Auth::user();

        if (! $registration->invoice || $registration->invoice->status_pembayaran !== 'Terverifikasi') {
            return response()->json([
                'success' => false,
                'message' => 'Presensi ditolak: invoice belum tersedia atau status pembayaran belum Terverifikasi.',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($registration->id_event !== $session->id_event) {
            return response()->json(['success' => false, 'message' => 'Pendaftaran peserta tidak terkait dengan sesi ini.'], Response::HTTP_FORBIDDEN);
        }
        if ($request->filled('qr_token') && ! hash_equals((string) $session->qr_token, (string) $request->input('qr_token'))) {
            return response()->json(['success' => false, 'message' => 'Token QR tidak cocok dengan sesi ini.'], Response::HTTP_FORBIDDEN);
        }
        if (! $this->isSessionOpen($session)) {
            return response()->json(['success' => false, 'message' => 'Presensi hanya dapat dilakukan selama jadwal sesi berlangsung.'], Response::HTTP_FORBIDDEN);
        }

        $attendance = Attendance::firstOrNew([
            'id_registration' => $registration->id_registration,
            'id_session' => $session->id_session,
        ]);

        $isNewCheckIn = ! $attendance->exists || $attendance->status_kehadiran !== 'Hadir';

        $attendance->status_kehadiran = 'Hadir';
        if (! $attendance->exists || ! $attendance->status_presentasi) {
            $attendance->status_presentasi = 'Belum';
        }
        if (! $attendance->tanggal_generate) {
            $attendance->tanggal_generate = now();
        }
        if (! $attendance->tanggal_check_in) {
            $attendance->tanggal_check_in = now();
        }

        $attendance->save();

        return response()->json([
            'success' => true,
            'message' => $isNewCheckIn
                ? 'Check-in kehadiran sesi berhasil dicatat.'
                : 'Data presensi sesi berhasil diperbarui.',
            'data' => [
                'id_attendance' => $attendance->id_attendance,
                'id_session' => $session->id_session,
                'nama_sesi' => $session->nama_sesi,
                'id_registration' => $attendance->id_registration,
                'status_kehadiran' => $attendance->status_kehadiran,
                'status_presentasi' => $attendance->status_presentasi,
                'tanggal_check_in' => $attendance->tanggal_check_in,
                'peserta' => $registration->participant?->full_name,
            ],
        ], Response::HTTP_OK);
    }

    #[OA\Get(
        path: '/api/registrations/{id_registration}/attendance-qr',
        summary: 'Buat atau Tampilkan Token QR Kehadiran Pendaftaran',
        description: 'Mengembalikan token QR milik pendaftaran peserta Luring, Terdaftar, dengan pembayaran Terverifikasi. Peserta Daring ditolak 403 (kehadiran lewat QR sesi).',
        tags: ['Kehadiran'],
        security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_registration', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Token QR pendaftaran tersedia'),
            new OA\Response(response: 403, description: 'Akses ditolak atau pembayaran belum terverifikasi'),
            new OA\Response(response: 404, description: 'Pendaftaran tidak ditemukan'),
        ]
    )]
    public function attendanceQr(int $id_registration): JsonResponse
    {
        $registration = Registration::with(['participant', 'invoice'])->findOrFail($id_registration);
        $user = Auth::user();
        $isOwner = $registration->participant?->id_user === $user->id_user;
        $canManageAttendance = app(PermissionMatrix::class)->allows($user, 'scan_attendance_qr');
        abort_unless($isOwner || $canManageAttendance, Response::HTTP_FORBIDDEN);

        // KF-13: QR kehadiran pribadi hanya untuk peserta Luring, Terdaftar, dan Terverifikasi.
        // Peserta Daring mencatat kehadiran lewat QR sesi (submitSessionQr).
        if ($registration->registration_status !== 'Terdaftar') {
            return response()->json(['success' => false, 'message' => __('asic-api.attendance.not_registered')], Response::HTTP_FORBIDDEN);
        }
        if (! $registration->isLuring()) {
            return response()->json(['success' => false, 'message' => __('asic-api.attendance.qr_offline_only')], Response::HTTP_FORBIDDEN);
        }
        if (! $registration->invoice || $registration->invoice->status_pembayaran !== 'Terverifikasi') {
            return response()->json(['success' => false, 'message' => __('asic-api.attendance.payment_unverified')], Response::HTTP_FORBIDDEN);
        }

        if (! $registration->qr_attendance_token) {
            $registration->qr_attendance_token = Str::random(64);
            $registration->save();
        }

        return response()->json(['success' => true, 'data' => ['qr_token' => $registration->qr_attendance_token]], Response::HTTP_OK);
    }

    #[OA\Post(
        path: '/api/sessions/{id_session}/attendance/scan',
        summary: 'Pindai QR Kehadiran Peserta',
        description: 'Check-in panitia bagi peserta Daring maupun Luring dengan pembayaran Terverifikasi.',
        tags: ['Kehadiran'],
        security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_session', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['qr_token'], properties: [new OA\Property(property: 'qr_token', type: 'string')])),
        responses: [new OA\Response(response: 200, description: 'Check-in berhasil'), new OA\Response(response: 403, description: 'Akses atau syarat peserta ditolak')]
    )]
    public function scanParticipantQr(Request $request, int $id_session): JsonResponse
    {
        $validated = $request->validate(['qr_token' => ['required', 'string']]);
        $registration = Registration::where('qr_attendance_token', $validated['qr_token'])->firstOrFail();
        // Token lama milik peserta Daring tidak lagi berlaku (KF-13).
        if (! $registration->isLuring()) {
            return response()->json(['success' => false, 'message' => __('asic-api.attendance.qr_offline_only')], Response::HTTP_FORBIDDEN);
        }

        return $this->checkInRegistration($registration, $id_session);
    }

    #[OA\Post(
        path: '/api/attendance/scan',
        summary: 'Submit Token QR Sesi oleh Peserta',
        description: 'Mencatat kehadiran peserta yang sedang login dengan token QR sesi konferensi yang aktif.',
        tags: ['Kehadiran'],
        security: [['sanctumAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['qr_token'],
                properties: [new OA\Property(property: 'qr_token', type: 'string', maxLength: 100, example: 'ASIC-SESSION-1-ABCD123456')]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Kehadiran peserta berhasil dicatat'),
            new OA\Response(response: 403, description: 'Token, kepemilikan pendaftaran, pembayaran, atau jadwal sesi tidak memenuhi syarat'),
            new OA\Response(response: 409, description: 'Peserta sudah tercatat hadir pada sesi ini'),
            new OA\Response(response: 422, description: 'Token QR tidak valid'),
        ]
    )]
    public function submitSessionQr(Request $request): JsonResponse
    {
        $validated = $request->validate(['qr_token' => ['required', 'string', 'max:100']]);
        $session = EventSession::with('event')->where('qr_token', $validated['qr_token'])->first();
        if (! $session) {
            return response()->json(['success' => false, 'message' => 'Token QR sesi tidak valid.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        if (! $this->isSessionOpen($session)) {
            return response()->json(['success' => false, 'message' => 'Sesi belum dimulai atau sudah berakhir.'], Response::HTTP_FORBIDDEN);
        }

        $participant = Participant::where('id_user', Auth::id())->first();
        if (! $participant) {
            return response()->json(['success' => false, 'message' => 'Akun ini tidak memiliki profil peserta.'], Response::HTTP_FORBIDDEN);
        }

        $registration = Registration::with('invoice')
            ->where('id_participant', $participant->id_participant)
            ->where('id_event', $session->id_event)
            ->where('registration_status', 'Terdaftar')
            ->orderByDesc('registration_date')
            ->first();
        if (! $registration || ! $registration->invoice || $registration->invoice->status_pembayaran !== 'Terverifikasi') {
            return response()->json(['success' => false, 'message' => 'Pendaftaran aktif dengan pembayaran terverifikasi tidak ditemukan untuk sesi ini.'], Response::HTTP_FORBIDDEN);
        }

        $attendance = DB::transaction(function () use ($registration, $session): ?Attendance {
            Registration::whereKey($registration->id_registration)->lockForUpdate()->firstOrFail();

            if (Attendance::where('id_registration', $registration->id_registration)
                ->where('id_session', $session->id_session)->exists()) {
                return null;
            }

            return $this->saveAttendance($registration, $session);
        });

        if (! $attendance) {
            return response()->json(['success' => false, 'message' => 'Kehadiran Anda pada sesi ini sudah tercatat.'], Response::HTTP_CONFLICT);
        }

        return response()->json([
            'success' => true,
            'message' => 'Kehadiran sesi berhasil dicatat.',
            'data' => $attendance,
        ], Response::HTTP_OK);
    }

    private function checkInRegistration(Registration $registration, int $id_session): JsonResponse
    {
        $registration->loadMissing('invoice');
        if (! $registration->invoice || $registration->invoice->status_pembayaran !== 'Terverifikasi') {
            return response()->json(['success' => false, 'message' => 'QR tidak memenuhi syarat: pembayaran harus Terverifikasi.'], Response::HTTP_FORBIDDEN);
        }
        $session = EventSession::with('event')->findOrFail($id_session);
        if ($registration->id_event !== $session->id_event) {
            return response()->json(['success' => false, 'message' => 'Pendaftaran peserta tidak terkait dengan sesi ini.'], Response::HTTP_FORBIDDEN);
        }
        if (! $this->isSessionOpen($session)) {
            return response()->json(['success' => false, 'message' => 'Presensi hanya dapat dilakukan selama jadwal sesi berlangsung.'], Response::HTTP_FORBIDDEN);
        }

        $attendance = $this->saveAttendance($registration, $session);

        return response()->json(['success' => true, 'message' => 'Check-in QR peserta berhasil dicatat.', 'data' => $attendance], Response::HTTP_OK);
    }

    private function saveAttendance(Registration $registration, EventSession $session): Attendance
    {
        $attendance = Attendance::firstOrNew(['id_registration' => $registration->id_registration, 'id_session' => $session->id_session]);
        $attendance->status_kehadiran = 'Hadir';
        $attendance->status_presentasi = $attendance->status_presentasi ?: 'Belum';
        $attendance->tanggal_generate = $attendance->tanggal_generate ?: now();
        $attendance->tanggal_check_in = now();
        $attendance->save();

        return $attendance;
    }

    private function isSessionOpen(EventSession $session): bool
    {
        $now = now();
        $start = $session->waktu;

        if ($session->waktu_selesai) {
            return $start && $now->betweenIncluded($start, $session->waktu_selesai);
        }

        $event = $session->event;
        if (! $event?->tanggal_mulai || ! $event?->tanggal_selesai) {
            return false;
        }

        return $now->betweenIncluded(
            Carbon::parse($event->tanggal_mulai)->startOfDay(),
            Carbon::parse($event->tanggal_selesai)->endOfDay()
        );
    }

    /**
     * Memperbarui status presentasi peserta pada sesi tertentu (Khusus Panitia / Tim Sesi).
     */
    #[OA\Patch(
        path: '/api/attendance/{id_attendance}/presentation',
        summary: 'Perbarui Status Presentasi Peserta',
        description: 'Memperbarui status apakah peserta sudah atau belum melakukan presentasi pada sesi paralel (Sekretariat / Admin).',
        tags: ['Kehadiran'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id_attendance',
                in: 'path',
                required: true,
                description: 'ID Data Kehadiran',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status_presentasi'],
                properties: [
                    new OA\Property(property: 'status_presentasi', type: 'string', enum: ['Sudah'], example: 'Sudah'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Status presentasi berhasil diperbarui',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Input tidak valid'),
        ]
    )]
    public function updatePresentationStatus(Request $request, int $id_attendance): JsonResponse
    {
        $request->validate([
            'status_presentasi' => ['required', 'in:Sudah'],
        ], [
            'status_presentasi.required' => 'Status presentasi wajib diisi.',
            'status_presentasi.in' => 'Status presentasi hanya dapat diubah menjadi Sudah.',
        ]);

        $attendance = Attendance::with(['registration.participant', 'eventSession'])->findOrFail($id_attendance);

        $attendance->update([
            'status_presentasi' => $request->input('status_presentasi'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status presentasi peserta berhasil diperbarui.',
            'data' => [
                'id_attendance' => $attendance->id_attendance,
                'id_registration' => $attendance->id_registration,
                'peserta' => $attendance->registration?->participant?->full_name,
                'status_presentasi' => $attendance->status_presentasi,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Menampilkan riwayat kehadiran sesi bagi pendaftaran peserta tertentu.
     */
    #[OA\Get(
        path: '/api/registrations/{id_registration}/sessions',
        summary: 'Riwayat Kehadiran Sesi Peserta',
        description: 'Menampilkan daftar seluruh sesi yang telah dihadiri oleh peserta pada pendaftaran tertentu.',
        tags: ['Kehadiran'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id_registration',
                in: 'path',
                required: true,
                description: 'ID Pendaftaran Peserta',
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Riwayat kehadiran berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'id_registration', type: 'integer'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object')),
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Akses ditolak'),
        ]
    )]
    public function participantSessions(int $id_registration): JsonResponse
    {
        $registration = Registration::with(['participant.userAccount'])->findOrFail($id_registration);

        /** @var UserAccount $user */
        $user = Auth::user();

        $isOwner = $registration->participant?->id_user === $user->id_user;
        $canManageAttendance = app(PermissionMatrix::class)->allows($user, 'scan_attendance_qr');
        $canVerifyPayments = app(PermissionMatrix::class)->allows($user, 'verify_payment');

        if (! $isOwner && ! $canManageAttendance && ! $canVerifyPayments) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk melihat data presensi ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        $attendances = Attendance::with('eventSession')
            ->where('id_registration', $id_registration)
            ->get();

        return response()->json([
            'success' => true,
            'id_registration' => $id_registration,
            'data' => $attendances,
        ], Response::HTTP_OK);
    }
}
