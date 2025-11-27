<?php

declare(strict_types=1);

namespace App\Http\Requests\Calendar;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCalendarEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'taskId' => ['sometimes', 'nullable', 'integer', 'exists:tasks,id'],
            'startAt' => ['sometimes', 'date'],
            'endAt' => ['sometimes', 'nullable', 'date', 'after_or_equal:startAt'],
            'allDay' => ['sometimes', 'boolean'],
            'isGenerated' => ['sometimes', 'boolean'],
        ];
    }

    public function payload(): array
    {
        $data = $this->validated();
        $payload = [];

        foreach ([
            'taskId' => 'task_id',
            'startAt' => 'start_at',
            'endAt' => 'end_at',
            'allDay' => 'all_day',
            'isGenerated' => 'is_generated',
        ] as $input => $attribute) {
            if (array_key_exists($input, $data)) {
                $payload[$attribute] = $data[$input];
            }
        }

        return $payload;
    }
}
