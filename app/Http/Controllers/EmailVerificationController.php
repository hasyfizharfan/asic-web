<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\UserAccount;
use App\Notifications\WelcomeParticipant;
use App\Support\MailGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

/** Verifikasi email peserta yang mendaftar sendiri (tautan bertanda tangan di email konfirmasi). */
class EmailVerificationController extends Controller
{
    /** Tautan dari email: tidak perlu login; keabsahan dijamin tanda tangan URL + hash email. */
    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        $user = UserAccount::find($id);
        if (! $user || ! hash_equals(sha1(strtolower($user->email)), $hash)) {
            return redirect()->route('auth.participant.login')->with('verify_status', 'invalid');
        }
        if ($user->email_verified_at === null) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        return redirect()->route('auth.participant.login')
            ->with('verify_status', 'verified')->with('registered_email', $user->email);
    }

    /** Kirim ulang tautan. Jawaban selalu sama agar tidak bisa dipakai menebak email terdaftar. */
    public function resend(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:100']]);
        $user = UserAccount::with(['role', 'participant'])->where('email', $data['email'])->first();

        if ($user && $user->needsEmailVerification() && MailGuard::allows($user->email)) {
            try {
                $user->notifyNow((new WelcomeParticipant)->locale(MailGuard::localeForCountry($user->participant?->country)));
            } catch (Throwable $e) {
                Log::warning('verify.resend_failed', ['id_user' => $user->id_user, 'error' => $e->getMessage()]);
            }
        }

        return response()->json(['success' => true, 'message' => __('asic-auth.verify.resent')]);
    }

    public static function isParticipant(UserAccount $user): bool
    {
        return $user->role?->nama_role === Role::ROLE_PESERTA;
    }
}
