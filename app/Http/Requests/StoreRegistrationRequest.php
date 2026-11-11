<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRegistrationRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna diizinkan membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi pendaftaran event seminar (KF-10).
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_event' => ['required', 'integer', 'exists:event,id_event'],
            'id_period' => ['required', 'integer', 'exists:registration_period,id_period'],
            'registration_type' => ['required', 'in:Student,Regular,Committee'],
            'attendance_mode' => ['required', 'in:Daring,Luring'],
            'participation_type' => ['required', 'in:Oral Presenter,Poster Presenter,Listener'],
            'publication_option' => ['nullable', 'in:Prosiding,Jurnal,Tidak'],
            'add_ons' => ['nullable', 'array'],
            'add_ons.*.id_add_on' => ['required_with:add_ons', 'integer', 'exists:add_on,id_add_on'],
            'add_ons.*.jumlah' => ['required_with:add_ons', 'integer', 'min:1'],
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
            'id_event.required' => 'Kegiatan seminar wajib dipilih.',
            'id_event.exists' => 'Kegiatan seminar yang dipilih tidak valid.',
            'id_period.required' => 'Periode pendaftaran wajib dipilih.',
            'id_period.exists' => 'Periode pendaftaran tidak valid.',
            'registration_type.required' => 'Kategori peserta (Student/Regular/Committee) wajib dipilih.',
            'registration_type.in' => 'Kategori peserta harus berupa Student, Regular, atau Committee.',
            'attendance_mode.required' => 'Mode kehadiran (Daring/Luring) wajib dipilih.',
            'attendance_mode.in' => 'Mode kehadiran harus berupa Daring atau Luring.',
            'participation_type.required' => 'Jenis partisipasi (Oral Presenter/Poster Presenter/Listener) wajib dipilih.',
            'participation_type.in' => 'Jenis partisipasi harus berupa Oral Presenter, Poster Presenter, atau Listener.',
            'publication_option.in' => 'Opsi publikasi harus berupa Prosiding, Jurnal, atau Tidak.',
            'add_ons.*.id_add_on.exists' => 'Layanan tambahan (add-on) yang dipilih tidak valid.',
            'add_ons.*.jumlah.min' => 'Jumlah layanan tambahan minimal 1.',
        ];
    }
}

