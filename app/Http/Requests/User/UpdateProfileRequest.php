<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Enums\NotificationChannel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'timezone' => ['sometimes', 'timezone:all'],
            'notificationChannel' => ['sometimes', Rule::in(NotificationChannel::values())],
            'settings' => ['sometimes', 'array'],
        ];
    }

    public function payload(): array
    {
        $data = $this->validated();

        if (isset($data['notificationChannel'])) {
            $data['notification_channel'] = $data['notificationChannel'];
            unset($data['notificationChannel']);
        }

        return $data;
    }
}
