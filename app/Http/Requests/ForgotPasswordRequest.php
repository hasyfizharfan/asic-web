<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalisasi input sebelum validasi dijalankan.
     */
    protected function prepareForValidation(): void
    {
        // Mendukung key 'registered_email' dari UI label "Registered Email Address"
        if (!$this->has('email') && $this->has('registered_email')) {
            $this->merge([
                'email' => $this->input('registered_email'),
            ]);
        }
    }

    /**
     * Aturan validasi untuk permintaan link reset kata sandi (KF-08).
     *
     * Disesuaikan dengan UI "Reset Your Password":
     * - Registered Email Address
     * - Protected by reCAPTCHA Enterprise
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'exists:user_account,email'],
            'registered_email' => ['nullable', 'string', 'email'],

            // Token keamanan reCAPTCHA Enterprise dari frontend
            'recaptcha_token' => ['nullable', 'string'],
            'g-recaptcha-response' => ['nullable', 'string'],
            'captcha_token' => ['nullable', 'string'],
        ];
    }

    /**
     * Pesan kustom untuk validasi forgot password.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Alamat email terdaftar wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'email.exists' => 'Alamat email tidak ditemukan dalam sistem akun delegasi ASIC 2026.',
        ];
    }
}
