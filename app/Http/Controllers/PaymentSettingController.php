<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePaymentSettingsRequest;
use App\Support\Audit;
use App\Support\PaymentSettings;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

/** KF-23: menu pengaturan informasi pembayaran (Bendahara/Admin, izin manage_tariffs). */
class PaymentSettingController extends Controller
{
    #[OA\Get(path: '/api/payment-settings', summary: 'Informasi pembayaran panitia', tags: ['Pembayaran'], security: [['sanctumAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Nilai aktif (pengaturan menimpa konfigurasi server)'), new OA\Response(response: 403, description: 'Tanpa izin')])]
    public function show(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => PaymentSettings::current()]);
    }

    #[OA\Put(path: '/api/payment-settings', summary: 'Simpan informasi pembayaran', description: 'Dipakai di invoice/receipt yang terbit berikutnya dan di halaman pembayaran peserta. Invoice lama tidak berubah.', tags: ['Pembayaran'], security: [['sanctumAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(properties: [
            new OA\Property(property: 'bank_name', type: 'string'), new OA\Property(property: 'account_name', type: 'string'), new OA\Property(property: 'account_no', type: 'string'),
            new OA\Property(property: 'swift_code', type: 'string'), new OA\Property(property: 'branch', type: 'string'), new OA\Property(property: 'qr_info', type: 'string'),
            new OA\Property(property: 'international_note', type: 'string'),
        ])),
        responses: [new OA\Response(response: 200, description: 'Tersimpan'), new OA\Response(response: 422, description: 'Validasi gagal')])]
    public function update(UpdatePaymentSettingsRequest $request): JsonResponse
    {
        PaymentSettings::save($request->validated());
        Audit::log('payment_settings.updated', 'app_setting', null, null, ['fields' => implode(',', array_keys($request->validated()))]);

        return response()->json(['success' => true, 'message' => __('asic-api.settings.saved'), 'data' => PaymentSettings::current()]);
    }
}
