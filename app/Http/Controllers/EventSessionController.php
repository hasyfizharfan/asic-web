<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventSessionRequest;
use App\Models\Event;
use App\Models\EventSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class EventSessionController extends Controller
{
    /**
     * Menampilkan daftar sesi dari suatu event.
     */
    #[OA\Get(
        path: '/api/events/{id_event}/sessions',
        summary: 'Daftar Sesi Kegiatan Event',
        description: 'Mendapatkan seluruh jadwal sesi presentasi/pleno untuk kegiatan seminar tertentu.',
        tags: ['Sesi Acara'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id_event',
                in: 'path',
                required: true,
                description: 'ID Kegiatan Event',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar sesi berhasil dimuat',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'event', type: 'string'),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(type: 'object'))
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Event tidak ditemukan')
        ]
    )]
    public function index(int $id_event): JsonResponse
    {
        $event = Event::findOrFail($id_event);
        $sessions = EventSession::where('id_event', $id_event)
            ->withCount('attendances')
            ->orderBy('waktu', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'event' => $event->nama_event,
            'data' => $sessions,
        ], Response::HTTP_OK);
    }

    /**
     * Membuat sesi baru dan menghasilkan berkas QR Code sesi (Poin 1).
     */
    #[OA\Post(
        path: '/api/sessions',
        summary: 'Buat Sesi Kegiatan Baru dan Generate QR Code',
        description: 'Menambahkan sesi kegiatan baru dan secara otomatis men-generate file SVG QR Code presensi.',
        tags: ['Sesi Acara'],
        security: [['sanctumAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['id_event', 'nama_sesi', 'waktu'],
                properties: [
                    new OA\Property(property: 'id_event', type: 'integer', example: 1),
                    new OA\Property(property: 'nama_sesi', type: 'string', example: 'Parallel Session A - Agronomy Track'),
                    new OA\Property(property: 'waktu', type: 'string', format: 'date-time', example: '2026-08-20 09:00:00')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Sesi acara berhasil dibuat dengan QR code',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'data', type: 'object'),
                        new OA\Property(property: 'qr_image_url', type: 'string')
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Validasi input gagal')
        ]
    )]
    public function store(StoreEventSessionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $token = 'ASIC-SESSION-' . $validated['id_event'] . '-' . strtoupper(Str::random(10));
        $qrRelativePath = 'session_qrcodes/qr_' . $token . '.svg';

        if (class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class)) {
            $svgContent = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                ->size(350)
                ->errorCorrection('H')
                ->generate($token);
            Storage::disk('public')->put($qrRelativePath, (string) $svgContent);
        } else {
            $svgContent = '<svg xmlns="http://www.w3.org/2000/svg" width="350" height="350" viewBox="0 0 350 350">'
                . '<rect width="350" height="350" fill="#ffffff"/>'
                . '<rect x="25" y="25" width="90" height="90" fill="#1e3a8a"/>'
                . '<rect x="35" y="35" width="70" height="70" fill="#ffffff"/>'
                . '<rect x="45" y="45" width="50" height="50" fill="#1e3a8a"/>'
                . '<rect x="235" y="25" width="90" height="90" fill="#1e3a8a"/>'
                . '<rect x="245" y="35" width="70" height="70" fill="#ffffff"/>'
                . '<rect x="255" y="45" width="50" height="50" fill="#1e3a8a"/>'
                . '<rect x="25" y="235" width="90" height="90" fill="#1e3a8a"/>'
                . '<rect x="35" y="245" width="70" height="70" fill="#ffffff"/>'
                . '<rect x="45" y="255" width="50" height="50" fill="#1e3a8a"/>'
                . '<text x="175" y="180" font-family="Arial" font-size="14" text-anchor="middle" fill="#0f172a">'
                . htmlspecialchars($token) . '</text>'
                . '</svg>';
            Storage::disk('public')->put($qrRelativePath, $svgContent);
        }

        $session = EventSession::create([
            'id_event' => $validated['id_event'],
            'nama_sesi' => $validated['nama_sesi'],
            'waktu' => $validated['waktu'],
            'qr_code_path' => $qrRelativePath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sesi acara berhasil dibuat dengan QR Code sesi terlampir.',
            'data' => $session,
            'qr_image_url' => Storage::disk('public')->url($qrRelativePath),
        ], Response::HTTP_CREATED);
    }

    /**
     * Menampilkan informasi QR Code sesi untuk dipindai atau dicetak panitia.
     */
    #[OA\Get(
        path: '/api/sessions/{id_session}/qr',
        summary: 'Tampilkan QR Code Sesi',
        description: 'Mendapatkan URL gambar QR Code sesi untuk dipajang di pintu ruangan sesi atau dipindai panitia/peserta.',
        tags: ['Sesi Acara'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id_session',
                in: 'path',
                required: true,
                description: 'ID Sesi Acara',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Data sesi dan URL QR code berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object'),
                        new OA\Property(property: 'qr_image_url', type: 'string')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Sesi tidak ditemukan')
        ]
    )]
    public function showQr(int $id_session): JsonResponse
    {
        $session = EventSession::with('event')->findOrFail($id_session);

        return response()->json([
            'success' => true,
            'data' => $session,
            'qr_image_url' => $session->qr_code_url,
        ], Response::HTTP_OK);
    }

    /**
     * Menampilkan daftar kehadiran dan status presentasi peserta pada sesi terkait.
     */
    #[OA\Get(
        path: '/api/sessions/{id_session}/attendees',
        summary: 'Daftar Peserta Hadir pada Sesi',
        description: 'Menampilkan seluruh peserta yang telah presensi pada sesi acara beserta status presentasinya (Sekretariat/Admin).',
        tags: ['Sesi Acara'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id_session',
                in: 'path',
                required: true,
                description: 'ID Sesi Acara',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar peserta hadir pada sesi berhasil dimuat',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Sesi tidak ditemukan')
        ]
    )]
    public function attendees(int $id_session): JsonResponse
    {
        $session = EventSession::with(['attendances.registration.participant.userAccount'])
            ->findOrFail($id_session);

        return response()->json([
            'success' => true,
            'data' => $session,
        ], Response::HTTP_OK);
    }
}
