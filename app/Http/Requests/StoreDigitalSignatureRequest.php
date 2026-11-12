<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDigitalSignatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_role' => ['required', 'integer', 'exists:role,id_role'],
            'file_ttd' => ['required', 'file', 'mimes:png,jpg,jpeg,svg', 'max:2048'],
            'file_stempel' => ['nullable', 'file', 'mimes:png,jpg,jpeg,svg', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_role.required' => 'Peran (Role) target wajib dipilih.',
            'id_role.exists' => 'Peran (Role) tidak valid atau tidak ditemukan.',
            'file_ttd.required' => 'Berkas gambar tanda tangan wajib diunggah.',
            'file_ttd.mimes' => 'Format berkas tanda tangan harus berupa PNG, JPG, JPEG, atau SVG.',
            'file_ttd.max' => 'Ukuran berkas tanda tangan maksimal 2MB.',
            'file_stempel.mimes' => 'Format berkas stempel harus berupa PNG, JPG, JPEG, atau SVG.',
            'file_stempel.max' => 'Ukuran berkas stempel maksimal 2MB.',
        ];
    }
}

