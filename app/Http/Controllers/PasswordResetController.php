<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\UserAccount;
use App\Mail\ResetPasswordMail;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class PasswordResetController extends Controller
{
    /**
     * Masa berlaku token reset kata sandi dalam menit (60 menit sesuai UI Card).
     */
    protected int $tokenExpiresMinutes = 60;

    /**
     * Kontak bantuan sekretariat seminar sesuai petunjuk di UI.
     */
    protected string $supportContact = 'asic@agr.unand.ac.id';

    /**
     * Mengirimkan link reset kata sandi (KF-08).
     */
    #[OA\Post(
        path: '/api/password/email',
        summary: 'Kirim Tautan Pemulihan Kata Sandi',
        description: 'Menghasilkan token reset kata sandi unik dan mengirimkannya ke alamat email pengguna terdaftar.',
        tags: ['Reset Kata Sandi'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'author@univ.ac.id')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tautan reset kata sandi berhasil dikirim',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string'),
                        new OA\Property(property: 'dispatch_status', type: 'string', example: 'sent'),
                        new OA\Property(property: 'data', type: 'object')
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Format email tidak valid')
        ]
    )]
    #[OA\Post(
        path: '/api/password/forgot',
        summary: 'Kirim Tautan Pemulihan Kata Sandi (Alias)',
        description: 'Endpoint alternatif pengiriman token reset kata sandi.',
        tags: ['Reset Kata Sandi'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'author@univ.ac.id')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Tautan reset kata sandi berhasil dikirim')
        ]
    )]
    public function sendResetLinkEmail(ForgotPasswordRequest $request): JsonResponse|RedirectResponse
    {
        $email = strtolower(trim($request->validated('email')));

        // Pastikan akun terdaftar di tabel user_account
        $user = UserAccount::where('email', $email)->first();
        if (!$user) {
            return response()->json([
                'success' => true,
                'message' => 'Jika alamat email terdaftar, instruksi reset kata sandi akan dikirimkan.',
            ], Response::HTTP_OK);
        }

        // Cek status keaktifan akun
        if ($user->status_akun !== 'Aktif') {
            return response()->json([
                'success' => true,
                'message' => 'Jika alamat email terdaftar, instruksi reset kata sandi akan dikirimkan.',
            ], Response::HTTP_OK);
        }

        // Buat token unik aman
        $rawToken = Str::random(64);

        // Simpan token yang di-hash ke tabel password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($rawToken),
                'created_at' => now(),
            ]
        );

        $frontendUrl = rtrim((string) config('app.frontend_url', env('FRONTEND_URL', '')), '/');
        $resetUrl = $frontendUrl . '/password/reset?token=' . urlencode($rawToken) . '&email=' . urlencode($email);
        Mail::to($user->email)->send(new ResetPasswordMail($resetUrl, $this->tokenExpiresMinutes));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jika alamat email terdaftar, instruksi reset kata sandi akan dikirimkan.',
            ], Response::HTTP_OK);
        }

        return back()->with(
            'status',
            'Jika alamat email terdaftar, instruksi reset kata sandi akan dikirimkan.'
        );
    }

    /**
     * Memproses reset kata sandi baru pengguna (KF-08).
     */
    #[OA\Post(
        path: '/api/password/reset',
        summary: 'Eksekusi Pembaruan Kata Sandi Baru',
        description: 'Memvalidasi token reset dan memperbarui kata sandi akun pengguna.',
        tags: ['Reset Kata Sandi'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'token', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'author@univ.ac.id'),
                    new OA\Property(property: 'token', type: 'string', example: 'abcdef123456...'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'NewSecret123#'),
                    new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'NewSecret123#')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Kata sandi berhasil diperbarui',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Kata sandi berhasil diperbarui.')
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Pengguna tidak ditemukan'),
            new OA\Response(response: 422, description: 'Token tidak valid atau kedaluwarsa')
        ]
    )]
    public function resetPassword(ResetPasswordRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();
        $email = strtolower(trim($validated['email']));
        $token = $validated['token'];

        // Ambil data token dari tabel password_reset_tokens
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record) {
            return $this->responseError(
                $request,
                'Permintaan reset kata sandi tidak valid atau telah kedaluwarsa.'
            );
        }

        // Cek kedaluwarsa token (60 menit)
        $tokenCreatedAt = Carbon::parse($record->created_at);
        if ($tokenCreatedAt->addMinutes($this->tokenExpiresMinutes)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return $this->responseError(
                $request,
                'Tautan reset kata sandi telah kedaluwarsa (lebih dari 60 menit). Silakan ajukan permohonan baru.'
            );
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
                'message' => 'Kata sandi berhasil diperbarui. Silakan masuk menggunakan kata sandi baru Anda.',
            ], Response::HTTP_OK);
        }

        return redirect()->route('login')->with('success', 'Kata sandi Anda berhasil diperbarui. Silakan login.');
    }

    /**
     * Helper untuk mengembalikan respons error baik JSON maupun Web Redirect.
     */
    protected function responseError(
        Request $request,
        string $message,
        int $status = Response::HTTP_UNPROCESSABLE_ENTITY
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
                'support_contact' => $this->supportContact,
            ], $status);
        }

        return back()->withErrors(['email' => $message]);
    }
}
