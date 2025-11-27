<?php

declare(strict_types=1);

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'notes' => ['sometimes', 'nullable', 'string'],
            'goalId' => ['sometimes', 'nullable', 'integer', 'exists:goals,id'],
            'status' => ['sometimes', Rule::in(TaskStatus::values())],
            'priority' => ['sometimes', Rule::in(TaskPriority::values())],
            'estimatedMinutes' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:1440'],
            'actualMinutes' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:1440'],
            'dueDate' => ['sometimes', 'nullable', 'date'],
            'dueAt' => ['sometimes', 'nullable', 'date'],
            'allDay' => ['sometimes', 'boolean'],
            'repeatRule' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function payload(): array
    {
        $data = $this->validated();

        $payload = [];

        foreach ([
            'title' => 'title',
            'notes' => 'notes',
            'goalId' => 'goal_id',
            'status' => 'status',
            'priority' => 'priority',
            'estimatedMinutes' => 'estimated_minutes',
            'actualMinutes' => 'actual_minutes',
            'dueDate' => 'due_date',
            'dueAt' => 'due_at',
            'allDay' => 'all_day',
            'repeatRule' => 'repeat_rule',
        ] as $inputKey => $attribute) {
            if (array_key_exists($inputKey, $data)) {
                $payload[$attribute] = $data[$inputKey];
            }
        }

        return $payload;
    }
}
