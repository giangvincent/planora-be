<?php

declare(strict_types=1);

namespace App\Http\Requests\Calendar;

use Illuminate\Foundation\Http\FormRequest;

class StoreCalendarEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'taskId' => ['nullable', 'integer', 'exists:tasks,id'],
            'startAt' => ['required', 'date'],
            'endAt' => ['nullable', 'date', 'after_or_equal:startAt'],
            'allDay' => ['required', 'boolean'],
            'isGenerated' => ['sometimes', 'boolean'],
        ];
    }

    public function payload(): array
    {
        $data = $this->validated();

        return [
            'task_id' => $data['taskId'] ?? null,
            'start_at' => $data['startAt'],
            'end_at' => $data['endAt'] ?? null,
            'all_day' => (bool) $data['allDay'],
            'is_generated' => (bool) ($data['isGenerated'] ?? false),
        ];
    }
}
