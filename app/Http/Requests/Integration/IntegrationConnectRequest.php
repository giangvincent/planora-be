<?php

declare(strict_types=1);

namespace App\Http\Requests\Integration;

use Illuminate\Foundation\Http\FormRequest;

class IntegrationConnectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'accessToken' => ['required', 'string'],
            'refreshToken' => ['nullable', 'string'],
            'expiresAt' => ['nullable', 'date'],
            'settings' => ['nullable', 'array'],
        ];
    }

    public function payload(): array
    {
        $data = $this->validated();

        return [
            'access_token' => $data['accessToken'],
            'refresh_token' => $data['refreshToken'] ?? null,
            'expires_at' => $data['expiresAt'] ?? null,
            'settings' => $data['settings'] ?? [],
        ];
    }
}
