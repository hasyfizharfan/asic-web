<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendanceRequest;
use App\Models\Attendance;
use App\Models\EventSession;
use App\Models\Registration;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['id_registration', 'qr_token'],
                properties: [
                    new OA\Property(property: 'id_registration', type: 'integer', example: 1),
                    new OA\Property(property: 'qr_token', type: 'string', example: 'ASIC-SESSION-1-ABCD123456')
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
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Akses ditolak, invoice belum terverifikasi, token tidak valid, atau presensi di luar rentang waktu sesi'),
            new OA\Response(response: 422, description: 'Input tidak valid')
        ]
    )]
    public function recordAttendance(StoreAttendanceRequest $request, int $id_session): JsonResponse
    {
        $session = EventSession::with('event')->findOrFail($id_session);
        $registration = Registration::with(['participant.userAccount', 'invoice'])
            ->findOrFail($request->integer('id_registration'));

        /** @var \App\Models\UserAccount $user */
        $user = Auth::user();

        $isPanitia = $user->hasRole([Role::ROLE_ADMIN, Role::ROLE_SEKRETARIAT]);
        if (!$isPanitia && $registration->participant?->id_user !== $user->id_user) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk mencatat kehadiran pendaftaran ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        if (!$registration->invoice || $registration->invoice->status_pembayaran !== 'Terverifikasi') {
            return response()->json([
                'success' => false,
                'message' => 'Presensi ditolak: invoice belum tersedia atau status pembayaran belum Terverifikasi.',
            ], Response::HTTP_FORBIDDEN);
        }

        if (!$isPanitia) {
            if (!is_string($session->qr_token) || $session->qr_token === '' || !hash_equals($session->qr_token, (string) $request->input('qr_token'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR token tidak valid untuk sesi ini.',
                ], Response::HTTP_FORBIDDEN);
            }

            $now = now();
            if (!$session->waktu_selesai || $now->lt($session->waktu) || $now->gt($session->waktu_selesai)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Presensi hanya dapat dilakukan selama rentang waktu sesi berlangsung.',
                ], Response::HTTP_FORBIDDEN);
            }
        }

        $attendance = Attendance::firstOrNew([
            'id_registration' => $registration->id_registration,
            'id_session' => $session->id_session,
        ]);

        $isNewCheckIn = !$attendance->exists || $attendance->status_kehadiran !== 'Hadir';

        $attendance->status_kehadiran = 'Hadir';
        if (!$attendance->exists || !$attendance->status_presentasi) {
            $attendance->status_presentasi = 'Belum';
        }
        if (!$attendance->tanggal_generate) {
            $attendance->tanggal_generate = now();
        }
        if (!$attendance->tanggal_check_in) {
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
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['status_presentasi'],
                properties: [
                    new OA\Property(property: 'status_presentasi', type: 'string', enum: ['Sudah'], example: 'Sudah')
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
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Input tidak valid')
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
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Riwayat kehadiran berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'id_registration', type: 'integer'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object'))
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Akses ditolak')
        ]
    )]
    public function participantSessions(int $id_registration): JsonResponse
    {
        $registration = Registration::with(['participant.userAccount'])->findOrFail($id_registration);

        /** @var \App\Models\UserAccount $user */
        $user = Auth::user();

        if (
            !$user->hasRole([Role::ROLE_ADMIN, Role::ROLE_SEKRETARIAT, Role::ROLE_BENDAHARA]) &&
            $registration->participant?->id_user !== $user->id_user
        ) {
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
