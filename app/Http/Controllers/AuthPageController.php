<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuthPageController extends Controller
{
    private const PORTAL_VIEWS = [
        'participant' => 'participant',
        'secretariat' => 'secretariat',
        'treasurer' => 'treasurer',
        'reviewer' => 'reviewer',
        'admin' => 'admin',
    ];

    public function login(string $portal = 'participant'): View|RedirectResponse
    {
        abort_unless(isset(self::PORTAL_VIEWS[$portal]), 404);

        if (auth()->check()) {
            return redirect()->to($this->redirectForRole(auth()->user()->role?->nama_role));
        }

        return view('auth.'.self::PORTAL_VIEWS[$portal], ['asic' => $this->asicDetails()]);
    }

    public function passwordReset(string $token): View
    {
        return view('auth.password-reset', [
            'token' => $token,
            'email' => request()->query('email', ''),
            'asic' => $this->asicDetails(),
        ]);
    }

    public function redirectForRole(?string $role): string
    {
        return match ($role) {
            Role::ROLE_PESERTA => route('portal.participant.dashboard'),
            Role::ROLE_SEKRETARIAT => route('portal.secretariat.dashboard'),
            Role::ROLE_BENDAHARA => route('portal.treasurer.dashboard'),
            Role::ROLE_TIM_ABSTRAK => route('portal.reviewer.dashboard'),
            Role::ROLE_ADMIN => route('portal.admin.dashboard'),
            default => route('login'),
        };
    }

    private function asicDetails(): array
    {
        return [
            'email' => config('mail.from.address', 'asic@agr.unand.ac.id'),
            'short_title' => 'Agrifood System International Conference',
            'dates' => 'August 12–13, 2027',
            'venue' => 'Universitas Andalas, Padang',
            'organizer' => 'Faculty of Agriculture, Universitas Andalas',
        ];
    }
}
