<?php

namespace App\Http\Requests;

use App\Models\SponsorshipInquiry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSponsorshipInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Event publik saja (Aktif); paket opsional dan harus milik event itu serta terpublikasi.
            'id_event' => ['required', 'integer', Rule::exists('event', 'id_event')->where('status_event', 'Aktif')],
            'id_package' => ['nullable', 'integer', Rule::exists('sponsorship_package', 'id_package')->where('id_event', (int) $this->input('id_event'))->where('status', 'Dipublikasikan')],
            'jenis' => ['required', Rule::in(SponsorshipInquiry::TYPES)],
            'nama_organisasi' => ['required', 'string', 'max:150'],
            'nama_kontak' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'telepon' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+()\-\s]+$/'],
            'pesan' => ['required', 'string', 'min:10', 'max:3000'],
            // Honeypot: diisi bot, kosong bagi manusia.
            'website' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return ['website.prohibited' => __('asic-content.validation.spam')];
    }
}
