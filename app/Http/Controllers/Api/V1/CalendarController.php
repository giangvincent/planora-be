<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CalendarEntry;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * Get calendar entries for a date range
     */
    public function index(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end' => 'required|date',
        ]);

        $entries = $request->user()->calendarEntries()
            ->whereBetween('start_at', [$request->start, $request->end])
            ->with('task')
            ->orderBy('start_at')
            ->get();

        return response()->json([
            'entries' => $entries,
        ]);
    }

    /**
     * Store a new calendar entry
     */
    public function store(Request $request)
    {
        $request->validate([
            'task_id' => 'nullable|exists:tasks,id',
            'start_at' => 'required|date',
            'end_at' => 'nullable|date',
            'all_day' => 'sometimes|boolean',
        ]);

        $entry = $request->user()->calendarEntries()->create($request->all());

        return response()->json([
            'entry' => $entry->load('task'),
        ], 201);
    }

    /**
     * Update calendar entry
     */
    public function update(Request $request, CalendarEntry $entry)
    {
        $this->authorize('update', $entry);

        $request->validate([
            'start_at' => 'sometimes|date',
            'end_at' => 'nullable|date',
            'all_day' => 'sometimes|boolean',
        ]);

        $entry->update($request->all());

        return response()->json([
            'entry' => $entry->fresh(['task']),
        ]);
    }

    /**
     * Delete calendar entry
     */
    public function destroy(Request $request, CalendarEntry $entry)
    {
        $this->authorize('delete', $entry);

        $entry->delete();

        return response()->json([
            'message' => 'Calendar entry deleted successfully',
        ], 204);
    }
}
