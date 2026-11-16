<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AbstractReview;
use App\Models\Registration;
use App\Models\RegistrationPeriod;
use App\Models\Role;
use App\Services\ReportService;
use App\Support\PermissionMatrix;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing('role');
        $reports = app(ReportService::class);
        $canSeePayments = app(PermissionMatrix::class)->allows($user, 'verify_payment', 'view_only');
        $payments = $canSeePayments ? $reports->payments([]) : null;

        $activity = collect()
            ->merge(Registration::query()->with('participant.userAccount')->latest('registration_date')->limit(5)->get()->map(fn (Registration $registration) => [
                'type' => 'Registrasi', 'description' => $registration->participant?->userAccount?->email ?? 'Registrasi',
                'timestamp' => $registration->registration_date, 'url' => null,
            ]))
            ->merge($canSeePayments ? DB::table('payment_verification as pv')->join('payment_proof as pp', 'pp.id_payment_proof', '=', 'pv.id_payment_proof')
                ->join('invoice as i', 'i.id_invoice', '=', 'pp.id_invoice')->join('registration as r', 'r.id_registration', '=', 'i.id_registration')
                ->join('participant as p', 'p.id_participant', '=', 'r.id_participant')->join('user_account as u', 'u.id_user', '=', 'p.id_user')
                ->orderByDesc('pv.tanggal_verifikasi')->limit(5)->get(['pv.hasil_verifikasi', 'pv.tanggal_verifikasi', 'u.email'])
                ->map(fn ($payment) => ['type' => 'Verifikasi pembayaran', 'description' => $payment->hasil_verifikasi.' · '.$payment->email, 'timestamp' => Carbon::parse($payment->tanggal_verifikasi), 'url' => null]) : collect())
            ->merge(AbstractReview::query()->with('abstractSubmission')->latest('tanggal_review')->limit(5)->get()->map(fn (AbstractReview $review) => [
                'type' => 'Review abstrak', 'description' => $review->abstractSubmission?->judul_abstrak ?? 'Review abstrak',
                'timestamp' => $review->tanggal_review, 'url' => null,
            ]))
            ->sortByDesc(fn (array $row) => $row['timestamp'] ? (string) $row['timestamp'] : '')
            ->take(10)->values();

        $activePeriod = RegistrationPeriod::query()->with('event')->where('status_periode', 'Aktif')->orderBy('tanggal_mulai')->first();

        return view('admin.overview', [
            'role' => 'admin', 'roleName' => $user->role?->nama_role ?? Role::ROLE_ADMIN,
            'registrations' => $reports->registrations([]), 'abstracts' => $reports->abstracts([]),
            'payments' => $payments, 'canSeePayments' => $canSeePayments,
            'activePeriod' => $activePeriod, 'activity' => $activity,
        ]);
    }

    public function overview(): RedirectResponse
    {
        return redirect()->route('portal.admin.dashboard');
    }

    public function users(): View
    {
        return view('admin.users');
    }

    public function permissions(): View
    {
        return view('admin.permissions');
    }

    public function signatures(Request $request): View
    {
        return view('admin.signatures', [
            'canManageSignatures' => app(PermissionMatrix::class)->allows($request->user(), 'manage_digital_signatures'),
            'signatureRoles' => Role::query()->orderBy('id_role')->get(['id_role', 'nama_role']),
        ]);
    }

    public function templates(): View
    {
        return view('admin.templates');
    }

    public function settings(): View
    {
        return view('admin.settings');
    }

    public function archive(): View
    {
        return view('admin.archive');
    }
}
