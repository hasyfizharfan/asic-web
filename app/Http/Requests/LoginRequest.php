<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk login akun.
     * Mendukung variasi input UI: 'email_or_username', 'email', 'username', atau 'login'
     * untuk login akun.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email_or_username' => ['sometimes', 'required_without_all:email,username,login', 'string'],
            'email' => ['sometimes', 'required_without_all:email_or_username,username,login', 'string'],
            'username' => ['sometimes', 'required_without_all:email_or_username,email,login', 'string'],
            'login' => ['sometimes', 'required_without_all:email_or_username,email,username', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Dapatkan nilai identitas login (Email atau Username/Reg.ID) yang dikirim oleh form UI.
     */
    public function getLoginIdentifier(): string
    {
        return trim((string) ($this->input('email_or_username')
            ?? $this->input('email')
            ?? $this->input('username')
            ?? $this->input('login')
            ?? ''));
    }

    /**
     * Pesan kustom untuk validasi login.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email_or_username.required_without_all' => 'Alamat email atau username / nomor registrasi wajib diisi.',
            'email.required_without_all' => 'Alamat email atau username / nomor registrasi wajib diisi.',
            'username.required_without_all' => 'Alamat email atau username / nomor registrasi wajib diisi.',
            'login.required_without_all' => 'Alamat email atau username / nomor registrasi wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ];
    }
}
