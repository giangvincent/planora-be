<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\IntegrationProvider;
use App\Enums\IntegrationStatus;
use App\Http\Requests\Integration\IntegrationConnectRequest;
use App\Http\Resources\IntegrationResource;
use App\Models\Integration;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class IntegrationController extends ApiController
{
    public function index(): JsonResponse
    {
        $integrations = request()->user()->integrations()->get();

        return $this->respond(IntegrationResource::collection($integrations)->resolve());
    }

    public function connect(IntegrationConnectRequest $request, string $provider): JsonResponse
    {
        $providerEnum = $this->resolveProvider($provider);

        $user = $request->user();
        $payload = $request->payload();
        $payload['status'] = IntegrationStatus::Connected->value;

        /** @var Integration $integration */
        $integration = $user->integrations()->updateOrCreate(
            ['provider' => $providerEnum->value],
            $payload
        );

        return $this->respond(IntegrationResource::make($integration->fresh())->resolve(), [], 201);
    }

    public function disconnect(string $provider): JsonResponse
    {
        $providerEnum = $this->resolveProvider($provider);
        $user = request()->user();

        $integration = $user->integrations()->where('provider', $providerEnum->value)->first();

        if (! $integration) {
            return response()->json([
                'message' => 'Integration not found.',
            ], 404);
        }

        $integration->fill([
            'status' => IntegrationStatus::Revoked->value,
            'access_token' => null,
            'refresh_token' => null,
            'expires_at' => null,
        ])->save();

        return $this->respond(IntegrationResource::make($integration->fresh())->resolve());
    }

    private function resolveProvider(string $provider): IntegrationProvider
    {
        $provider = strtolower($provider);

        foreach (IntegrationProvider::cases() as $case) {
            if ($case->value === $provider) {
                return $case;
            }
        }

        abort(404, 'Unsupported integration provider.');
    }
}
