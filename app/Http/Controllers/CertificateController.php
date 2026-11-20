<?php

namespace App\Http\Controllers;

use App\Http\Requests\IssueCertificateRequest;
use App\Models\IssuedDocument;
use App\Models\Registration;
use App\Services\CertificateEligibility;
use App\Services\DocumentService;
use App\Support\Audit;
use App\Support\Currency;
use App\Support\PermissionMatrix;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

/**
 * KF-27: status dan penerbitan sertifikat. Pemilik registrasi atau staf dengan izin issue_documents.
 * Idempoten: penerbitan kedua mengembalikan dokumen & nomor yang sama (DocumentService::issue).
 */
class CertificateController extends Controller
{
    public function __construct(private readonly CertificateEligibility $eligibility, private readonly DocumentService $documents) {}

    #[OA\Get(path: '/api/registrations/{id_registration}/certificate', summary: 'Status kelayakan & dokumen sertifikat', tags: ['Sertifikat'], security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_registration', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: '{ eligible, reasons, checks, document|null }'), new OA\Response(response: 403, description: 'Bukan pemilik/staf')])]
    public function show(Request $request, int $id_registration): JsonResponse
    {
        $registration = Registration::with(['participant', 'event'])->findOrFail($id_registration);
        $this->authorizeAccess($request, $registration, 'view_only');

        return response()->json(['success' => true, 'data' => $this->payload($registration)]);
    }

    #[OA\Post(path: '/api/registrations/{id_registration}/certificate', summary: 'Terbitkan sertifikat bila syarat terpenuhi', tags: ['Sertifikat'], security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_registration', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Sertifikat terbit (atau sudah terbit sebelumnya)'), new OA\Response(response: 422, description: 'Syarat belum terpenuhi beserta alasannya'), new OA\Response(response: 403, description: 'Bukan pemilik/staf')])]
    public function store(IssueCertificateRequest $request, int $id_registration): JsonResponse
    {
        $registration = Registration::with(['participant', 'event', 'abstractSubmission'])->findOrFail($id_registration);
        $this->authorizeAccess($request, $registration, 'full_access');

        $existing = IssuedDocument::where('id_registration', $registration->id_registration)->where('jenis_dokumen', 'Sertifikat')->first();
        if (! $existing) {
            $result = $this->eligibility->evaluate($registration);
            if (! $result['eligible']) {
                return response()->json(['success' => false, 'message' => __('asic-documents.certificate.not_eligible'), 'data' => $result], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $document = $this->documents->issue($registration, 'Sertifikat', [
                'certificate_lang' => Currency::forParticipant($registration->participant) === Currency::IDR ? 'id' : 'en',
            ]);
            Audit::log('certificate.issued', 'issued_document', $document->id_document, null, ['id_registration' => $registration->id_registration]);
        }

        return response()->json(['success' => true, 'message' => __('asic-documents.certificate.issued', ['number' => ($existing ?? $document)->nomor_dokumen]), 'data' => $this->payload($registration)]);
    }

    /** @return array<string, mixed> */
    private function payload(Registration $registration): array
    {
        $document = IssuedDocument::where('id_registration', $registration->id_registration)->where('jenis_dokumen', 'Sertifikat')->first();

        return $this->eligibility->evaluate($registration) + ['document' => $document ? [
            'id_document' => $document->id_document, 'nomor_dokumen' => $document->nomor_dokumen,
            'tanggal_terbit' => $document->tanggal_terbit?->toIso8601String(), 'download_url' => '/api/documents/'.$document->id_document.'/download',
        ] : null];
    }

    private function authorizeAccess(Request $request, Registration $registration, string $level): void
    {
        $user = $request->user();
        $owner = $registration->participant?->id_user === $user?->id_user;
        abort_unless($owner || app(PermissionMatrix::class)->allows($user, 'issue_documents', $level), Response::HTTP_FORBIDDEN);
    }
}
