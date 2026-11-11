<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAbstractRequest;
use App\Models\AbstractSubmission;
use App\Models\Registration;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class AbstractController extends Controller
{
    /**
     * Menampilkan data abstrak dari suatu pendaftaran (KF-15, KF-17).
     */
    public function show(int $id_registration): JsonResponse
    {
        $registration = Registration::with(['participant.userAccount', 'abstractSubmission'])
            ->findOrFail($id_registration);

        /** @var \App\Models\UserAccount $user */
        $user = Auth::user();

        // Otorisasi: Pemilik atau Tim Panitia (Admin, Tim Abstrak, Sekretariat)
        if (
            !$user->hasRole([Role::ROLE_ADMIN, Role::ROLE_TIM_ABSTRAK, Role::ROLE_SEKRETARIAT]) &&
            $registration->participant?->id_user !== $user->id_user
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk melihat data abstrak ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        $abstract = $registration->abstractSubmission;

        if (!$abstract) {
            return response()->json([
                'success' => false,
                'message' => 'Naskah abstrak belum dikirimkan untuk pendaftaran ini.',
            ], Response::HTTP_NOT_FOUND);
        }

        $fileUrl = $abstract->file_abstrak && Storage::disk('public')->exists($abstract->file_abstrak)
            ? Storage::disk('public')->url($abstract->file_abstrak)
            : null;

        return response()->json([
            'success' => true,
            'data' => $abstract,
            'file_url' => $fileUrl,
        ], Response::HTTP_OK);
    }

    /**
     * Mengunggah naskah abstrak untuk Presenter Oral/Poster (KF-15).
     *
     * Berkas disimpan ke `storage/app/public/abstracts` dan dicatat di database.
     */
    public function store(StoreAbstractRequest $request, int $id_registration): JsonResponse|RedirectResponse
    {
        $registration = Registration::with(['participant.userAccount', 'abstractSubmission'])
            ->findOrFail($id_registration);

        /** @var \App\Models\UserAccount $user */
        $user = Auth::user();

        // 1. Otorisasi kepemilikan registrasi
        if (
            !$user->hasRole([Role::ROLE_ADMIN, Role::ROLE_SEKRETARIAT, Role::ROLE_TIM_ABSTRAK]) &&
            $registration->participant?->id_user !== $user->id_user
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk mengunggah abstrak pada pendaftaran ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        // 2. Validasi peran peserta: Khusus Presenter (Oral/Poster), Listener dilarang kirim abstrak (KF-15)
        if ($registration->participation_type === 'Listener') {
            return response()->json([
                'success' => false,
                'message' => 'Peserta kategori Listener tidak perlu mengirimkan naskah abstrak.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $validated = $request->validated();
        $file = $request->file('file_abstrak');

        // 3. Simpan file ke Storage::disk('public') pada folder 'abstracts'
        $filePath = $file->store('abstracts', 'public');

        // 4. Jika sebelumnya sudah ada naskah abstrak, hapus berkas lama dari storage
        $existingAbstract = $registration->abstractSubmission;
        if ($existingAbstract && $existingAbstract->file_abstrak) {
            Storage::disk('public')->delete($existingAbstract->file_abstrak);
        }

        // 5. Update atau Insert data ke tabel abstract_submission
        $abstract = AbstractSubmission::updateOrCreate(
            ['id_registration' => $registration->id_registration],
            [
                'judul_abstrak' => $validated['judul_abstrak'],
                'metode_presentasi' => $validated['metode_presentasi'],
                'file_abstrak' => $filePath,
                'tanggal_submit' => now(),
                'status_abstrak' => 'Diajukan',
            ]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Naskah abstrak berhasil diunggah dengan status Diajukan.',
                'data' => $abstract,
                'file_url' => Storage::disk('public')->url($filePath),
            ], Response::HTTP_CREATED);
        }

        return redirect()->route('registrations.show', $id_registration)
            ->with('success', 'Naskah abstrak berhasil diunggah.');
    }

    /**
     * Mengunduh berkas naskah abstrak.
     */
    public function download(int $id_registration): BinaryFileResponse|JsonResponse
    {
        $registration = Registration::with(['participant.userAccount', 'abstractSubmission'])
            ->findOrFail($id_registration);

        /** @var \App\Models\UserAccount $user */
        $user = Auth::user();

        // Otorisasi
        if (
            !$user->hasRole([Role::ROLE_ADMIN, Role::ROLE_TIM_ABSTRAK, Role::ROLE_SEKRETARIAT]) &&
            $registration->participant?->id_user !== $user->id_user
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk mengunduh naskah abstrak ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        $abstract = $registration->abstractSubmission;
        if (!$abstract || !$abstract->file_abstrak || !Storage::disk('public')->exists($abstract->file_abstrak)) {
            return response()->json([
                'success' => false,
                'message' => 'Berkas naskah abstrak tidak ditemukan di server.',
            ], Response::HTTP_NOT_FOUND);
        }

        return Storage::disk('public')->download($abstract->file_abstrak);
    }
}

