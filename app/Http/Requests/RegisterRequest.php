<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk registrasi akun awal delegate/author (KF-08).
     *
     * Disesuaikan dengan elemen visual pada UI:
     * - Kategori & Kewarganegaraan (Indonesian Domestic vs International)
     * - Nama Lengkap dengan Gelar Akademik
     * - Email Institusi
     * - Afiliasi / Departemen & Institusi
     * - Kata Sandi dengan indikator keamanan (8+ chars, Uppercase, Number, Symbol)
     * - Konfirmasi Kata Sandi
     * - Persetujuan Syarat & Ketentuan (Terms & Conditions)
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Kategori delegasi & kewarganegaraan dari radio button UI
            'delegate_category' => ['nullable', 'string', 'max:50'],
            'nationality' => ['nullable', 'string', 'max:50'],
            'country' => ['nullable', 'string', 'max:100'],

            // Nama: Mendukung input single 'full_name' / 'name' dari UI maupun 'first_name' / 'last_name' terpisah
            'full_name' => ['required', 'string', 'max:150'],
            'name' => ['nullable', 'string', 'max:150'],
            'first_name' => ['nullable', 'string', 'max:50'],
            'last_name' => ['nullable', 'string', 'max:50'],
            'prefix' => ['nullable', 'string', 'max:10'],

            // Email institusi / universitas
            'email' => ['required', 'string', 'email', 'max:100', 'unique:user_account,email'],

            // Afiliasi / Departemen & Institusi (mendukung key 'affiliation' dan 'institution')
            'affiliation' => ['nullable', 'string', 'max:150'],
            'institution' => ['nullable', 'string', 'max:150'],

            // Kata sandi dengan 4 syarat keamanan sesuai indikator UI:
            // 8+ chars, Uppercase/Lowercase (mixedCase), Number, Symbol
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],

            // Checkbox persetujuan syarat & ketentuan serta etika publikasi
            'agree_terms' => ['sometimes', 'accepted'],
            'terms' => ['sometimes', 'accepted'],

            // Atribut opsional tambahan
            'phone' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Pesan kustom validasi dalam Bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Alamat email institusi wajib diisi.',
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.max' => 'Panjang alamat email maksimal 100 karakter.',
            'email.unique' => 'Alamat email ini sudah terdaftar dalam sistem.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'password.min' => 'Kata sandi minimal harus terdiri dari 8 karakter.',
            'full_name.max' => 'Nama lengkap maksimal 150 karakter.',
            'name.max' => 'Nama lengkap maksimal 150 karakter.',
            'first_name.max' => 'Nama depan maksimal 50 karakter.',
            'last_name.max' => 'Nama belakang maksimal 50 karakter.',
            'prefix.max' => 'Gelar/sapaan (prefix) maksimal 10 karakter.',
            'affiliation.max' => 'Nama afiliasi/institusi maksimal 150 karakter.',
            'institution.max' => 'Nama institusi maksimal 150 karakter.',
            'agree_terms.accepted' => 'Anda harus menyetujui Syarat & Ketentuan serta Pedoman Etika Publikasi ASIC 2026.',
            'terms.accepted' => 'Anda harus menyetujui Syarat & Ketentuan serta Pedoman Etika Publikasi ASIC 2026.',
        ];
    }
}
