<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'currentPassword' => ['required', 'string'],
            'newPassword' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * @return array{currentPassword:string, newPassword:string}
     */
    public function credentials(): array
    {
        $data = $this->validated();

        return [
            'currentPassword' => $data['currentPassword'],
            'newPassword' => $data['newPassword'],
        ];
    }
}
