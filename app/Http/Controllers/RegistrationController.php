<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegistrationRequest;
use App\Models\AddOn;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\RegistrationAddOn;
use App\Models\RegistrationPeriod;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class RegistrationController extends Controller
{
    /**
     * Menampilkan daftar registrasi milik peserta atau seluruh registrasi jika diakses panitia.
     */
    #[OA\Get(
        path: '/api/registrations',
        summary: 'Daftar Pendaftaran Seminar',
        description: 'Mengambil daftar pendaftaran milik pengguna yang sedang login atau seluruh data jika memiliki peran Admin / Sekretariat.',
        tags: ['Pendaftaran'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id_event',
                in: 'query',
                required: false,
                description: 'Filter berdasarkan ID Event seminar',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar pendaftaran berhasil diambil',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Belum terautentikasi')
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\UserAccount $user */
        $user = Auth::user();
        $query = Registration::with([
            'participant.userAccount',
            'event',
            'registrationPeriod',
            'addOns',
            'abstractSubmission',
            'attendance',
            'participantFiles',
        ]);

        if (!$user->hasRole([Role::ROLE_ADMIN, Role::ROLE_SEKRETARIAT])) {
            $participant = $user->participant;
            if (!$participant) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                ], Response::HTTP_OK);
            }
            $query->where('id_participant', $participant->id_participant);
        }

        if ($request->filled('id_event')) {
            $query->where('id_event', $request->integer('id_event'));
        }

        $registrations = $query->orderBy('id_registration', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $registrations,
        ], Response::HTTP_OK);
    }

    /**
     * Menampilkan detail pendaftaran tertentu (KF-10).
     */
    #[OA\Get(
        path: '/api/registrations/{id_registration}',
        summary: 'Detail Pendaftaran Seminar',
        description: 'Mengambil informasi lengkap pendaftaran beserta relasi invoice, abstrak, sesi, dan berkas.',
        tags: ['Pendaftaran'],
        security: [['sanctumAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id_registration',
                in: 'path',
                required: true,
                description: 'ID Pendaftaran',
                schema: new OA\Schema(type: 'integer', example: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Detail pendaftaran berhasil dimuat',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 403, description: 'Akses ditolak (bukan pemilik atau panitia)'),
            new OA\Response(response: 404, description: 'Data pendaftaran tidak ditemukan')
        ]
    )]
    public function show(int $id_registration): JsonResponse
    {
        $registration = Registration::with([
            'participant.userAccount',
            'event',
            'registrationPeriod',
            'addOns',
            'abstractSubmission',
            'attendance',
            'participantFiles',
        ])->findOrFail($id_registration);

        /** @var \App\Models\UserAccount $user */
        $user = Auth::user();

        if (
            !$user->hasRole([Role::ROLE_ADMIN, Role::ROLE_SEKRETARIAT]) &&
            $registration->participant?->id_user !== $user->id_user
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk melihat data pendaftaran ini.',
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'success' => true,
            'data' => $registration,
        ], Response::HTTP_OK);
    }

    /**
     * Menyimpan data pendaftaran event seminar (KF-10).
     */
    #[OA\Post(
        path: '/api/registrations',
        summary: 'Pendaftaran Kegiatan Seminar Baru',
        description: 'Membuat pendaftaran baru untuk event seminar beserta pilihan add-on (paket wisata/akomodasi).',
        tags: ['Pendaftaran'],
        security: [['sanctumAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['id_event', 'id_period', 'registration_type', 'attendance_mode', 'participation_type'],
                properties: [
                    new OA\Property(property: 'id_event', type: 'integer', example: 1),
                    new OA\Property(property: 'id_period', type: 'integer', example: 1),
                    new OA\Property(property: 'registration_type', type: 'string', enum: ['Early Bird', 'Regular', 'Late'], example: 'Early Bird'),
                    new OA\Property(property: 'attendance_mode', type: 'string', enum: ['Luring', 'Daring'], example: 'Luring'),
                    new OA\Property(property: 'participation_type', type: 'string', enum: ['Oral Presenter', 'Poster Presenter', 'Listener'], example: 'Oral Presenter'),
                    new OA\Property(property: 'publication_option', type: 'string', enum: ['Ya', 'Tidak'], example: 'Ya'),
                    new OA\Property(
                        property: 'add_ons',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id_add_on', type: 'integer', example: 1),
                                new OA\Property(property: 'jumlah', type: 'integer', example: 1)
                            ]
                        )
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Pendaftaran kegiatan seminar berhasil disimpan',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 409, description: 'Peserta sudah terdaftar pada kegiatan ini'),
            new OA\Response(response: 422, description: 'Periode pendaftaran ditutup atau input tidak valid')
        ]
    )]
    public function store(StoreRegistrationRequest $request): JsonResponse|RedirectResponse
    {
        /** @var \App\Models\UserAccount $user */
        $user = Auth::user();

        $participant = $user->participant;
        if (!$participant) {
            $participant = Participant::create([
                'id_user' => $user->id_user,
                'first_name' => explode('@', $user->email)[0],
            ]);
        }

        $validated = $request->validated();

        $period = RegistrationPeriod::findOrFail($validated['id_period']);
        $today = Carbon::today();

        if (
            $period->status_periode !== 'Aktif' ||
            $today->lt(Carbon::parse($period->tanggal_mulai)) ||
            $today->gt(Carbon::parse($period->tanggal_selesai))
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Periode pendaftaran yang dipilih sudah ditutup atau tidak aktif.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $existing = Registration::where('id_participant', $participant->id_participant)
            ->where('id_event', $validated['id_event'])
            ->where('registration_status', 'Terdaftar')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah terdaftar pada kegiatan seminar ini.',
                'data' => $existing,
            ], Response::HTTP_CONFLICT);
        }

        $registration = DB::transaction(function () use ($validated, $participant) {
            $newRegistration = Registration::create([
                'id_participant' => $participant->id_participant,
                'id_event' => $validated['id_event'],
                'id_period' => $validated['id_period'],
                'registration_type' => $validated['registration_type'],
                'attendance_mode' => $validated['attendance_mode'],
                'participation_type' => $validated['participation_type'],
                'publication_option' => $validated['publication_option'] ?? 'Tidak',
                'registration_date' => now(),
                'registration_status' => 'Terdaftar',
            ]);

            if (!empty($validated['add_ons'])) {
                foreach ($validated['add_ons'] as $item) {
                    $addOn = AddOn::find($item['id_add_on']);
                    if ($addOn) {
                        $jumlah = (int) ($item['jumlah'] ?? 1);
                        $subtotal = $addOn->harga * $jumlah;

                        RegistrationAddOn::create([
                            'id_registration' => $newRegistration->id_registration,
                            'id_add_on' => $addOn->id_add_on,
                            'jumlah' => $jumlah,
                            'subtotal' => $subtotal,
                        ]);
                    }
                }
            }

            return $newRegistration;
        });

        $registration->load(['event', 'registrationPeriod', 'addOns', 'participant']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Pendaftaran kegiatan seminar berhasil disimpan.',
                'data' => $registration,
            ], Response::HTTP_CREATED);
        }

        return redirect()->route('api.registrations.show', $registration->id_registration)
            ->with('success', 'Pendaftaran kegiatan seminar berhasil disimpan.');
    }
}
