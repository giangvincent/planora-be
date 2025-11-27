<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Support\ResourceHelper;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Notification */
class NotificationResource extends JsonResource
{
    public function toArray($request): array
    {
        $status = $this->status;
        $channel = $this->channel;

        return [
            'id' => $this->id,
            'taskId' => $this->task_id,
            'channel' => $channel instanceof \BackedEnum ? $channel->value : $channel,
            'status' => $status instanceof \BackedEnum ? $status->value : $status,
            'scheduledFor' => ResourceHelper::formatDateTime($this->scheduled_for),
            'sentAt' => ResourceHelper::formatDateTime($this->sent_at),
            'payload' => $this->payload,
            'error' => $this->error,
            'createdAt' => ResourceHelper::formatDateTime($this->created_at),
            'updatedAt' => ResourceHelper::formatDateTime($this->updated_at),
            'task' => TaskResource::make($this->whenLoaded('task')),
        ];
    }
}
