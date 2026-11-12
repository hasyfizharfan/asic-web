<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExtendInvoiceRequest;
use App\Models\Invoice;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    /**
     * Menampilkan detail invoice tagihan seminar.
     */
    #[OA\Get(
        path: '/api/invoices/{id_invoice}',
        summary: 'Detail Invoice Tagihan Seminar',
        description: 'Mendapatkan informasi detail tagihan pendaftaran, rincian item, dan status verifikasi pembayaran.',
        tags: ['Invoice'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id_invoice',
                in: 'path',
                required: true,
                description: 'ID Tagihan Invoice',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail invoice berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object'),
                        new OA\Property(property: 'is_expired', type: 'boolean', example: false)
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Akses ditolak'),
            new OA\Response(response: 404, description: 'Invoice tidak ditemukan')
        ]
    )]
    public function show(int $id_invoice): JsonResponse
    {
        $invoice = Invoice::with([
            'registration.participant.userAccount',
            'registrationFee',
            'invoiceDetails',
            'paymentProofs',
        ])->findOrFail($id_invoice);

        /** @var \App\Models\UserAccount $user */
        $user = Auth::user();

        if (
            !$user->hasRole([Role::ROLE_ADMIN, Role::ROLE_BENDAHARA, Role::ROLE_SEKRETARIAT]) &&
            $invoice->registration?->participant?->id_user !== $user->id_user
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk melihat invoice ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        if ($invoice->isKedaluwarsa() && $invoice->status_pembayaran === 'Menunggu Pembayaran') {
            $invoice->update(['status_pembayaran' => 'Kedaluwarsa']);
        }

        return response()->json([
            'success' => true,
            'data' => $invoice,
            'is_expired' => $invoice->isKedaluwarsa(),
        ], Response::HTTP_OK);
    }

    /**
     * Memperpanjang batas waktu jatuh tempo invoice (Poin 2).
     * Khusus Bendahara & Admin.
     */
    #[OA\Post(
        path: '/api/invoices/{id_invoice}/extend',
        summary: 'Perpanjang Jatuh Tempo Invoice',
        description: 'Memperpanjang tanggal jatuh tempo pembayaran invoice tagihan peserta (Khusus Bendahara & Admin).',
        tags: ['Invoice'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id_invoice',
                in: 'path',
                required: true,
                description: 'ID Invoice',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['tanggal_jatuh_tempo_baru'],
                properties: [
                    new OA\Property(property: 'tanggal_jatuh_tempo_baru', type: 'string', format: 'date', example: '2026-08-15')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Jatuh tempo invoice berhasil diperpanjang',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Format tanggal tidak valid')
        ]
    )]
    public function extendDueDate(ExtendInvoiceRequest $request, int $id_invoice): JsonResponse
    {
        $invoice = Invoice::with(['registration.participant'])->findOrFail($id_invoice);

        $validated = $request->validated();
        $invoice->perpanjangJatuhTempo($validated['tanggal_jatuh_tempo_baru']);

        return response()->json([
            'success' => true,
            'message' => 'Batas waktu jatuh tempo invoice berhasil diperpanjang.',
            'data' => [
                'id_invoice' => $invoice->id_invoice,
                'nomor_invoice' => $invoice->nomor_invoice,
                'status_pembayaran' => $invoice->status_pembayaran,
                'jumlah_perpanjangan' => $invoice->jumlah_perpanjangan,
                'tanggal_jatuh_tempo_awal' => $invoice->tanggal_jatuh_tempo,
                'tanggal_jatuh_tempo_baru' => $invoice->tanggal_jatuh_tempo_baru,
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Menandai status invoice menjadi Kedaluwarsa secara manual atau by trigger.
     */
    #[OA\Patch(
        path: '/api/invoices/{id_invoice}/expire',
        summary: 'Tandai Invoice Menjadi Kedaluwarsa',
        description: 'Mengubah status pembayaran invoice menjadi Kedaluwarsa secara manual (Khusus Bendahara & Admin).',
        tags: ['Invoice'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id_invoice',
                in: 'path',
                required: true,
                description: 'ID Invoice',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Status invoice berhasil diubah menjadi Kedaluwarsa',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Invoice sudah terverifikasi sehingga tidak dapat diubah')
        ]
    )]
    public function markAsExpired(int $id_invoice): JsonResponse
    {
        $invoice = Invoice::findOrFail($id_invoice);

        if ($invoice->status_pembayaran === 'Terverifikasi') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice yang telah terverifikasi tidak dapat diubah menjadi kedaluwarsa.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $invoice->update(['status_pembayaran' => 'Kedaluwarsa']);

        return response()->json([
            'success' => true,
            'message' => 'Status invoice berhasil diperbarui menjadi Kedaluwarsa.',
            'data' => $invoice,
        ], Response::HTTP_OK);
    }
}
