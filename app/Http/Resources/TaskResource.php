<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Support\ResourceHelper;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Task */
class TaskResource extends JsonResource
{
    public function toArray($request): array
    {
        $status = $this->status;
        $priority = $this->priority;

        return [
            'id' => $this->id,
            'goalId' => $this->goal_id,
            'title' => $this->title,
            'notes' => $this->notes,
            'status' => $status instanceof \BackedEnum ? $status->value : $status,
            'priority' => $priority instanceof \BackedEnum ? $priority->value : $priority,
            'estimatedMinutes' => $this->estimated_minutes,
            'actualMinutes' => $this->actual_minutes,
            'dueDate' => ResourceHelper::formatDate($this->due_date),
            'dueAt' => ResourceHelper::formatDateTime($this->due_at),
            'allDay' => (bool) $this->all_day,
            'repeatRule' => $this->repeat_rule,
            'createdAt' => ResourceHelper::formatDateTime($this->created_at),
            'updatedAt' => ResourceHelper::formatDateTime($this->updated_at),
            'goal' => GoalResource::make($this->whenLoaded('goal')),
        ];
    }
}
