<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScanReregistrationRequest;
use App\Http\Requests\StoreReregistrationRequest;
use App\Http\Resources\ReregistrationResource;
use App\Models\Event;
use App\Models\Registration;
use App\Services\ReregistrationService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

/**
 * KF-12: registrasi ulang peserta Luring pada hari pertama kegiatan (meja Sekretariat).
 */
class ReregistrationController extends Controller
{
    public function __construct(private readonly ReregistrationService $service) {}

    #[OA\Post(
        path: '/api/events/{id_event}/reregistration/scan',
        summary: 'Registrasi ulang lewat pemindaian QR kehadiran peserta',
        description: 'Hanya peserta Luring, berstatus Terdaftar, pembayaran Terverifikasi, dan hanya pada hari pertama kegiatan (zona waktu aplikasi). Pemindaian kedua mengembalikan 409 tanpa mengubah waktu registrasi ulang.',
        tags: ['Kehadiran'],
        security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_event', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['qr_token'], properties: [new OA\Property(property: 'qr_token', type: 'string', maxLength: 100)])),
        responses: [
            new OA\Response(response: 200, description: 'Registrasi ulang tercatat'),
            new OA\Response(response: 403, description: 'Tanpa izin, pembayaran belum Terverifikasi, status bukan Terdaftar, atau bukan hari pertama'),
            new OA\Response(response: 409, description: 'Peserta sudah registrasi ulang'),
            new OA\Response(response: 422, description: 'Token tidak dikenali, peserta Daring, atau kegiatan tidak cocok'),
        ]
    )]
    public function scan(ScanReregistrationRequest $request, int $id_event): JsonResponse
    {
        $event = Event::findOrFail($id_event);
        $registration = Registration::with(['participant', 'invoice'])
            ->where('qr_attendance_token', $request->validated('qr_token'))
            ->first();

        if (! $registration) {
            return response()->json(['success' => false, 'message' => __('asic-api.reregistration.token_invalid')], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->respond($this->service->register($event, $registration, $request->user()));
    }

    #[OA\Post(
        path: '/api/events/{id_event}/reregistration',
        summary: 'Registrasi ulang manual dari hasil pencarian',
        description: 'Aturan sama dengan pemindaian QR: Luring, Terdaftar, Terverifikasi, hari pertama, idempoten (409).',
        tags: ['Kehadiran'],
        security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_event', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(required: ['id_registration'], properties: [new OA\Property(property: 'id_registration', type: 'integer')])),
        responses: [
            new OA\Response(response: 200, description: 'Registrasi ulang tercatat'),
            new OA\Response(response: 403, description: 'Tanpa izin atau syarat tidak terpenuhi'),
            new OA\Response(response: 409, description: 'Peserta sudah registrasi ulang'),
            new OA\Response(response: 422, description: 'Validasi gagal, peserta Daring, atau kegiatan tidak cocok'),
        ]
    )]
    public function store(StoreReregistrationRequest $request, int $id_event): JsonResponse
    {
        $event = Event::findOrFail($id_event);
        $registration = Registration::with(['participant', 'invoice'])->findOrFail($request->validated('id_registration'));

        return $this->respond($this->service->register($event, $registration, $request->user()));
    }

    /** @param array{status: int, message: string, registration: Registration|null} $result */
    private function respond(array $result): JsonResponse
    {
        return response()->json([
            'success' => $result['status'] === Response::HTTP_OK,
            'message' => $result['message'],
            'data' => $result['registration'] ? (new ReregistrationResource($result['registration']))->resolve() : null,
        ], $result['status']);
    }
}
