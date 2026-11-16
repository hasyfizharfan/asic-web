<?php

namespace App\Http\Requests;

use App\Models\RegistrationPeriod;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Dipakai untuk tambah (POST) dan ubah (PATCH, semua kolom opsional). */
class StoreRegistrationPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // izin diperiksa middleware permission:manage_tariffs
    }

    public function rules(): array
    {
        $p = $this->isMethod('patch') ? ['sometimes'] : [];

        return [
            'id_event' => [...$p, 'required', 'integer', 'exists:event,id_event'],
            'nama_periode' => [...$p, 'required', 'string', 'max:100'],
            'tanggal_mulai' => [...$p, 'required', 'date'],
            'tanggal_selesai' => [...$p, 'required', 'date'],
            'status_periode' => [...$p, 'required', Rule::in(['Aktif', 'Tidak Aktif'])],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $current = $this->route('id_period') ? RegistrationPeriod::find($this->route('id_period')) : null;
            $start = $this->input('tanggal_mulai', $current?->tanggal_mulai);
            $end = $this->input('tanggal_selesai', $current?->tanggal_selesai);
            if ($start && $end && strtotime((string) $end) < strtotime((string) $start)) {
                $validator->errors()->add('tanggal_selesai', __('asic-api.periods.end_before_start'));
            }
        }];
    }
}
