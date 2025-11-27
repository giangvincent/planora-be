<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Support\ResourceHelper;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\CalendarEntry */
class CalendarEntryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'taskId' => $this->task_id,
            'startAt' => ResourceHelper::formatDateTime($this->start_at),
            'endAt' => ResourceHelper::formatDateTime($this->end_at),
            'allDay' => (bool) $this->all_day,
            'isGenerated' => (bool) $this->is_generated,
            'createdAt' => ResourceHelper::formatDateTime($this->created_at),
            'updatedAt' => ResourceHelper::formatDateTime($this->updated_at),
            'task' => TaskResource::make($this->whenLoaded('task')),
        ];
    }
}
