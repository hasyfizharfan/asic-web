<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAbstractRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi pengiriman abstrak seminar (KF-15).
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'judul_abstrak' => ['required', 'string', 'max:255'],
            'scope' => ['required', 'string', 'in:CP,PB,PP,SM,SE,OT'],
            'metode_presentasi' => ['required', 'in:Oral,Poster'],
            'file_abstrak' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
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
            'judul_abstrak.required' => 'Judul abstrak wajib diisi.',
            'judul_abstrak.max' => 'Judul abstrak maksimal 255 karakter.',
            'scope.max' => 'Ruang lingkup (scope) maksimal 150 karakter.',
            'scope.required' => 'Ruang lingkup abstrak wajib dipilih.',
            'scope.in' => 'Ruang lingkup harus salah satu dari CP, PB, PP, SM, SE, atau OT.',
            'metode_presentasi.required' => 'Metode presentasi (Oral/Poster) wajib dipilih.',
            'metode_presentasi.in' => 'Metode presentasi harus berupa Oral atau Poster.',
            'file_abstrak.required' => 'Berkas naskah abstrak wajib diunggah.',
            'file_abstrak.file' => 'Berkas abstrak harus berupa file yang valid.',
            'file_abstrak.mimes' => 'Format berkas abstrak harus berupa PDF atau Word (pdf, doc, docx).',
            'file_abstrak.max' => 'Ukuran berkas abstrak maksimal 10MB.',
        ];
    }
}

