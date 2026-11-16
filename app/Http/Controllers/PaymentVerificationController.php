<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Requests\VerifyPaymentRequest;
use App\Models\Invoice;
use App\Models\PaymentProof;
use App\Models\PaymentVerification;
use App\Models\UserAccount;
use App\Services\DocumentService;
use App\Services\StatusNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class PaymentVerificationController extends Controller
{
    use ApiResponses;

    private const STATUSES = ['Menunggu Pembayaran', 'Menunggu Verifikasi', 'Terverifikasi', 'Ditolak', 'Kadaluwarsa'];

    #[OA\Get(
        path: '/api/payments',
        summary: 'Daftar Pembayaran (default: Menunggu Verifikasi)',
        tags: ['Verifikasi Pembayaran'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', example: 'Menunggu Verifikasi')),
            new OA\Parameter(name: 'id_event', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [new OA\Response(response: 200, description: 'OK')]
    )]
    public function index(Request $request): JsonResponse
    {
        $status = $request->input('status', 'Menunggu Verifikasi');
        if (! in_array($status, self::STATUSES, true) && $status !== 'all') {
            return $this->fail(__('asic-api.messages.status_pembayaran_tidak_valid'), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $invoices = Invoice::with(['registration.participant.userAccount', 'latestPaymentProof.verification'])
            ->when($status !== 'all', fn ($q) => $q->where('status_pembayaran', $status))
            ->when($request->filled('id_event'), fn ($q) => $q->whereHas('registration', fn ($r) => $r->where('id_event', $request->integer('id_event'))))
            ->when($request->filled('search'), fn ($q) => $q->where('nomor_invoice', 'like', '%'.$request->string('search').'%'))
            ->orderByDesc('id_invoice')
            ->paginate(min(max($request->integer('per_page', 15), 1), 100));

        return $this->page('Daftar pembayaran berhasil dimuat.', $invoices, $invoices->getCollection());
    }

    #[OA\Get(
        path: '/api/payments/{id_invoice}',
        summary: 'Detail Pembayaran, Bukti, dan Riwayat Verifikasi',
        tags: ['Verifikasi Pembayaran'],
        security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_invoice', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'OK'), new OA\Response(response: 404, description: 'Tidak ditemukan')]
    )]
    public function show(int $id_invoice): JsonResponse
    {
        $invoice = Invoice::with([
            'registration.participant.userAccount', 'invoiceDetails',
            'paymentProofs' => fn ($q) => $q->orderBy('id_payment_proof'), 'paymentProofs.verification.treasurer:id_user,email',
        ])->find($id_invoice);

        if (! $invoice) {
            return $this->fail(__('asic-api.messages.invoice_tidak_ditemukan'), Response::HTTP_NOT_FOUND);
        }

        $data = $invoice->toArray();
        $data['proofs_files'] = $invoice->paymentProofs->map(fn ($p) => [
            'id_payment_proof' => $p->id_payment_proof,
            'file_url' => route('api.payment-proofs.file', $p->id_payment_proof),
        ]);

        return $this->ok(__('asic-api.messages.detail_pembayaran_berhasil_dimuat'), $data);
    }

    #[OA\Get(
        path: '/api/payment-proofs/{id_payment_proof}/file',
        summary: 'Lihat Berkas Bukti Pembayaran',
        tags: ['Verifikasi Pembayaran'],
        security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_payment_proof', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Berkas'), new OA\Response(response: 404, description: 'Tidak ditemukan')]
    )]
    public function proofFile(int $id_payment_proof): mixed
    {
        $proof = PaymentProof::find($id_payment_proof);
        if (! $proof) {
            return $this->fail(__('asic-api.messages.bukti_pembayaran_tidak_ditemukan'), Response::HTTP_NOT_FOUND);
        }

        // Disk penyimpanan bukti ditentukan modul unggah (anggota lain); cek disk privat lalu publik.
        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($proof->file_bukti)) {
                return response()->file(Storage::disk($disk)->path($proof->file_bukti));
            }
        }

        return $this->fail(__('asic-api.messages.berkas_bukti_pembayaran_tidak_ditemukan_di_penyimp'), Response::HTTP_NOT_FOUND);
    }

    #[OA\Post(
        path: '/api/payment-proofs/{id_payment_proof}/verify',
        summary: 'Verifikasi atau Tolak Bukti Pembayaran',
        description: 'Valid → invoice Terverifikasi + receipt terbit. Tidak Valid → invoice Ditolak + alasan disimpan. Satu bukti hanya dapat diverifikasi sekali.',
        tags: ['Verifikasi Pembayaran'],
        security: [['sanctumAuth' => []]],
        parameters: [new OA\Parameter(name: 'id_payment_proof', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['hasil_verifikasi'],
            properties: [
                new OA\Property(property: 'hasil_verifikasi', type: 'string', enum: ['Valid', 'Tidak Valid']),
                new OA\Property(property: 'alasan_penolakan', type: 'string'),
            ]
        )),
        responses: [
            new OA\Response(response: 200, description: 'Terverifikasi/ditolak'),
            new OA\Response(response: 409, description: 'Sudah diverifikasi / status tidak sesuai'),
            new OA\Response(response: 422, description: 'Validasi gagal'),
        ]
    )]
    public function verify(VerifyPaymentRequest $request, int $id_payment_proof): JsonResponse
    {
        /** @var UserAccount $user */
        $user = Auth::user();
        $input = $request->validated();

        $outcome = DB::transaction(function () use ($id_payment_proof, $input, $user) {
            $proof = PaymentProof::lockForUpdate()->find($id_payment_proof);
            if (! $proof) {
                return ['error' => Response::HTTP_NOT_FOUND, 'message' => __('asic-api.messages.bukti_pembayaran_tidak_ditemukan')];
            }
            $invoice = Invoice::with('registration')->lockForUpdate()->find($proof->id_invoice);

            if (PaymentVerification::where('id_payment_proof', $proof->id_payment_proof)->exists()) {
                return ['error' => Response::HTTP_CONFLICT, 'message' => __('asic-api.messages.bukti_pembayaran_ini_sudah_diverifikasi')];
            }
            if ((int) PaymentProof::where('id_invoice', $invoice->id_invoice)->max('id_payment_proof') !== (int) $proof->id_payment_proof) {
                return ['error' => Response::HTTP_CONFLICT, 'message' => __('asic-api.messages.hanya_bukti_pembayaran_terbaru_yang_dapat_diverifi')];
            }
            if ($invoice->status_pembayaran !== 'Menunggu Verifikasi') {
                return ['error' => Response::HTTP_CONFLICT, 'message' => "Invoice berstatus '{$invoice->status_pembayaran}', bukan 'Menunggu Verifikasi'."];
            }

            $valid = $input['hasil_verifikasi'] === 'Valid';
            $verification = PaymentVerification::create([
                'id_payment_proof' => $proof->id_payment_proof,
                'id_treasurer' => $user->id_user,
                'tanggal_verifikasi' => now(),
                'hasil_verifikasi' => $input['hasil_verifikasi'],
                'alasan_penolakan' => $valid ? null : $input['alasan_penolakan'],
            ]);

            $receipt = null;
            if ($valid) {
                $invoice->update(['status_pembayaran' => 'Terverifikasi']);
                $receipt = app(DocumentService::class)->issue($invoice->registration, 'Receipt', [
                    'invoice_nomor' => $invoice->nomor_invoice,
                    'amount' => $invoice->total_tagihan,
                    'currency' => $invoice->mata_uang ?? 'IDR',
                    'verified_at' => $verification->tanggal_verifikasi,
                    'items' => $invoice->invoiceDetails()->get(['jenis_biaya', 'deskripsi', 'jumlah', 'harga_satuan', 'subtotal'])->toArray(),
                ]);
            } else {
                $invoice->update(['status_pembayaran' => 'Ditolak']);
            }

            return compact('proof', 'invoice', 'verification', 'receipt');
        });

        if (isset($outcome['error'])) {
            return $this->fail($outcome['message'], $outcome['error']);
        }

        app(StatusNotifier::class)->notify(
            $outcome['invoice']->registration,
            $input['hasil_verifikasi'] === 'Valid' ? 'payment_valid' : 'payment_invalid',
            'verification:'.$outcome['verification']->id_verification,
            ['reason' => $outcome['verification']->alasan_penolakan]
        );

        Log::info('payment.verified', [
            'id_invoice' => $outcome['invoice']->id_invoice, 'id_payment_proof' => $outcome['proof']->id_payment_proof,
            'hasil' => $input['hasil_verifikasi'], 'treasurer' => $user->id_user,
        ]);

        $proofNominal = $outcome['proof']->nominal_transfer;

        return $this->ok(
            $input['hasil_verifikasi'] === 'Valid' ? 'Pembayaran terverifikasi dan receipt diterbitkan.' : 'Bukti pembayaran ditolak.',
            [
                'id_invoice' => $outcome['invoice']->id_invoice,
                'status_pembayaran' => $outcome['invoice']->status_pembayaran,
                'verification' => $outcome['verification'],
                'receipt' => $outcome['receipt'] ? ['id_document' => $outcome['receipt']->id_document, 'nomor_dokumen' => $outcome['receipt']->nomor_dokumen] : null,
                'nominal_matches_invoice' => $proofNominal === null ? null : round((float) $proofNominal, 2) === round((float) $outcome['invoice']->total_tagihan, 2),
            ]
        );
    }
}
