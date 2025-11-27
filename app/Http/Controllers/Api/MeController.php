<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\User\UpdatePasswordRequest;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class MeController extends ApiController
{
    public function show(): JsonResponse
    {
        return $this->respond(UserResource::make(request()->user())->resolve());
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $payload = $request->payload();

        if (isset($payload['settings'])) {
            $payload['settings'] = array_merge($user->settings ?? [], $payload['settings']);
        }

        $user->fill($payload)->save();

        return $this->respond(UserResource::make($user->fresh())->resolve());
    }

    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $credentials = $request->credentials();

        if (! Hash::check($credentials['currentPassword'], $user->password)) {
            return response()->json([
                'message' => 'Current password does not match.',
            ], 422);
        }

        $user->forceFill([
            'password' => Hash::make($credentials['newPassword']),
        ])->save();

        $user->tokens()->where('id', '!=', optional($user->currentAccessToken())->id)->delete();

        return $this->respond(['message' => 'Password updated.']);
    }
}
