<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMyPasswordRequest;
use App\Http\Requests\UpdateMyProfileRequest;
use App\Http\Resources\MyProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class MyProfileController extends Controller
{
    public function update(UpdateMyProfileRequest $request): JsonResponse
    {
        $participant = $request->user()->participant;
        abort_unless($participant, Response::HTTP_NOT_FOUND, 'Profil peserta tidak ditemukan.');
        $participant->fill($request->validated())->save();

        return response()->json(['success' => true, 'message' => __('asic-api.messages.profil_berhasil_diperbarui'), 'data' => new MyProfileResource($request->user()->load('participant'))], Response::HTTP_OK);
    }

    public function updatePassword(UpdateMyPasswordRequest $request): JsonResponse
    {
        $request->user()->forceFill(['password' => Hash::make($request->validated('password'))])->save();

        return response()->json(['success' => true, 'message' => __('asic-api.messages.kata_sandi_berhasil_diperbarui')], Response::HTTP_OK);
    }
}
