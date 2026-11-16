<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSponsorshipInquiryRequest;
use App\Http\Requests\UpdateSponsorshipInquiryRequest;
use App\Http\Resources\ContentItemResource;
use App\Models\Event;
use App\Models\SponsorshipInquiry;
use App\Support\Audit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/** KF-33: permintaan sponsorship/exhibition dari publik dan pengelolaannya oleh panitia. */
class SponsorshipInquiryController extends Controller
{
    #[OA\Get(path: '/api/content/sponsorship-inquiries', summary: 'Daftar permintaan sponsor/exhibition', tags: ['Konten Publik'], security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_event', in: 'query', required: false, schema: new OA\Schema(type: 'integer')), new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: SponsorshipInquiry::STATUSES))],
        responses: [new OA\Response(response: 200, description: 'Daftar permintaan')])]
    public function index(Request $request): JsonResponse
    {
        $page = SponsorshipInquiry::query()
            ->when($request->filled('id_event'), fn ($q) => $q->where('id_event', $request->integer('id_event')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->latest('id_inquiry')
            ->paginate(min(max($request->integer('per_page', 50), 1), 200));

        return response()->json(['success' => true, 'data' => ContentItemResource::collection($page->getCollection())->resolve(),
            'pagination' => ['total' => $page->total(), 'current_page' => $page->currentPage(), 'last_page' => $page->lastPage()]]);
    }

    #[OA\Get(path: '/api/content/sponsorship-inquiries/{id_inquiry}', summary: 'Detail permintaan', tags: ['Konten Publik'], security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_inquiry', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'Detail')])]
    public function show(int $id_inquiry): JsonResponse
    {
        return response()->json(['success' => true, 'data' => (new ContentItemResource(SponsorshipInquiry::findOrFail($id_inquiry)))->resolve()]);
    }

    #[OA\Patch(path: '/api/content/sponsorship-inquiries/{id_inquiry}', summary: 'Ubah status dan catatan internal', tags: ['Konten Publik'], security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_inquiry', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'Diperbarui')])]
    public function update(UpdateSponsorshipInquiryRequest $request, int $id_inquiry): JsonResponse
    {
        $inquiry = SponsorshipInquiry::findOrFail($id_inquiry);
        $before = $inquiry->status;
        $inquiry->update($request->validated());
        if ($before !== $inquiry->status) {
            Audit::log('content.inquiry_status', 'sponsorship_inquiry', $inquiry->id_inquiry, null, ['from' => $before, 'to' => $inquiry->status]);
        }

        return response()->json(['success' => true, 'message' => __('asic-content.api.saved'), 'data' => (new ContentItemResource($inquiry->fresh()))->resolve()]);
    }

    #[OA\Post(path: '/api/public/sponsorship-inquiries', summary: 'Kirim permintaan sponsorship/exhibition (publik)', description: 'Dibatasi 5 permintaan per menit per IP. Kolom website harus kosong (honeypot).', tags: ['Publik'],
        responses: [new OA\Response(response: 201, description: 'Permintaan tersimpan'), new OA\Response(response: 422, description: 'Validasi gagal'), new OA\Response(response: 429, description: 'Terlalu banyak permintaan')])]
    public function publicStore(StoreSponsorshipInquiryRequest $request): JsonResponse
    {
        $data = $request->safe()->except('website');
        $data['status'] = 'Baru';
        $inquiry = SponsorshipInquiry::create($data);

        $to = config('asic.contact_email');
        if ($to) {
            try {
                $event = Event::find($inquiry->id_event);
                Mail::raw(__('asic-content.inquiry_mail.body', ['jenis' => $inquiry->jenis, 'org' => $inquiry->nama_organisasi, 'event' => $event?->nama_event, 'id' => $inquiry->id_inquiry]), function ($m) use ($to, $inquiry) {
                    $m->to($to)->subject(__('asic-content.inquiry_mail.subject', ['jenis' => $inquiry->jenis]));
                });
            } catch (Throwable $e) {
                Log::warning('sponsorship.email_failed', ['id_inquiry' => $inquiry->id_inquiry, 'error' => $e->getMessage()]);
            }
        }

        return response()->json(['success' => true, 'message' => __('asic-content.api.inquiry_received'), 'data' => ['id_inquiry' => $inquiry->id_inquiry]], Response::HTTP_CREATED);
    }
}
