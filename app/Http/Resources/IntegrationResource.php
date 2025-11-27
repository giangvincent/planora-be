<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Support\ResourceHelper;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Integration */
class IntegrationResource extends JsonResource
{
    public function toArray($request): array
    {
        $provider = $this->provider;
        $status = $this->status;

        return [
            'id' => $this->id,
            'provider' => $provider instanceof \BackedEnum ? $provider->value : $provider,
            'status' => $status instanceof \BackedEnum ? $status->value : $status,
            'expiresAt' => ResourceHelper::formatDateTime($this->expires_at),
            'settings' => $this->settings,
            'createdAt' => ResourceHelper::formatDateTime($this->created_at),
            'updatedAt' => ResourceHelper::formatDateTime($this->updated_at),
        ];
    }
}
