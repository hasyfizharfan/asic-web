<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentTemplateRequest;
use App\Http\Requests\UpdateDocumentTemplateRequest;
use App\Http\Requests\UpsertIssuedTemplateRequest;
use App\Http\Resources\DocumentTemplateResource;
use App\Models\DocumentTemplate;
use App\Services\DocumentRenderer;
use App\Services\DocumentService;
use App\Support\Audit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * KF-26/KF-06: template dokumen. Kelompok 1 (LOA, Penolakan, Invoice, Receipt, Sertifikat) dirender
 * dari Blade; yang diatur hanya format nomor & status. Kelompok 2 (Abstrak, Presentasi, Artikel,
 * Panduan) berupa berkas unggahan di disk privat, satu Aktif per (event, jenis, subjenis, bahasa).
 */
class DocumentTemplateController extends Controller
{
    public function __construct(private readonly DocumentService $documents) {}

    #[OA\Get(path: '/api/document-templates', summary: 'Daftar template dokumen', description: 'Dokumen terbit yang belum punya baris tampil sebagai "bawaan" tanpa membuat baris baru.', tags: ['Template Dokumen'], security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id_event', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'kelompok', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['issued', 'downloads'])),
            new OA\Parameter(name: 'jenis_dokumen', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status_template', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['Aktif', 'Tidak Aktif'])),
            new OA\Parameter(name: 'subjenis', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'bahasa', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['id', 'en'])),
        ],
        responses: [new OA\Response(response: 200, description: 'Daftar template'), new OA\Response(response: 403, description: 'Tanpa izin')])]
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'id_event' => ['sometimes', 'integer'], 'kelompok' => ['sometimes', Rule::in(['issued', 'downloads'])],
            'jenis_dokumen' => ['sometimes', 'string'], 'status_template' => ['sometimes', Rule::in(['Aktif', 'Tidak Aktif'])],
            'subjenis' => ['sometimes', 'string'], 'bahasa' => ['sometimes', Rule::in(['id', 'en'])],
        ]);
        $group = $request->string('kelompok')->toString();
        $jenisList = match ($group) {
            'issued' => DocumentTemplate::ISSUED,
            'downloads' => DocumentTemplate::DOWNLOADS,
            default => [...DocumentTemplate::ISSUED, ...DocumentTemplate::DOWNLOADS],
        };
        $rows = DocumentTemplate::query()->whereIn('jenis_dokumen', $jenisList)
            ->when($request->filled('id_event'), fn ($q) => $q->where('id_event', $request->integer('id_event')))
            ->when($request->filled('jenis_dokumen'), fn ($q) => $q->where('jenis_dokumen', $request->string('jenis_dokumen')->toString()))
            ->when($request->filled('status_template'), fn ($q) => $q->where('status_template', $request->string('status_template')->toString()))
            ->when($request->filled('subjenis'), fn ($q) => $q->where('subjenis', $request->string('subjenis')->toString()))
            ->when($request->filled('bahasa'), fn ($q) => $q->where('bahasa', $request->string('bahasa')->toString()))
            ->orderBy('jenis_dokumen')->orderByDesc('id_template')->get();

        $data = DocumentTemplateResource::collection($rows)->resolve();

        // Jenis dokumen terbit tanpa baris Aktif ditampilkan sebagai "bawaan" (tidak menulis ke DB).
        if ($group !== 'downloads' && $request->filled('id_event')) {
            foreach (DocumentTemplate::ISSUED as $jenis) {
                if (! $rows->where('jenis_dokumen', $jenis)->where('status_template', 'Aktif')->count()) {
                    $data[] = ['id_template' => null, 'id_event' => $request->integer('id_event'), 'kelompok' => 'issued', 'jenis_dokumen' => $jenis,
                        'nama_template' => __('asic-documents.builtin_name', ['jenis' => $jenis]), 'format_nomor' => DocumentService::DEFAULT_FORMATS[$jenis] ?? null,
                        'status_template' => 'Aktif', 'bawaan' => true];
                }
            }
        }

        return response()->json(['success' => true, 'data' => $data]);
    }

    #[OA\Get(path: '/api/document-templates/{id_template}', summary: 'Detail template', tags: ['Template Dokumen'], security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_template', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'Detail')])]
    public function show(int $id_template): JsonResponse
    {
        return response()->json(['success' => true, 'data' => (new DocumentTemplateResource(DocumentTemplate::findOrFail($id_template)))->resolve()]);
    }

    #[OA\Post(path: '/api/document-templates', summary: 'Unggah berkas unduhan (Abstrak, Presentasi, Artikel, Panduan)', description: 'Multipart: file (docx/doc/pptx/ppt/pdf, maks 10 MB). Versi Aktif sebelumnya untuk kombinasi yang sama menjadi Tidak Aktif.', tags: ['Template Dokumen'], security: [['sanctumAuth' => []]],
        responses: [new OA\Response(response: 201, description: 'Tersimpan'), new OA\Response(response: 422, description: 'Validasi gagal')])]
    public function store(StoreDocumentTemplateRequest $request): JsonResponse
    {
        $data = $request->safe()->except('file');
        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        $path = sprintf('templates/%d/%s/%s.%s', $data['id_event'], strtolower($data['jenis_dokumen']), Str::uuid(), $ext);
        Storage::disk($this->documents->disk())->putFileAs(dirname($path), $file, basename($path));

        $template = DB::transaction(function () use ($data, $file, $path, $request) {
            $this->deactivateSiblings($data['id_event'], $data['jenis_dokumen'], $data['subjenis'] ?? null, $data['bahasa'] ?? null);

            return DocumentTemplate::create($data + [
                'file_template' => $path, 'tanggal_upload' => now(), 'status_template' => 'Aktif',
                'nama_file_asli' => mb_substr($file->getClientOriginalName(), 0, 255), 'mime' => $file->getMimeType(),
                'ukuran_file' => $file->getSize(), 'id_user_pengunggah' => $request->user()?->id_user,
            ]);
        });
        Audit::log('template.uploaded', 'document_template', $template->id_template, null, ['jenis' => $template->jenis_dokumen]);

        return response()->json(['success' => true, 'message' => __('asic-documents.api.saved'), 'data' => (new DocumentTemplateResource($template))->resolve()], Response::HTTP_CREATED);
    }

    #[OA\Patch(path: '/api/document-templates/{id_template}', summary: 'Ubah template', description: 'Terbit: format_nomor/status_template. Unduhan: nama_template/status_template/subjenis/bahasa/versi.', tags: ['Template Dokumen'], security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_template', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Diperbarui'), new OA\Response(response: 422, description: 'Format nomor tidak valid')])]
    public function update(UpdateDocumentTemplateRequest $request, int $id_template): JsonResponse
    {
        $template = DocumentTemplate::findOrFail($id_template);
        DB::transaction(function () use ($template, $request): void {
            $template->fill($request->validated());
            if ($template->status_template === 'Aktif' && $template->isDirty(['status_template', 'subjenis', 'bahasa'])) {
                in_array($template->jenis_dokumen, DocumentTemplate::ISSUED, true)
                    ? DocumentTemplate::where('id_event', $template->id_event)->where('jenis_dokumen', $template->jenis_dokumen)->whereKeyNot($template->id_template)->update(['status_template' => 'Tidak Aktif'])
                    : $this->deactivateSiblings($template->id_event, $template->jenis_dokumen, $template->subjenis, $template->bahasa, $template->id_template);
            }
            $template->save();
        });
        Audit::log('template.updated', 'document_template', $template->id_template, null, ['jenis' => $template->jenis_dokumen, 'status' => $template->status_template]);

        return response()->json(['success' => true, 'message' => __('asic-documents.api.saved'), 'data' => (new DocumentTemplateResource($template->fresh()))->resolve()]);
    }

    #[OA\Put(path: '/api/document-templates/issued/{jenis}', summary: 'Atur format nomor dokumen terbit', description: 'Membuat baris Aktif bila jenis masih bawaan, atau memperbarui baris Aktif yang ada.', tags: ['Template Dokumen'], security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'jenis', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: DocumentTemplate::ISSUED))],
        responses: [new OA\Response(response: 200, description: 'Tersimpan'), new OA\Response(response: 422, description: 'Format tidak valid')])]
    public function upsertIssued(UpsertIssuedTemplateRequest $request, string $jenis): JsonResponse
    {
        abort_unless(in_array($jenis, DocumentTemplate::ISSUED, true), Response::HTTP_NOT_FOUND);
        $data = $request->validated();
        $template = DocumentTemplate::where('id_event', $data['id_event'])->where('jenis_dokumen', $jenis)->where('status_template', 'Aktif')->orderByDesc('id_template')->first()
            ?? new DocumentTemplate(['id_event' => $data['id_event'], 'jenis_dokumen' => $jenis, 'nama_template' => __('asic-documents.builtin_name', ['jenis' => $jenis]),
                'file_template' => 'builtin:'.strtolower($jenis), 'tanggal_upload' => now(), 'status_template' => 'Aktif']);
        $template->fill(['format_nomor' => $data['format_nomor'], 'status_template' => $data['status_template'] ?? 'Aktif'])->save();
        Audit::log('template.number_format', 'document_template', $template->id_template, null, ['jenis' => $jenis, 'format' => $template->format_nomor]);

        return response()->json(['success' => true, 'message' => __('asic-documents.api.saved'), 'data' => (new DocumentTemplateResource($template->fresh()))->resolve()]);
    }

    #[OA\Get(path: '/api/document-templates/{id_template}/download', summary: 'Unduh berkas template (staf)', tags: ['Template Dokumen'], security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_template', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))], responses: [new OA\Response(response: 200, description: 'Berkas'), new OA\Response(response: 404, description: 'Tidak ada berkas')])]
    public function download(int $id_template): StreamedResponse
    {
        return self::stream(DocumentTemplate::findOrFail($id_template), $this->documents->disk());
    }

    #[OA\Get(path: '/api/document-templates/preview/{jenis}', summary: 'Pratinjau dokumen terbit dengan data CONTOH', description: 'HTML berwatermark CONTOH/SAMPLE; tidak membuat IssuedDocument.', tags: ['Template Dokumen'], security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'jenis', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: DocumentTemplate::ISSUED))], responses: [new OA\Response(response: 200, description: 'HTML pratinjau')])]
    public function preview(Request $request, string $jenis, DocumentRenderer $renderer): HttpResponse
    {
        abort_unless(in_array($jenis, DocumentTemplate::ISSUED, true), Response::HTTP_NOT_FOUND);
        $format = $request->filled('id_event')
            ? DocumentTemplate::where('id_event', $request->integer('id_event'))->where('jenis_dokumen', $jenis)->where('status_template', 'Aktif')->value('format_nomor')
            : null;
        $nomor = str_replace(['{SEQ}', '{SCOPE}', '{EDITION}', '{YYYY}', '{MM}'], ['001', 'CP', config('asic.edition', '4th'), now()->format('Y'), now()->format('m')], $format ?: (DocumentService::DEFAULT_FORMATS[$jenis] ?? '{SEQ}'));

        return response($renderer->html($jenis, self::sampleContext($nomor)), 200, ['Content-Type' => 'text/html; charset=UTF-8', 'Cache-Control' => 'no-store']);
    }

    /** Data contoh berlabel jelas untuk pratinjau; bukan data peserta mana pun. */
    public static function sampleContext(string $nomor): array
    {
        $sample = 'CONTOH / SAMPLE';

        return [
            'sample' => true, 'nomor' => $nomor, 'tanggal' => now(), 'name' => $sample, 'email' => 'sample@example.test', 'institution' => $sample,
            'country' => 'Indonesia', 'event_name' => $sample, 'event_theme' => $sample, 'period_name' => $sample, 'publication_option' => 'Prosiding',
            'is_presenter' => true, 'event_dates' => $sample, 'event_location' => $sample, 'registration_type' => 'Regular', 'attendance_mode' => 'Luring',
            'participation_type' => 'Oral Presenter', 'abstract_title' => $sample, 'presentation_method' => 'Oral', 'review_notes' => null,
            'invoice_nomor' => $sample, 'invoice_due' => now()->addWeek()->toDateString(), 'currency' => 'IDR', 'total' => 0, 'amount' => 0,
            'items' => [['jenis_biaya' => $sample, 'deskripsi' => null, 'jumlah' => 1, 'harga_satuan' => 0, 'subtotal' => 0]],
            'verified_at' => now(), 'payment_qr_info' => null, 'role_label' => $sample, 'certificate_lang' => 'id',
        ];
    }

    /** Alirkan berkas template dari disk privat dengan nama unduhan yang disanitasi. */
    public static function stream(DocumentTemplate $template, string $disk): StreamedResponse
    {
        $path = (string) $template->file_template;
        abort_if(str_starts_with($path, 'builtin:') || ! Storage::disk($disk)->exists($path), Response::HTTP_NOT_FOUND);
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $name = Str::slug($template->nama_template ?: $template->jenis_dokumen).($template->versi ? '-v'.Str::slug($template->versi) : '').'.'.$ext;

        return Storage::disk($disk)->download($path, $name, ['Cache-Control' => 'private, max-age=0, no-store', 'X-Content-Type-Options' => 'nosniff']);
    }

    private function deactivateSiblings(int $idEvent, string $jenis, ?string $subjenis, ?string $bahasa, ?int $exceptId = null): void
    {
        DocumentTemplate::where('id_event', $idEvent)->where('jenis_dokumen', $jenis)->where('status_template', 'Aktif')
            ->when($subjenis === null, fn ($q) => $q->whereNull('subjenis'), fn ($q) => $q->where('subjenis', $subjenis))
            ->when($bahasa === null, fn ($q) => $q->whereNull('bahasa'), fn ($q) => $q->where('bahasa', $bahasa))
            ->when($exceptId, fn ($q) => $q->whereKeyNot($exceptId))
            ->update(['status_template' => 'Tidak Aktif']);
    }
}
