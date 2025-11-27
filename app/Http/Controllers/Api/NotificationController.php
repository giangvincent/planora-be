<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Notification\NotificationIndexRequest;
use App\Http\Requests\Notification\NotificationSubscribeRequest;
use App\Http\Resources\NotificationResource;
use App\Jobs\SendUserDailyDigest;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;

class NotificationController extends ApiController
{
    public function index(NotificationIndexRequest $request): JsonResponse
    {
        $filters = $request->filters();

        $query = Notification::query()->where('user_id', $request->user()->id)
            ->status($filters['status'] ?? null)
            ->scheduledBetween($filters['start'] ?? null, $filters['end'] ?? null)
            ->orderByDesc('scheduled_for');

        $perPage = max(1, min((int) ($filters['perPage'] ?? 20), 100));
        $paginator = $query->paginate($perPage);

        return $this->respondWithPagination($paginator, NotificationResource::class);
    }

    public function test(): JsonResponse
    {
        $user = request()->user();

        SendUserDailyDigest::dispatch($user->id);

        return $this->respond(['message' => 'Test notification scheduled.']);
    }

    public function subscribe(NotificationSubscribeRequest $request): JsonResponse
    {
        $user = $request->user();
        $settings = $user->settings ?? [];

        $settings['webpush']['subscription'] = $request->subscription();

        $user->forceFill(['settings' => $settings])->save();

        return $this->respond(['message' => 'Subscription saved.']);
    }

    public function unsubscribe(): JsonResponse
    {
        $user = request()->user();
        $settings = $user->settings ?? [];

        unset($settings['webpush']['subscription']);

        $user->forceFill(['settings' => $settings])->save();

        return $this->respond(['message' => 'Subscription removed.']);
    }
}
