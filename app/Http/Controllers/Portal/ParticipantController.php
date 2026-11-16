<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\IssuedDocument;
use App\Models\Registration;
use App\Models\RegistrationFee;
use App\Models\UserAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParticipantController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('portal.participant.dashboard');
    }

    private function page(Request $request, string $view): View
    {
        /** @var UserAccount $user */
        $user = $request->user();
        $user->loadMissing(['role', 'participant']);
        $registrations = Registration::query()
            ->where('id_participant', $user->participant?->id_participant ?? 0)
            ->with(['event', 'registrationPeriod', 'invoice', 'abstractSubmission', 'participantFiles', 'attendance'])
            ->latest('id_registration')->get();
        $documents = IssuedDocument::query()->whereIn('id_registration', $registrations->pluck('id_registration'))->get();

        return view($view, [
            'role' => 'participant',
            'roleName' => $user->participant?->full_name ?: __('asic-nav.portal_participant'),
            'userName' => $user->participant?->full_name ?: $user->email,
            'participant' => $user->participant,
            'registrations' => $registrations,
            'registration' => $registrations->first(),
            'documents' => $documents,
            'events' => $view === 'participant.registration' ? Event::query()->where('status_event', 'Aktif')->get() : collect(),
            'fees' => $view === 'participant.registration' ? RegistrationFee::query()->whereHas('event', fn ($q) => $q->where('status_event', 'Aktif'))->whereHas('registrationPeriod', fn ($q) => $q->where('status_periode', 'Aktif'))->with(['event', 'registrationPeriod'])->get() : collect(),
        ]);
    }

    public function dashboard(Request $request): View
    {
        return $this->page($request, 'participant.dashboard');
    }

    public function registration(Request $request): View
    {
        return $this->page($request, 'participant.registration');
    }

    public function abstract(Request $request): View
    {
        return $this->page($request, 'participant.abstract');
    }

    public function checkout(Request $request): View
    {
        return $this->page($request, 'participant.payment.checkout');
    }

    public function paymentStatus(Request $request): View
    {
        return $this->page($request, 'participant.payment.status');
    }

    public function documents(Request $request): View
    {
        return $this->page($request, 'participant.documents');
    }

    public function attendance(Request $request): View
    {
        return $this->page($request, 'participant.attendance-qr');
    }

    public function fullPaper(Request $request): View
    {
        return $this->page($request, 'participant.full-paper');
    }

    public function profile(Request $request): View
    {
        return $this->page($request, 'participant.profile');
    }

    public function refund(Request $request): View
    {
        return $this->page($request, 'participant.refund');
    }

    public function notifications(Request $request): View
    {
        return $this->page($request, 'participant.notifications');
    }

    public function certificate(Request $request): View
    {
        return $this->page($request, 'participant.certificate');
    }
}
