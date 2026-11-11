<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreParticipantFileRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi pengunggahan berkas peserta (KF-16).
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'jenis_file' => ['required', 'in:Presentasi,Artikel'],
            'file' => ['required', 'file', 'mimes:ppt,pptx,pdf,doc,docx', 'max:10240'],
        ];
    }

    /**
     * Pesan kustom validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'jenis_file.required' => 'Jenis berkas (Presentasi/Artikel) wajib ditentukan.',
            'jenis_file.in' => 'Jenis berkas harus berupa Presentasi atau Artikel.',
            'file.required' => 'Berkas wajib diunggah.',
            'file.file' => 'Berkas harus berupa file yang valid.',
            'file.mimes' => 'Format berkas harus berupa PPT, PPTX, PDF, DOC, atau DOCX.',
            'file.max' => 'Ukuran berkas maksimal 10MB.',
        ];
    }
}

