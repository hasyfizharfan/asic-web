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
use Symfony\Component\HttpFoundation\Response;

class RegistrationController extends Controller
{
    /**
     * Menampilkan daftar registrasi milik peserta yang sedang login,
     * atau seluruh registrasi jika diakses oleh panitia (Admin / Sekretariat).
     */
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

        // Jika bukan Admin / Sekretariat, hanya tampilkan registrasi milik sendiri
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

        // Filter opsional berdasarkan id_event
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

        // Otorisasi: Peserta hanya dapat melihat pendaftaran miliknya sendiri
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
    public function store(StoreRegistrationRequest $request): JsonResponse|RedirectResponse
    {
        /** @var \App\Models\UserAccount $user */
        $user = Auth::user();

        // 1. Dapatkan profil participant terkait akun pengguna
        $participant = $user->participant;
        if (!$participant) {
            $participant = Participant::create([
                'id_user' => $user->id_user,
                'first_name' => explode('@', $user->email)[0],
            ]);
        }

        $validated = $request->validated();

        // 2. Validasi periode pendaftaran masih aktif dan dalam rentang tanggal (KF-11)
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

        // 3. Cek apakah peserta sudah pernah mendaftar pada event yang sama (dengan status aktif)
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

        // 4. Buat record pendaftaran dan add-on dalam transaksi database
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

            // Simpan layanan tambahan (add-on) bila dipilih peserta
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

        return redirect()->route('registrations.show', $registration->id_registration)
            ->with('success', 'Pendaftaran kegiatan seminar berhasil disimpan.');
    }
}

