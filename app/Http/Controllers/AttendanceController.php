<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceRequest;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\EventSession;
use App\Models\Registration;
use App\Models\UserAccount;
use App\Support\PermissionMatrix;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if (! $registration->isLuring()) {
            return response()->json(['success' => false, 'message' => 'Check-in di lokasi hanya berlaku untuk peserta Luring.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        if (! $registration->invoice || $registration->invoice->status_pembayaran !== 'Terverifikasi') {
            return response()->json([
                'success' => false,
                'message' => 'Presensi ditolak: invoice belum tersedia atau status pembayaran belum Terverifikasi.',
            ], Response::HTTP_FORBIDDEN);
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

    public function attendanceQr(int $id_registration): JsonResponse
    {
        $registration = Registration::with(['participant', 'invoice'])->findOrFail($id_registration);
        $user = Auth::user();
        $isOwner = $registration->participant?->id_user === $user->id_user;
        $canManageAttendance = app(PermissionMatrix::class)->allows($user, 'scan_attendance_qr');
        abort_unless($isOwner || $canManageAttendance, Response::HTTP_FORBIDDEN);

        if (! $registration->isLuring() || ! $registration->invoice || $registration->invoice->status_pembayaran !== 'Terverifikasi') {
            return response()->json(['success' => false, 'message' => 'QR kehadiran hanya tersedia bagi peserta Luring dengan pembayaran Terverifikasi.'], Response::HTTP_FORBIDDEN);
        }

        if (! $registration->qr_attendance_token) {
            $registration->qr_attendance_token = Str::random(64);
            $registration->save();
        }

        return response()->json(['success' => true, 'data' => ['qr_token' => $registration->qr_attendance_token]], Response::HTTP_OK);
    }

    #[OA\Post(
        path: '/api/sessions/{id_session}/attendance/scan',
        summary: 'Pindai QR Kehadiran Peserta Luring',
        description: 'Check-in Sekretariat untuk peserta Luring dengan pembayaran Terverifikasi.',
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

        return $this->checkInRegistration($registration, $id_session);
    }

    private function checkInRegistration(Registration $registration, int $id_session): JsonResponse
    {
        if (! $registration->isLuring() || ! $registration->invoice || $registration->invoice->status_pembayaran !== 'Terverifikasi') {
            return response()->json(['success' => false, 'message' => 'QR tidak memenuhi syarat: peserta harus Luring dan pembayaran Terverifikasi.'], Response::HTTP_FORBIDDEN);
        }
        $session = EventSession::findOrFail($id_session);
        $event = Event::findOrFail($session->id_event);
        if (now()->toDateString() !== $event->tanggal_mulai) {
            return response()->json(['success' => false, 'message' => 'Registrasi ulang hanya tersedia pada hari pertama kegiatan.'], Response::HTTP_FORBIDDEN);
        }
        $attendance = Attendance::firstOrNew(['id_registration' => $registration->id_registration, 'id_session' => $session->id_session]);
        $attendance->status_kehadiran = 'Hadir';
        $attendance->status_presentasi = $attendance->status_presentasi ?: 'Belum';
        $attendance->tanggal_generate = $attendance->tanggal_generate ?: now();
        $attendance->tanggal_check_in = now();
        $attendance->save();

        return response()->json(['success' => true, 'message' => 'Check-in QR peserta berhasil dicatat.', 'data' => $attendance], Response::HTTP_OK);
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
