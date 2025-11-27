<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;

class WebhookController extends ApiController
{
    public function handle(string $provider): JsonResponse
    {
        // Placeholder implementation for future integration-specific handlers.
        return $this->respond(['message' => sprintf('Webhook for %s received.', $provider)]);
    }
}
