<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreParticipantFileRequest;
use App\Models\ParticipantFile;
use App\Models\Registration;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ParticipantFileController extends Controller
{
    /**
     * Memantau seluruh daftar berkas peserta (Presentasi & Artikel) beserta status validasinya (KF-16, KF-17).
     */
    public function index(int $id_registration): JsonResponse
    {
        $registration = Registration::with(['participant.userAccount', 'participantFiles'])
            ->findOrFail($id_registration);

        /** @var \App\Models\UserAccount $user */
        $user = Auth::user();

        // Otorisasi: Pemilik atau Panitia (Admin / Sekretariat)
        if (
            !$user->hasRole([Role::ROLE_ADMIN, Role::ROLE_SEKRETARIAT]) &&
            $registration->participant?->id_user !== $user->id_user
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk memantau berkas pendaftaran ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        $files = $registration->participantFiles->map(function ($file) {
            return [
                'id_file' => $file->id_file,
                'id_registration' => $file->id_registration,
                'jenis_file' => $file->jenis_file,
                'nama_file' => $file->nama_file,
                'status_file' => $file->status_file,
                'tanggal_upload' => $file->tanggal_upload,
                'file_url' => Storage::disk('public')->exists($file->file_path)
                    ? Storage::disk('public')->url($file->file_path)
                    : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $files,
        ], Response::HTTP_OK);
    }

    /**
     * Mengunggah berkas Presentasi (PPT) atau Artikel Lengkap (KF-16).
     *
     * Berkas disimpan ke `storage/app/public/participant_files` dengan default status 'Diupload'.
     */
    public function store(StoreParticipantFileRequest $request, int $id_registration): JsonResponse|RedirectResponse
    {
        $registration = Registration::with(['participant.userAccount'])->findOrFail($id_registration);

        /** @var \App\Models\UserAccount $user */
        $user = Auth::user();

        // 1. Otorisasi kepemilikan registrasi
        if (
            !$user->hasRole([Role::ROLE_ADMIN, Role::ROLE_SEKRETARIAT]) &&
            $registration->participant?->id_user !== $user->id_user
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk mengunggah berkas pada pendaftaran ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validated();
        $file = $request->file('file');

        $originalName = $file->getClientOriginalName();

        // 2. Simpan file ke Storage disk 'public'
        $filePath = $file->store('participant_files', 'public');

        // 3. Catat entri ke tabel participant_file
        $participantFile = ParticipantFile::create([
            'id_registration' => $registration->id_registration,
            'jenis_file' => $validated['jenis_file'],
            'nama_file' => $originalName,
            'file_path' => $filePath,
            'tanggal_upload' => now(),
            'status_file' => 'Diupload',
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Berkas {$validated['jenis_file']} berhasil diunggah dengan status Diupload.",
                'data' => [
                    'id_file' => $participantFile->id_file,
                    'id_registration' => $participantFile->id_registration,
                    'jenis_file' => $participantFile->jenis_file,
                    'nama_file' => $participantFile->nama_file,
                    'status_file' => $participantFile->status_file,
                    'tanggal_upload' => $participantFile->tanggal_upload,
                    'file_url' => Storage::disk('public')->url($filePath),
                ],
            ], Response::HTTP_CREATED);
        }

        return redirect()->route('registrations.files.index', $id_registration)
            ->with('success', "Berkas {$validated['jenis_file']} berhasil diunggah.");
    }

    /**
     * Menampilkan status berkas tertentu (KF-17).
     */
    public function show(int $id_file): JsonResponse
    {
        $participantFile = ParticipantFile::with('registration.participant.userAccount')
            ->findOrFail($id_file);

        /** @var \App\Models\UserAccount $user */
        $user = Auth::user();

        if (
            !$user->hasRole([Role::ROLE_ADMIN, Role::ROLE_SEKRETARIAT]) &&
            $participantFile->registration?->participant?->id_user !== $user->id_user
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk melihat berkas ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id_file' => $participantFile->id_file,
                'id_registration' => $participantFile->id_registration,
                'jenis_file' => $participantFile->jenis_file,
                'nama_file' => $participantFile->nama_file,
                'status_file' => $participantFile->status_file,
                'tanggal_upload' => $participantFile->tanggal_upload,
                'file_url' => Storage::disk('public')->exists($participantFile->file_path)
                    ? Storage::disk('public')->url($participantFile->file_path)
                    : null,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Mengunduh berkas peserta (Presentasi/Artikel).
     */
    public function download(int $id_file): BinaryFileResponse|JsonResponse
    {
        $participantFile = ParticipantFile::with('registration.participant.userAccount')
            ->findOrFail($id_file);

        /** @var \App\Models\UserAccount $user */
        $user = Auth::user();

        if (
            !$user->hasRole([Role::ROLE_ADMIN, Role::ROLE_SEKRETARIAT]) &&
            $participantFile->registration?->participant?->id_user !== $user->id_user
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk mengunduh berkas ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        if (!Storage::disk('public')->exists($participantFile->file_path)) {
            return response()->json([
                'success' => false,
                'message' => 'Berkas tidak ditemukan pada server.',
            ], Response::HTTP_NOT_FOUND);
        }

        return Storage::disk('public')->download(
            $participantFile->file_path,
            $participantFile->nama_file
        );
    }
}

