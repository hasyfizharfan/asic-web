<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegistrationFeeRequest;
use App\Http\Requests\UpdateRegistrationFeeRequest;
use App\Http\Resources\RegistrationFeeResource;
use App\Models\RegistrationFee;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class RegistrationFeeController extends Controller
{
    /**
     * Kolom yang membentuk "identitas" dan nominal tarif. Jika tarif sudah dipakai
     * invoice, kolom-kolom ini dikunci agar data tarif yang dirujuk invoice lama tidak berubah.
     */
    private const LOCKED_WHEN_USED = [
        'id_event', 'id_period', 'registration_type', 'attendance_mode', 'tujuan_publikasi', 'amount', 'discount',
    ];

    #[OA\Get(
        path: '/api/registration-fees',
        summary: 'Daftar Tarif Pendaftaran',
        description: 'Daftar tarif terpaginasi dengan filter event, periode, kategori peserta, mode kehadiran, dan tujuan publikasi (butuh izin manage_tariffs minimal view_only).',
        tags: ['Tarif Pendaftaran'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id_event', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'id_period', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'registration_type', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['Student', 'Regular', 'Committee'])),
            new OA\Parameter(name: 'attendance_mode', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['Daring', 'Luring'])),
            new OA\Parameter(name: 'tujuan_publikasi', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['Prosiding', 'Jurnal', 'Tidak'])),
            new OA\Parameter(name: 'mata_uang', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['IDR', 'USD'])),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', example: 15)),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Daftar tarif berhasil dimuat'),
            new OA\Response(response: 403, description: 'Akses ditolak'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $fees = $this->filteredQuery($request)
            ->with(['event:id_event,nama_event,status_event', 'registrationPeriod'])
            ->withCount('invoices')
            ->orderBy('id_event')->orderBy('id_period')->orderBy('registration_type')
            ->orderBy('attendance_mode')->orderBy('tujuan_publikasi')
            ->paginate($this->perPage($request));

        return $this->paginated($fees, 'Daftar tarif pendaftaran berhasil dimuat.');
    }

    #[OA\Get(
        path: '/api/public/registration-fees',
        summary: 'Daftar Tarif Publik (Read-only)',
        description: 'Tarif yang tampil kepada calon peserta: hanya event berstatus Aktif dan periode berstatus Aktif. Tanpa autentikasi.',
        tags: ['Tarif Pendaftaran'],
        parameters: [
            new OA\Parameter(name: 'id_event', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'registration_type', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'attendance_mode', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'tujuan_publikasi', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'mata_uang', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['IDR', 'USD'])),
        ],
        responses: [new OA\Response(response: 200, description: 'Daftar tarif publik berhasil dimuat')]
    )]
    public function publicIndex(Request $request): JsonResponse
    {
        $fees = $this->filteredQuery($request)
            ->whereHas('event', fn ($q) => $q->where('status_event', 'Aktif'))
            ->whereHas('registrationPeriod', fn ($q) => $q->where('status_periode', 'Aktif'))
            ->with(['event:id_event,nama_event,status_event', 'registrationPeriod'])
            ->orderBy('id_event')->orderBy('id_period')->orderBy('registration_type')
            ->orderBy('attendance_mode')->orderBy('tujuan_publikasi')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar tarif pendaftaran berhasil dimuat.',
            'data' => RegistrationFeeResource::collection($fees),
        ], Response::HTTP_OK);
    }

    #[OA\Get(
        path: '/api/registration-fees/{id_fee}',
        summary: 'Detail Tarif Pendaftaran',
        tags: ['Tarif Pendaftaran'],
        security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_fee', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Detail tarif berhasil dimuat'),
            new OA\Response(response: 404, description: 'Tarif tidak ditemukan'),
        ]
    )]
    public function show(int $id_fee): JsonResponse
    {
        $fee = RegistrationFee::with(['event', 'registrationPeriod'])->withCount('invoices')->find($id_fee);

        if (! $fee) {
            return $this->notFound();
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail tarif berhasil dimuat.',
            'data' => new RegistrationFeeResource($fee),
        ], Response::HTTP_OK);
    }

    #[OA\Post(
        path: '/api/registration-fees',
        summary: 'Tambah Tarif Pendaftaran',
        tags: ['Tarif Pendaftaran'],
        security: [['sanctumAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['id_event', 'id_period', 'registration_type', 'attendance_mode', 'tujuan_publikasi', 'amount'],
                properties: [
                    new OA\Property(property: 'id_event', type: 'integer', example: 1),
                    new OA\Property(property: 'id_period', type: 'integer', example: 1),
                    new OA\Property(property: 'registration_type', type: 'string', enum: ['Student', 'Regular', 'Committee']),
                    new OA\Property(property: 'attendance_mode', type: 'string', enum: ['Daring', 'Luring']),
                    new OA\Property(property: 'tujuan_publikasi', type: 'string', enum: ['Prosiding', 'Jurnal', 'Tidak']),
                    new OA\Property(property: 'amount', type: 'number', example: 1500000),
                    new OA\Property(property: 'discount', type: 'number', example: 0),
                    new OA\Property(property: 'mata_uang', type: 'string', enum: ['IDR', 'USD'], example: 'IDR', description: 'Bawaan IDR. Peserta Indonesia memakai tarif IDR, peserta negara lain tarif USD.'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Tarif berhasil dibuat'),
            new OA\Response(response: 403, description: 'Akses ditolak'),
            new OA\Response(response: 422, description: 'Validasi gagal / kombinasi duplikat'),
        ]
    )]
    public function store(StoreRegistrationFeeRequest $request): JsonResponse
    {
        try {
            $fee = RegistrationFee::create($request->validatedForStore());
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tarif gagal disimpan karena pelanggaran integritas data.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $fee->load(['event', 'registrationPeriod'])->loadCount('invoices');

        return response()->json([
            'success' => true,
            'message' => 'Tarif pendaftaran berhasil ditambahkan.',
            'data' => new RegistrationFeeResource($fee),
        ], Response::HTTP_CREATED);
    }

    #[OA\Patch(
        path: '/api/registration-fees/{id_fee}',
        summary: 'Ubah Tarif Pendaftaran',
        description: 'Update parsial. Jika tarif sudah dipakai invoice, field identitas/nominal tidak dapat diubah (409).',
        tags: ['Tarif Pendaftaran'],
        security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_fee', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Tarif berhasil diperbarui'),
            new OA\Response(response: 404, description: 'Tarif tidak ditemukan'),
            new OA\Response(response: 409, description: 'Tarif sudah dipakai invoice'),
            new OA\Response(response: 422, description: 'Validasi gagal'),
        ]
    )]
    public function update(UpdateRegistrationFeeRequest $request, int $id_fee): JsonResponse
    {
        $fee = RegistrationFee::find($id_fee);

        if (! $fee) {
            return $this->notFound();
        }

        $input = $request->validated();
        if (array_key_exists('discount', $input) && $input['discount'] === null) {
            $input['discount'] = 0;
        }

        $fee->fill($input);

        if ($fee->isUsedByInvoice()) {
            $lockedChanges = array_values(array_intersect(array_keys($fee->getDirty()), self::LOCKED_WHEN_USED));

            if ($lockedChanges !== []) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tarif sudah digunakan oleh invoice sehingga tidak dapat diubah. Buat tarif baru untuk perubahan nominal atau kombinasi.',
                    'locked_fields' => $lockedChanges,
                ], Response::HTTP_CONFLICT);
            }
        }

        $fee->save();
        $fee->load(['event', 'registrationPeriod'])->loadCount('invoices');

        return response()->json([
            'success' => true,
            'message' => 'Tarif pendaftaran berhasil diperbarui.',
            'data' => new RegistrationFeeResource($fee),
        ], Response::HTTP_OK);
    }

    #[OA\Delete(
        path: '/api/registration-fees/{id_fee}',
        summary: 'Hapus Tarif Pendaftaran',
        description: 'Ditolak (409) bila tarif sudah dipakai invoice.',
        tags: ['Tarif Pendaftaran'],
        security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_fee', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Tarif berhasil dihapus'),
            new OA\Response(response: 404, description: 'Tarif tidak ditemukan'),
            new OA\Response(response: 409, description: 'Tarif sudah dipakai invoice'),
        ]
    )]
    public function destroy(int $id_fee): JsonResponse
    {
        $fee = RegistrationFee::find($id_fee);

        if (! $fee) {
            return $this->notFound();
        }

        $conflict = response()->json([
            'success' => false,
            'message' => 'Tarif sudah digunakan oleh invoice sehingga tidak dapat dihapus.',
        ], Response::HTTP_CONFLICT);

        if ($fee->isUsedByInvoice()) {
            return $conflict;
        }

        try {
            $fee->delete();
        } catch (QueryException $e) {
            // Pengaman terakhir: FK invoice.id_fee (ON DELETE RESTRICT) pada kondisi balapan.
            return $conflict;
        }

        return response()->json([
            'success' => true,
            'message' => 'Tarif pendaftaran berhasil dihapus.',
        ], Response::HTTP_OK);
    }

    private function filteredQuery(Request $request)
    {
        return RegistrationFee::query()
            ->when($request->filled('id_event'), fn ($q) => $q->where('id_event', $request->integer('id_event')))
            ->when($request->filled('id_period'), fn ($q) => $q->where('id_period', $request->integer('id_period')))
            ->when($request->filled('registration_type'), fn ($q) => $q->where('registration_type', $request->string('registration_type')->toString()))
            ->when($request->filled('attendance_mode'), fn ($q) => $q->where('attendance_mode', $request->string('attendance_mode')->toString()))
            ->when($request->filled('tujuan_publikasi'), fn ($q) => $q->where('tujuan_publikasi', $request->string('tujuan_publikasi')->toString()))
            ->when($request->filled('mata_uang'), fn ($q) => $q->where('mata_uang', $request->string('mata_uang')->toString()));
    }

    private function perPage(Request $request): int
    {
        return min(max($request->integer('per_page', 15), 1), 100);
    }

    private function paginated($paginator, string $message): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => RegistrationFeeResource::collection($paginator->getCollection()),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ], Response::HTTP_OK);
    }

    private function notFound(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Tarif pendaftaran tidak ditemukan.',
        ], Response::HTTP_NOT_FOUND);
    }
}
