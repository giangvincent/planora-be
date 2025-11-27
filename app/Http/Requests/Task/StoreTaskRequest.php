<?php

declare(strict_types=1);

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'goalId' => ['nullable', 'integer', 'exists:goals,id'],
            'status' => ['nullable', Rule::in(TaskStatus::values())],
            'priority' => ['nullable', Rule::in(TaskPriority::values())],
            'estimatedMinutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'actualMinutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'dueDate' => ['nullable', 'date', 'required_if:allDay,true'],
            'dueAt' => ['nullable', 'date', 'required_unless:allDay,true'],
            'allDay' => ['required', 'boolean'],
            'repeatRule' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function payload(): array
    {
        $data = $this->validated();

        $payload = [
            'title' => $data['title'],
            'notes' => $data['notes'] ?? null,
            'goal_id' => $data['goalId'] ?? null,
            'status' => $data['status'] ?? null,
            'priority' => $data['priority'] ?? null,
            'estimated_minutes' => $data['estimatedMinutes'] ?? null,
            'actual_minutes' => $data['actualMinutes'] ?? null,
            'due_date' => $data['dueDate'] ?? null,
            'due_at' => $data['dueAt'] ?? null,
            'all_day' => (bool) $data['allDay'],
            'repeat_rule' => $data['repeatRule'] ?? null,
        ];

        return $payload;
    }
}
