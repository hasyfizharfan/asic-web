<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Registration;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AttendanceController extends Controller
{
    /**
     * Otomatis menerbitkan QR Code kehadiran untuk peserta luring (KF-13).
     *
     * Menyimpan token/path unik ke kolom `qr_code` pada tabel `attendance`,
     * dan menghasilkan berkas gambar QR Code di Storage public.
     */
    public function generate(int $id_registration): JsonResponse
    {
        $registration = Registration::with(['participant.userAccount', 'attendance'])
            ->findOrFail($id_registration);

        /** @var \App\Models\UserAccount $user */
        $user = Auth::user();

        // Otorisasi: Hanya pemilik pendaftaran atau Panitia (Sekretariat / Admin / Bendahara)
        if (
            !$user->hasRole([Role::ROLE_ADMIN, Role::ROLE_SEKRETARIAT, Role::ROLE_BENDAHARA]) &&
            $registration->participant?->id_user !== $user->id_user
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk menerbitkan QR Code ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        // QR Code hanya berlaku untuk peserta dengan mode kehadiran 'Luring'
        if ($registration->attendance_mode !== 'Luring') {
            return response()->json([
                'success' => false,
                'message' => 'QR Code kehadiran hanya berlaku untuk peserta dengan mode kehadiran Luring (Offline).',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Jika QR Code sudah pernah dibuat sebelumnya, kembalikan data yang ada
        if ($registration->attendance) {
            $qrCodePath = 'qrcodes/qr_' . $registration->attendance->qr_code . '.svg';
            $qrUrl = Storage::disk('public')->exists($qrCodePath)
                ? Storage::disk('public')->url($qrCodePath)
                : null;

            return response()->json([
                'success' => true,
                'message' => 'QR Code kehadiran sudah pernah diterbitkan.',
                'data' => $registration->attendance,
                'qr_image_url' => $qrUrl,
            ], Response::HTTP_OK);
        }

        // Generate string token unik untuk QR Code
        $token = 'ASIC-ATT-' . $registration->id_registration . '-' . strtoupper(Str::random(12));

        // Buat file gambar QR Code ke storage/app/public/qrcodes/
        $qrDirectory = 'qrcodes';
        $qrFileName = 'qr_' . $token . '.svg';
        $qrRelativePath = $qrDirectory . '/' . $qrFileName;

        if (class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
            $svgContent = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                ->size(300)
                ->errorCorrection('H')
                ->generate($token);
            Storage::disk('public')->put($qrRelativePath, (string) $svgContent);
        } else {
            // Fallback generator SVG QR placeholder jika package belum terpasang
            $svgContent = '<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300">'
                . '<rect width="300" height="300" fill="#ffffff"/>'
                . '<rect x="20" y="20" width="80" height="80" fill="#000000"/>'
                . '<rect x="30" y="30" width="60" height="60" fill="#ffffff"/>'
                . '<rect x="40" y="40" width="40" height="40" fill="#000000"/>'
                . '<rect x="200" y="20" width="80" height="80" fill="#000000"/>'
                . '<rect x="210" y="30" width="60" height="60" fill="#ffffff"/>'
                . '<rect x="220" y="40" width="40" height="40" fill="#000000"/>'
                . '<rect x="20" y="200" width="80" height="80" fill="#000000"/>'
                . '<rect x="30" y="210" width="60" height="60" fill="#ffffff"/>'
                . '<rect x="40" y="220" width="40" height="40" fill="#000000"/>'
                . '<text x="150" y="160" font-family="Arial" font-size="12" text-anchor="middle" fill="#333333">'
                . htmlspecialchars($token) . '</text>'
                . '</svg>';
            Storage::disk('public')->put($qrRelativePath, $svgContent);
        }

        // Simpan ke tabel attendance
        $attendance = Attendance::create([
            'id_registration' => $registration->id_registration,
            'qr_code' => $token,
            'tanggal_generate' => now(),
            'tanggal_check_in' => null,
            'status_kehadiran' => 'Tidak Hadir',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'QR Code kehadiran berhasil diterbitkan.',
            'data' => $attendance,
            'qr_image_url' => Storage::disk('public')->url($qrRelativePath),
        ], Response::HTTP_CREATED);
    }

    /**
     * Menampilkan QR Code kehadiran peserta tertentu (KF-13).
     */
    public function showQr(int $id_registration): JsonResponse
    {
        $attendance = Attendance::with([
            'registration.participant.userAccount',
            'registration.event',
        ])->where('id_registration', $id_registration)->firstOrFail();

        /** @var \App\Models\UserAccount $user */
        $user = Auth::user();

        // Otorisasi: Peserta pemilik atau panitia
        if (
            !$user->hasRole([Role::ROLE_ADMIN, Role::ROLE_SEKRETARIAT, Role::ROLE_BENDAHARA]) &&
            $attendance->registration?->participant?->id_user !== $user->id_user
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk melihat QR Code ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        $qrCodePath = 'qrcodes/qr_' . $attendance->qr_code . '.svg';
        $qrUrl = Storage::disk('public')->exists($qrCodePath)
            ? Storage::disk('public')->url($qrCodePath)
            : null;

        return response()->json([
            'success' => true,
            'data' => $attendance,
            'qr_image_url' => $qrUrl,
        ], Response::HTTP_OK);
    }

    /**
     * Endpoint API khusus Sekretariat untuk memindai QR Code kehadiran (KF-12, KF-14).
     *
     * Mengubah `status_kehadiran` menjadi 'Hadir' dan merekam `tanggal_check_in`.
     */
    public function scanQr(Request $request): JsonResponse
    {
        $request->validate([
            'qr_code' => ['required', 'string'],
        ], [
            'qr_code.required' => 'Token string QR Code wajib disertakan.',
        ]);

        $token = trim($request->string('qr_code'));

        $attendance = Attendance::with([
            'registration.participant.userAccount',
            'registration.event',
        ])->where('qr_code', $token)->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => 'QR Code kehadiran tidak valid atau tidak ditemukan dalam sistem.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Cek apakah peserta sudah pernah melakukan registrasi ulang / check-in
        if ($attendance->status_kehadiran === 'Hadir') {
            $participant = $attendance->registration?->participant;
            return response()->json([
                'success' => false,
                'message' => 'Peserta sudah pernah melakukan check-in kehadiran sebelumnya.',
                'data' => [
                    'id_attendance' => $attendance->id_attendance,
                    'status_kehadiran' => $attendance->status_kehadiran,
                    'tanggal_check_in' => $attendance->tanggal_check_in,
                    'nama_peserta' => $participant ? $participant->full_name : '-',
                    'institusi' => $participant?->institution,
                    'event' => $attendance->registration?->event?->nama_event,
                ],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Perbarui status kehadiran menjadi Hadir
        $now = now();
        $attendance->update([
            'status_kehadiran' => 'Hadir',
            'tanggal_check_in' => $now,
        ]);

        $participant = $attendance->registration?->participant;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi ulang berhasil. Status kehadiran peserta telah diperbarui menjadi Hadir.',
            'data' => [
                'id_attendance' => $attendance->id_attendance,
                'id_registration' => $attendance->id_registration,
                'status_kehadiran' => $attendance->status_kehadiran,
                'tanggal_check_in' => $attendance->tanggal_check_in,
                'participant' => [
                    'id_participant' => $participant?->id_participant,
                    'full_name' => $participant ? $participant->full_name : null,
                    'institution' => $participant?->institution,
                    'phone' => $participant?->phone,
                ],
                'event' => [
                    'id_event' => $attendance->registration?->event?->id_event,
                    'nama_event' => $attendance->registration?->event?->nama_event,
                ],
            ],
        ], Response::HTTP_OK);
    }
}

