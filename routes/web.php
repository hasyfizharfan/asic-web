<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Password Reset Form (browser-rendered view)
Route::get('/password/reset/{token}', function (string $token) {
    return response()->json([
        'token' => $token,
        'email' => request('email'),
    ]);
})->name('password.reset');

if (app()->environment('local') && file_exists(__DIR__.'/fe-preview.php')) {
    require __DIR__.'/fe-preview.php';
}
