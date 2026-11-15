<?php

namespace App\Http\Requests;

use App\Models\RegistrationFee;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class UpdateRegistrationFeeRequest extends StoreRegistrationFeeRequest
{
    public function rules(): array
    {
        return [
            'id_event' => ['sometimes', 'required', 'integer', 'exists:event,id_event'],
            'id_period' => ['sometimes', 'required', 'integer', 'exists:registration_period,id_period'],
            'registration_type' => ['sometimes', 'required', Rule::in(RegistrationFee::REGISTRATION_TYPES)],
            'attendance_mode' => ['sometimes', 'required', Rule::in(RegistrationFee::ATTENDANCE_MODES)],
            'tujuan_publikasi' => ['sometimes', 'required', Rule::in(RegistrationFee::PUBLICATION_TARGETS)],
            'amount' => ['sometimes', 'required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'discount' => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $fee = RegistrationFee::find($this->route('id_fee'));
                if (! $fee) {
                    return; // controller mengembalikan 404
                }

                // Gabungkan nilai yang dikirim dengan nilai tersimpan agar validasi
                // lintas-field (periode-event, diskon<=amount, duplikasi) memakai kondisi akhir.
                $merged = array_merge($fee->only([
                    'id_event', 'id_period', 'registration_type', 'attendance_mode', 'tujuan_publikasi', 'amount', 'discount',
                ]), $this->validated());
                $merged['discount'] = $merged['discount'] ?? 0;

                $this->validatePeriodBelongsToEvent($validator, (int) $merged['id_event'], (int) $merged['id_period']);
                $this->validateDiscountNotAboveAmount($validator, (float) $merged['amount'], (float) $merged['discount']);
                $this->validateNoDuplicate($validator, [
                    'id_event' => (int) $merged['id_event'],
                    'id_period' => (int) $merged['id_period'],
                    'registration_type' => $merged['registration_type'],
                    'attendance_mode' => $merged['attendance_mode'],
                    'tujuan_publikasi' => $merged['tujuan_publikasi'],
                ], $fee->id_fee);
            },
        ];
    }
}
