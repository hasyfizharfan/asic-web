<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Models\Invoice;
use App\Models\PaymentProof;
use App\Models\UserAccount;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * KF-21: peserta mengunggah bukti pembayaran sebelum jatuh tempo.
 * Dibuat karena endpoint ini belum ada di repo dan menjadi prasyarat verifikasi Bendahara;
 * sinkronkan dengan anggota tim yang memegang modul M-06 sisi peserta.
 */
class PaymentProofController extends Controller
{
    use ApiResponses;

    public function store(Request $request, int $id_invoice): JsonResponse
    {
        /** @var UserAccount $user */
        $user = Auth::user();

        $input = $request->validate([
            'file_bukti' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'metode_pembayaran' => ['nullable', 'string', 'in:'.implode(',', PaymentProof::METHODS)],
            'catatan_peserta' => ['nullable', 'string', 'max:1000'],
        ], [
            'file_bukti.required' => 'Berkas bukti pembayaran wajib diunggah.',
            'file_bukti.mimes' => 'Bukti pembayaran harus berformat JPG, PNG, atau PDF.',
            'file_bukti.max' => 'Ukuran bukti pembayaran maksimal 5 MB.',
        ]);

        $outcome = DB::transaction(function () use ($id_invoice, $user, $request, $input) {
            $invoice = Invoice::with(['registration.participant', 'registration.abstractSubmission'])->lockForUpdate()->find($id_invoice);
            if (! $invoice) {
                return ['error' => Response::HTTP_NOT_FOUND, 'message' => __('asic-api.messages.invoice_tidak_ditemukan')];
            }
            if ($invoice->registration?->participant?->id_user !== $user->id_user) {
                return ['error' => Response::HTTP_FORBIDDEN, 'message' => __('asic-api.messages.anda_bukan_pemilik_invoice_ini')];
            }
            // KF-20: presenter membayar setelah abstrak diterima (invoice lama yang terbit dini tidak dihapus).
            if ($invoice->registration?->isPresenter() && $invoice->registration->abstractSubmission?->status_abstrak !== 'Diterima') {
                return ['error' => Response::HTTP_UNPROCESSABLE_ENTITY, 'message' => __('asic-api.payment.presenter_abstract_not_accepted')];
            }
            if (! in_array($invoice->status_pembayaran, ['Menunggu Pembayaran', 'Ditolak'], true)) {
                return ['error' => Response::HTTP_CONFLICT, 'message' => "Bukti tidak dapat diunggah pada invoice berstatus '{$invoice->status_pembayaran}'."];
            }
            $due = $invoice->tanggal_jatuh_tempo_baru ?? $invoice->tanggal_jatuh_tempo;
            if ($due && Carbon::parse($due)->endOfDay()->isPast()) {
                return ['error' => Response::HTTP_UNPROCESSABLE_ENTITY, 'message' => __('asic-api.messages.batas_waktu_pembayaran_invoice_sudah_terlewat')];
            }

            $path = $request->file('file_bukti')->store('payment-proofs/'.$invoice->id_invoice, 'local');
            $proof = PaymentProof::create([
                'id_invoice' => $invoice->id_invoice,
                'file_bukti' => $path,
                'tanggal_upload' => now(),
                // Peserta mentransfer sesuai total tagihan (nominal tidak diisi/diubah peserta);
                // kecocokan dana dengan mutasi rekening diperiksa Bendahara.
                'nominal_transfer' => $invoice->total_tagihan,
                'metode_pembayaran' => $input['metode_pembayaran'] ?? null,
                'catatan_peserta' => $input['catatan_peserta'] ?? null,
            ]);
            $invoice->update(['status_pembayaran' => 'Menunggu Verifikasi']);

            return compact('proof', 'invoice');
        });

        if (isset($outcome['error'])) {
            return $this->fail($outcome['message'], $outcome['error']);
        }

        Log::info('payment.proof_uploaded', ['id_invoice' => $outcome['invoice']->id_invoice, 'id_payment_proof' => $outcome['proof']->id_payment_proof]);

        return $this->ok(__('asic-api.messages.bukti_pembayaran_berhasil_diunggah_dan_menunggu_ve'), [
            'id_payment_proof' => $outcome['proof']->id_payment_proof,
            'status_pembayaran' => $outcome['invoice']->status_pembayaran,
        ], Response::HTTP_CREATED);
    }
}
