<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Support\ResourceHelper;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Goal */
class GoalResource extends JsonResource
{
    public function toArray($request): array
    {
        $status = $this->status;

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'targetDate' => ResourceHelper::formatDate($this->target_date),
            'status' => $status instanceof \BackedEnum ? $status->value : $status,
            'progress' => $this->progress,
            'color' => $this->color,
            'createdAt' => ResourceHelper::formatDateTime($this->created_at),
            'updatedAt' => ResourceHelper::formatDateTime($this->updated_at),
        ];
    }
}
