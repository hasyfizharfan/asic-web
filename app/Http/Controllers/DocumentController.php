<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Models\IssuedDocument;
use App\Models\Registration;
use App\Models\UserAccount;
use App\Support\PermissionMatrix;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class DocumentController extends Controller
{
    use ApiResponses;

    /** Jenis dokumen yang boleh dilihat staf menurut izin. */
    private function staffTypes(UserAccount $user): array
    {
        $m = app(PermissionMatrix::class);
        $types = [];
        if ($m->allows($user, 'review_abstracts', 'view_only')) {
            array_push($types, 'LOA', 'Penolakan');
        }
        if ($m->allows($user, 'verify_payment', 'view_only')) {
            array_push($types, 'Invoice', 'Receipt');
        }

        return $types;
    }

    private function canAccess(UserAccount $user, IssuedDocument $doc): bool
    {
        $doc->loadMissing('registration.participant');
        if ($doc->registration?->participant?->id_user === $user->id_user) {
            return true;
        }

        return in_array($doc->jenis_dokumen, $this->staffTypes($user), true);
    }

    #[OA\Get(
        path: '/api/registrations/{id_registration}/documents',
        summary: 'Daftar Dokumen Terbit (LOA, Penolakan, Invoice, Receipt)',
        tags: ['Dokumen'],
        security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_registration', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 403, description: 'Bukan pemilik/tanpa izin')]
    )]
    public function index(int $id_registration): JsonResponse
    {
        /** @var UserAccount $user */
        $user = Auth::user();
        $registration = Registration::with('participant')->find($id_registration);
        if (! $registration) {
            return $this->fail(__('asic-api.messages.registrasi_tidak_ditemukan'), Response::HTTP_NOT_FOUND);
        }

        $query = IssuedDocument::where('id_registration', $id_registration)->orderBy('id_document');
        if ($registration->participant?->id_user !== $user->id_user) {
            $types = $this->staffTypes($user);
            if ($types === []) {
                return $this->fail(__('asic-api.messages.anda_tidak_memiliki_hak_akses_untuk_melihat_dokume'), Response::HTTP_FORBIDDEN);
            }
            $query->whereIn('jenis_dokumen', $types);
        }

        return $this->ok(__('asic-api.messages.daftar_dokumen_berhasil_dimuat'), $query->get(['id_document', 'id_registration', 'jenis_dokumen', 'nomor_dokumen', 'tanggal_terbit']));
    }

    #[OA\Get(
        path: '/api/documents/{id_document}/download',
        summary: 'Unduh Dokumen PDF (berotorisasi)',
        tags: ['Dokumen'],
        security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_document', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Berkas PDF'), new OA\Response(response: 403, description: 'Ditolak'), new OA\Response(response: 404, description: 'Tidak ditemukan')]
    )]
    public function download(int $id_document): mixed
    {
        /** @var UserAccount $user */
        $user = Auth::user();
        $doc = IssuedDocument::find($id_document);
        if (! $doc) {
            return $this->fail(__('asic-api.messages.dokumen_tidak_ditemukan'), Response::HTTP_NOT_FOUND);
        }
        if (! $this->canAccess($user, $doc)) {
            return $this->fail(__('asic-api.messages.anda_tidak_memiliki_hak_akses_untuk_mengunduh_doku'), Response::HTTP_FORBIDDEN);
        }

        $disk = Storage::disk(config('asic.documents_disk', 'local'));
        if (! $disk->exists($doc->file_document)) {
            return $this->fail(__('asic-api.messages.berkas_dokumen_tidak_tersedia_di_penyimpanan'), Response::HTTP_NOT_FOUND);
        }

        return $disk->download($doc->file_document, $doc->jenis_dokumen.'-'.str_replace('/', '-', (string) $doc->nomor_dokumen).'.pdf', ['Content-Type' => 'application/pdf']);
    }
}
