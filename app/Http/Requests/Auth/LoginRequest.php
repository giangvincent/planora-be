<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'deviceName' => ['nullable', 'string', 'max:255'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array{email:string, password:string}
     */
    public function credentials(): array
    {
        return $this->only('email', 'password');
    }

    public function deviceName(): string
    {
        return $this->validated()['deviceName'] ?? 'api';
    }

    public function remember(): bool
    {
        return (bool) ($this->validated()['remember'] ?? false);
    }
}
