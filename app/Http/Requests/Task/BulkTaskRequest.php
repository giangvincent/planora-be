<?php

declare(strict_types=1);

namespace App\Http\Requests\Task;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $taskStatuses = TaskStatus::values();
        $taskPriorities = TaskPriority::values();

        return [
            'create' => ['sometimes', 'array'],
            'create.*.title' => ['required_with:create', 'string', 'max:255'],
            'create.*.notes' => ['nullable', 'string'],
            'create.*.goalId' => ['nullable', 'integer', 'exists:goals,id'],
            'create.*.status' => ['nullable', Rule::in($taskStatuses)],
            'create.*.priority' => ['nullable', Rule::in($taskPriorities)],
            'create.*.estimatedMinutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'create.*.actualMinutes' => ['nullable', 'integer', 'min:0', 'max:1440'],
            'create.*.dueDate' => ['nullable', 'date'],
            'create.*.dueAt' => ['nullable', 'date'],
            'create.*.allDay' => ['required_with:create', 'boolean'],
            'create.*.repeatRule' => ['nullable', 'string', 'max:255'],

            'update' => ['sometimes', 'array'],
            'update.*.id' => ['required_with:update', 'integer', 'exists:tasks,id'],
            'update.*.title' => ['sometimes', 'string', 'max:255'],
            'update.*.notes' => ['sometimes', 'nullable', 'string'],
            'update.*.goalId' => ['sometimes', 'nullable', 'integer', 'exists:goals,id'],
            'update.*.status' => ['sometimes', Rule::in($taskStatuses)],
            'update.*.priority' => ['sometimes', Rule::in($taskPriorities)],
            'update.*.estimatedMinutes' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:1440'],
            'update.*.actualMinutes' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:1440'],
            'update.*.dueDate' => ['sometimes', 'nullable', 'date'],
            'update.*.dueAt' => ['sometimes', 'nullable', 'date'],
            'update.*.allDay' => ['sometimes', 'boolean'],
            'update.*.repeatRule' => ['sometimes', 'nullable', 'string', 'max:255'],

            'delete' => ['sometimes', 'array'],
            'delete.*' => ['integer', 'exists:tasks,id'],
        ];
    }

    public function createPayloads(): array
    {
        return collect($this->validated()['create'] ?? [])->map(function (array $payload): array {
            return $this->mapTaskPayload($payload);
        })->all();
    }

    public function updatePayloads(): array
    {
        return collect($this->validated()['update'] ?? [])->map(function (array $payload): array {
            $taskId = (int) $payload['id'];
            unset($payload['id']);

            return [
                'id' => $taskId,
                'attributes' => $this->mapTaskPayload($payload),
            ];
        })->all();
    }

    /**
     * @return list<int>
     */
    public function deletions(): array
    {
        return collect($this->validated()['delete'] ?? [])->map(fn ($id) => (int) $id)->all();
    }

    private function mapTaskPayload(array $data): array
    {
        $mapping = [
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
        ];

        $payload = [];

        foreach ($mapping as $input => $attribute) {
            if (array_key_exists($input, $data)) {
                $payload[$attribute] = $data[$input];
            }
        }

        return $payload;
    }
}
