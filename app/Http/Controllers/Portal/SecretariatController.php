<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\AbstractSubmission;
use App\Models\Announcement;
use App\Models\EventSession;
use App\Models\IssuedDocument;
use App\Models\Registration;
use App\Models\Role;
use App\Models\UserAccount;
use App\Services\ReportService;
use App\Support\PermissionMatrix;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SecretariatController extends Controller
{
    public function dashboard(Request $request): View
    {
        /** @var UserAccount $user */
        $user = $request->user();
        $user->loadMissing('role');

        $documented = fn (string $type) => IssuedDocument::query()
            ->where('jenis_dokumen', $type)->select('id_registration');
        $undocumentedAccepted = AbstractSubmission::query()->where('status_abstrak', 'Diterima')
            ->whereNotIn('id_registration', $documented('LOA'))->count();
        $undocumentedRejected = AbstractSubmission::query()->where('status_abstrak', 'Ditolak')
            ->whereNotIn('id_registration', $documented('Penolakan'))->count();

        $canSeePayments = app(PermissionMatrix::class)->allows($user, 'verify_payment', 'view_only');

        return view('secretariat.dashboard', [
            'role' => 'secretariat',
            'roleName' => $user->role?->nama_role ?? Role::ROLE_SEKRETARIAT,
            'userName' => $user->email,
            'registrationStatuses' => Registration::query()->select('registration_status as status', DB::raw('COUNT(*) as total'))
                ->groupBy('registration_status')->orderBy('registration_status')->get(),
            'abstractPending' => AbstractSubmission::where('status_abstrak', 'Diajukan')->count(),
            'abstractDocumentsPending' => $undocumentedAccepted + $undocumentedRejected,
            'nextSessions' => EventSession::query()->with('event:id_event,nama_event')->withCount('attendances')
                ->where('waktu', '>=', now())->orderBy('waktu')->limit(5)->get(),
            'draftAnnouncements' => Announcement::where('status', 'Draft')->count(),
            'latestAbstracts' => AbstractSubmission::query()->with(['registration.participant'])
                ->orderByDesc('tanggal_submit')->limit(5)->get(),
            'paymentSummary' => $canSeePayments ? app(ReportService::class)->payments([]) : null,
        ]);
    }

    public function abstracts(): View
    {
        return view('secretariat.abstracts.index', ['scopes' => config('abstract-scopes', [])]);
    }

    public function showAbstract(int $id): View
    {
        return view('secretariat.abstracts.show', ['id' => $id]);
    }

    public function reregistration(): View
    {
        return view('secretariat.reregistration');
    }

    public function attendance(): View
    {
        return view('secretariat.attendance');
    }

    public function recap(Request $request): View
    {
        $canSeePayments = app(PermissionMatrix::class)->allows($request->user(), 'verify_payment', 'view_only');

        return view('secretariat.recap', ['canSeePayments' => $canSeePayments]);
    }

    public function announcements(): View
    {
        return view('secretariat.announcements');
    }

    public function content(): View
    {
        return view('secretariat.content');
    }
}
