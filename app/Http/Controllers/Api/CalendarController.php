<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Calendar\StoreCalendarEntryRequest;
use App\Http\Requests\Calendar\UpdateCalendarEntryRequest;
use App\Http\Resources\CalendarEntryResource;
use App\Models\CalendarEntry;
use App\Models\Task;
use App\Services\CalendarService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;

class CalendarController extends ApiController
{
    public function __construct(private readonly CalendarService $calendarService)
    {
    }

    public function index(): JsonResponse
    {
        $start = request()->query('start');
        $end = request()->query('end');

        $entries = $this->calendarService->getEntries(request()->user(), $start, $end);

        return $this->respond($entries);
    }

    public function store(StoreCalendarEntryRequest $request): JsonResponse
    {
        $this->authorize('create', CalendarEntry::class);

        $payload = $this->preparePayload($request->payload(), $request->user()->id);

        $entry = CalendarEntry::query()->create($payload);

        return $this->respond(CalendarEntryResource::make($entry->fresh('task'))->resolve(), [], 201);
    }

    public function update(UpdateCalendarEntryRequest $request, CalendarEntry $entry): JsonResponse
    {
        $this->authorize('update', $entry);

        $payload = $this->preparePayload($request->payload(), $request->user()->id, allowEmpty: true);
        $entry->fill($payload)->save();

        return $this->respond(CalendarEntryResource::make($entry->fresh('task'))->resolve());
    }

    public function destroy(CalendarEntry $entry): JsonResponse
    {
        $this->authorize('delete', $entry);

        $entry->delete();

        return response()->json([], 204);
    }

    private function preparePayload(array $payload, int $userId, bool $allowEmpty = false): array
    {
        if (isset($payload['task_id'])) {
            $task = Task::query()->where('user_id', $userId)->find($payload['task_id']);

            if (! $task) {
                throw (new ModelNotFoundException())->setModel(Task::class, $payload['task_id']);
            }
        }

        if (! $allowEmpty) {
            $payload['user_id'] = $userId;
        }

        return $payload;
    }
}
