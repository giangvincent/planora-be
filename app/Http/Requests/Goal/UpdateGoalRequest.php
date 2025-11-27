<?php

declare(strict_types=1);

namespace App\Http\Requests\Goal;

use App\Enums\GoalStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', Rule::in(GoalStatus::values())],
            'progress' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'targetDate' => ['sometimes', 'nullable', 'date'],
            'color' => ['sometimes', 'nullable', 'regex:/^#?[0-9a-fA-F]{6}$/'],
        ];
    }

    public function payload(): array
    {
        $data = $this->validated();

        if (array_key_exists('targetDate', $data)) {
            $data['target_date'] = $data['targetDate'];
            unset($data['targetDate']);
        }

        return $data;
    }
}
