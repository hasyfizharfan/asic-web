<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_event' => ['required', 'integer', 'exists:event,id_event'],
            'nama_sesi' => ['required', 'string', 'max:150'],
            'waktu' => ['required', 'date'],
            'waktu_selesai' => ['required', 'date', 'after:waktu'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_event.required' => 'ID Event wajib dipilih.',
            'id_event.exists' => 'Event tidak ditemukan.',
            'nama_sesi.required' => 'Nama sesi wajib diisi.',
            'nama_sesi.max' => 'Nama sesi maksimal 150 karakter.',
            'waktu.required' => 'Waktu pelaksanaan sesi wajib diisi.',
            'waktu.date' => 'Format waktu sesi tidak valid.',
            'waktu_selesai.required' => 'Waktu selesai sesi wajib diisi.',
            'waktu_selesai.date' => 'Format waktu selesai sesi tidak valid.',
            'waktu_selesai.after' => 'Waktu selesai harus setelah waktu mulai sesi.',
        ];
    }
}

