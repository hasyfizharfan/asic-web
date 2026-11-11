<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\UserAccount;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class PasswordResetController extends Controller
{
    /**
     * Masa berlaku token reset kata sandi dalam menit.
     */
    protected int $tokenExpiresMinutes = 60;

    /**
     * Mengirimkan link reset kata sandi (KF-08).
     *
     * Membuat token unik, menyimpannya di tabel `password_reset_tokens`,
     * dan mengembalikan respons/mengirimkan email reset kata sandi.
     */
    public function sendResetLinkEmail(ForgotPasswordRequest $request): JsonResponse|RedirectResponse
    {
        $email = strtolower(trim($request->validated('email')));

        // Pastikan akun terdaftar
        $user = UserAccount::where('email', $email)->first();
        if (!$user) {
            return $this->responseError($request, 'Alamat email tidak ditemukan dalam sistem.', Response::HTTP_NOT_FOUND);
        }

        // Buat token unik
        $rawToken = Str::random(64);

        // Simpan token yang di-hash ke tabel password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($rawToken),
                'created_at' => now(),
            ]
        );

        // URL reset kata sandi
        $resetUrl = url(route('password.reset', [
            'token' => $rawToken,
            'email' => $email,
        ], false));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Link instruksi reset kata sandi telah berhasil dibuat.',
                'data' => [
                    'email' => $email,
                    'reset_token' => $rawToken,
                    'reset_url' => $resetUrl,
                    'expires_in_minutes' => $this->tokenExpiresMinutes,
                ],
            ], Response::HTTP_OK);
        }

        return back()->with('status', 'Link instruksi reset kata sandi telah dikirimkan ke email Anda.');
    }

    /**
     * Memproses reset kata sandi baru pengguna (KF-08).
     *
     * Memvalidasi token dari tabel `password_reset_tokens`, memperbarui password
     * pada `user_account`, dan menghapus token setelah berhasil digunakan.
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();
        $email = strtolower(trim($validated['email']));
        $token = $validated['token'];

        // Ambil data token dari tabel password_reset_tokens
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record) {
            return $this->responseError($request, 'Permintaan reset kata sandi tidak valid atau telah kadaluwarsa.');
        }

        // Cek kedaluwarsa token (60 menit)
        $tokenCreatedAt = Carbon::parse($record->created_at);
        if ($tokenCreatedAt->addMinutes($this->tokenExpiresMinutes)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return $this->responseError($request, 'Token reset kata sandi telah kedaluwarsa. Silakan ajukan permohonan baru.');
        }

        // Cek kecocokan token
        if (!Hash::check($token, $record->token)) {
            return $this->responseError($request, 'Token reset kata sandi tidak valid.');
        }

        // Cari user account
        $user = UserAccount::where('email', $email)->first();
        if (!$user) {
            return $this->responseError($request, 'Pengguna tidak ditemukan.', Response::HTTP_NOT_FOUND);
        }

        // Update kata sandi baru pada tabel user_account
        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Hapus token yang telah berhasil digunakan
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Kata sandi berhasil diperbarui. Silakan login menggunakan kata sandi baru Anda.',
            ], Response::HTTP_OK);
        }

        return redirect()->route('login')->with('success', 'Kata sandi Anda berhasil diperbarui. Silakan login.');
    }

    /**
     * Helper untuk mengembalikan respons error baik JSON maupun Web Redirect.
     */
    protected function responseError(Request $request, string $message, int $status = Response::HTTP_UNPROCESSABLE_ENTITY): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        return back()->withErrors(['email' => $message]);
    }
}

