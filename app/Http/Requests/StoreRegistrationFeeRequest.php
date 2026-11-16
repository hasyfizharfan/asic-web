<?php

namespace App\Http\Requests;

use App\Models\RegistrationFee;
use App\Models\RegistrationPeriod;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRegistrationFeeRequest extends FormRequest
{
    /**
     * Otorisasi ditegakkan oleh middleware `permission:manage_tariffs` pada route.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_event' => ['required', 'integer', 'exists:event,id_event'],
            'id_period' => ['required', 'integer', 'exists:registration_period,id_period'],
            'registration_type' => ['required', Rule::in(RegistrationFee::REGISTRATION_TYPES)],
            'attendance_mode' => ['required', Rule::in(RegistrationFee::ATTENDANCE_MODES)],
            'tujuan_publikasi' => ['required', Rule::in(RegistrationFee::PUBLICATION_TARGETS)],
            'amount' => ['required', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'discount' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99', 'decimal:0,2'],
            'mata_uang' => ['nullable', Rule::in(RegistrationFee::CURRENCIES)],
        ];
    }

    public function messages(): array
    {
        return [
            'id_event.required' => 'Event wajib dipilih.',
            'id_event.exists' => 'Event tidak ditemukan.',
            'id_period.required' => 'Periode pendaftaran wajib dipilih.',
            'id_period.exists' => 'Periode pendaftaran tidak ditemukan.',
            'registration_type.required' => 'Kategori peserta wajib diisi.',
            'registration_type.in' => 'Kategori peserta harus salah satu dari: Student, Regular, Committee.',
            'attendance_mode.required' => 'Mode kehadiran wajib diisi.',
            'attendance_mode.in' => 'Mode kehadiran harus salah satu dari: Daring, Luring.',
            'tujuan_publikasi.required' => 'Tujuan publikasi wajib diisi.',
            'tujuan_publikasi.in' => 'Tujuan publikasi harus salah satu dari: Prosiding, Jurnal, Tidak.',
            'amount.required' => 'Nominal tarif wajib diisi.',
            'amount.numeric' => 'Nominal tarif harus berupa angka.',
            'amount.min' => 'Nominal tarif tidak boleh negatif.',
            'amount.decimal' => 'Nominal tarif maksimal 2 angka desimal.',
            'discount.numeric' => 'Diskon harus berupa angka.',
            'discount.min' => 'Diskon tidak boleh negatif.',
            'discount.decimal' => 'Diskon maksimal 2 angka desimal.',
            'mata_uang.in' => 'Mata uang harus IDR atau USD.',
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $data = $this->all();

                $this->validatePeriodBelongsToEvent($validator, (int) $data['id_event'], (int) $data['id_period']);
                $this->validateDiscountNotAboveAmount($validator, (float) $data['amount'], (float) ($data['discount'] ?? 0));
                $this->validateNoDuplicate($validator, [
                    'id_event' => (int) $data['id_event'],
                    'id_period' => (int) $data['id_period'],
                    'registration_type' => $data['registration_type'],
                    'attendance_mode' => $data['attendance_mode'],
                    'tujuan_publikasi' => $data['tujuan_publikasi'],
                    'mata_uang' => $data['mata_uang'] ?? 'IDR',
                ]);
            },
        ];
    }

    /**
     * Nilai yang akan disimpan: diskon default 0 bila tidak dikirim.
     *
     * @return array<string, mixed>
     */
    public function validatedForStore(): array
    {
        $validated = $this->validated();
        $validated['discount'] = $validated['discount'] ?? 0;

        return $validated;
    }

    protected function validatePeriodBelongsToEvent(Validator $validator, int $eventId, int $periodId): void
    {
        $belongs = RegistrationPeriod::query()
            ->where('id_period', $periodId)
            ->where('id_event', $eventId)
            ->exists();

        if (! $belongs) {
            $validator->errors()->add('id_period', 'Periode pendaftaran tidak termasuk dalam event yang dipilih.');
        }
    }

    protected function validateDiscountNotAboveAmount(Validator $validator, float $amount, float $discount): void
    {
        if (round($discount, 2) > round($amount, 2)) {
            $validator->errors()->add('discount', 'Diskon tidak boleh melebihi nominal tarif.');
        }
    }

    /**
     * @param  array<string, mixed>  $combination
     */
    protected function validateNoDuplicate(Validator $validator, array $combination, ?int $ignoreId = null): void
    {
        $exists = RegistrationFee::query()
            ->where($combination)
            ->when($ignoreId, fn ($q) => $q->where('id_fee', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            $validator->errors()->add('registration_type', 'Tarif dengan kombinasi event, periode, kategori peserta, mode kehadiran, tujuan publikasi, dan mata uang yang sama sudah ada.');
        }
    }
}
