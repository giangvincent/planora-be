<?php

declare(strict_types=1);

namespace App\Http\Requests\Goal;

use Illuminate\Foundation\Http\FormRequest;

class StoreGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'targetDate' => ['nullable', 'date'],
            'color' => ['nullable', 'regex:/^#?[0-9a-fA-F]{6}$/'],
        ];
    }

    public function payload(): array
    {
        $data = $this->validated();
        $data['target_date'] = $data['targetDate'] ?? null;
        unset($data['targetDate']);

        return $data;
    }
}
