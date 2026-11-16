<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegistrationPeriodRequest;
use App\Http\Resources\RegistrationPeriodResource;
use App\Models\Registration;
use App\Models\RegistrationFee;
use App\Models\RegistrationPeriod;
use App\Support\Audit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

/** KF-11: pengaturan periode pendaftaran (early bird/reguler) oleh Bendahara/Admin (izin manage_tariffs). */
class RegistrationPeriodController extends Controller
{
    #[OA\Get(path: '/api/registration-periods', summary: 'Daftar periode pendaftaran', tags: ['Tarif Pendaftaran'], security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_event', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Daftar periode')])]
    public function index(Request $request): JsonResponse
    {
        $request->validate(['id_event' => ['sometimes', 'integer']]);
        $rows = RegistrationPeriod::query()->with('event:id_event,nama_event')
            ->when($request->filled('id_event'), fn ($q) => $q->where('id_event', $request->integer('id_event')))
            ->orderBy('id_event')->orderBy('tanggal_mulai')->get();

        return response()->json(['success' => true, 'data' => RegistrationPeriodResource::collection($rows)->resolve()]);
    }

    #[OA\Post(path: '/api/registration-periods', summary: 'Tambah periode pendaftaran', tags: ['Tarif Pendaftaran'], security: [['sanctumAuth' => []]],
        responses: [new OA\Response(response: 201, description: 'Tersimpan'), new OA\Response(response: 422, description: 'Validasi gagal')])]
    public function store(StoreRegistrationPeriodRequest $request): JsonResponse
    {
        $period = RegistrationPeriod::create($request->validated());
        Audit::log('period.created', 'registration_period', $period->id_period);

        return response()->json(['success' => true, 'message' => __('asic-api.periods.saved'), 'data' => (new RegistrationPeriodResource($period->load('event')))->resolve()], Response::HTTP_CREATED);
    }

    #[OA\Patch(path: '/api/registration-periods/{id_period}', summary: 'Ubah periode pendaftaran', tags: ['Tarif Pendaftaran'], security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_period', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Diperbarui'), new OA\Response(response: 422, description: 'Validasi gagal')])]
    public function update(StoreRegistrationPeriodRequest $request, int $id_period): JsonResponse
    {
        $period = RegistrationPeriod::findOrFail($id_period);
        $period->update($request->validated());
        Audit::log('period.updated', 'registration_period', $period->id_period, null, ['status' => $period->status_periode]);

        return response()->json(['success' => true, 'message' => __('asic-api.periods.saved'), 'data' => (new RegistrationPeriodResource($period->fresh('event')))->resolve()]);
    }

    #[OA\Delete(path: '/api/registration-periods/{id_period}', summary: 'Hapus periode yang belum dipakai', tags: ['Tarif Pendaftaran'], security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_period', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Dihapus'), new OA\Response(response: 409, description: 'Sudah dipakai tarif/registrasi')])]
    public function destroy(int $id_period): JsonResponse
    {
        $period = RegistrationPeriod::findOrFail($id_period);
        if (Registration::where('id_period', $id_period)->exists() || RegistrationFee::where('id_period', $id_period)->exists()) {
            return response()->json(['success' => false, 'message' => __('asic-api.periods.in_use')], Response::HTTP_CONFLICT);
        }
        $period->delete();
        Audit::log('period.deleted', 'registration_period', $id_period);

        return response()->json(['success' => true, 'message' => __('asic-api.periods.deleted')]);
    }
}
