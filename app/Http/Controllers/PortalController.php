<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Invoice;
use App\Models\Registration;
use App\Models\Role;
use App\Models\UserAccount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function dashboard(Request $request): View|RedirectResponse
    {
        /** @var UserAccount $user */
        $user = $request->user();
        $user->loadMissing(['role', 'participant']);

        $role = match ($user->role?->nama_role) {
            Role::ROLE_PESERTA => 'participant',
            Role::ROLE_SEKRETARIAT => 'secretariat',
            Role::ROLE_BENDAHARA => 'treasurer',
            Role::ROLE_TIM_ABSTRAK => 'reviewer',
            Role::ROLE_ADMIN => 'admin',
            default => abort(403),
        };

        if ($role === 'reviewer') {
            return redirect()->route('portal.reviewer.dashboard');
        }
        if ($role === 'secretariat') {
            return redirect()->route('portal.secretariat.dashboard');
        }

        $registrations = Registration::query()
            ->with(['participant.userAccount', 'event', 'abstractSubmission', 'invoice'])
            ->when($role === 'participant', function (Builder $query) use ($user): void {
                $participantId = $user->participant?->id_participant;
                $query->where('id_participant', $participantId ?? 0);
            });

        $invoices = Invoice::query()
            ->with(['registration.participant.userAccount', 'registration.event'])
            ->when($role === 'participant', function (Builder $query) use ($user): void {
                $participantId = $user->participant?->id_participant;
                $query->whereHas('registration', fn (Builder $registrationQuery) => $registrationQuery
                    ->where('id_participant', $participantId ?? 0));
            });

        $abstracts = Registration::query()
            ->whereHas('abstractSubmission')
            ->when($role === 'participant', function (Builder $query) use ($user): void {
                $participantId = $user->participant?->id_participant;
                $query->where('id_participant', $participantId ?? 0);
            });

        $stats = [
            'registrations' => (clone $registrations)->count(),
            'abstracts' => $abstracts->count(),
            'pending_payments' => (clone $invoices)->whereIn('status_pembayaran', ['Menunggu Pembayaran', 'Menunggu Verifikasi'])->count(),
            'active_events' => Event::query()->where('status_event', 'Aktif')->count(),
        ];

        return view('portals.dashboard', [
            'role' => $role,
            'roleName' => $user->role?->nama_role ?? 'ASIC Portal',
            'userName' => $user->participant?->full_name ?: $user->email,
            'stats' => $stats,
            'latestRegistrations' => (clone $registrations)->latest('registration_date')->limit(5)->get(),
            'latestInvoices' => (clone $invoices)->latest('tanggal_invoice')->limit(5)->get(),
        ]);
    }

    public function redirectToDashboard(Request $request)
    {
        $role = $request->user()?->role?->nama_role;
        $route = match ($role) {
            Role::ROLE_PESERTA => 'portal.participant.dashboard',
            Role::ROLE_SEKRETARIAT => 'portal.secretariat.dashboard',
            Role::ROLE_BENDAHARA => 'portal.treasurer.dashboard',
            Role::ROLE_TIM_ABSTRAK => 'portal.reviewer.dashboard',
            Role::ROLE_ADMIN => 'portal.admin.dashboard',
            default => 'login',
        };

        return redirect()->route($route);
    }
}
