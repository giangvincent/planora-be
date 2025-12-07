<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\LearningTask;
use App\Services\AutoCheckService;
use Illuminate\Http\Request;

class AutoCheckController extends Controller
{
    public function __construct(
        private AutoCheckService $autoCheckService
    ) {}

    public function show(Request $request, LearningTask $learningTask)
    {
        $this->authorizeStep($request, $learningTask);

        return response()->json([
            'auto_checks' => $learningTask->autoChecks,
        ]);
    }

    public function run(Request $request, LearningTask $learningTask)
    {
        $this->authorizeStep($request, $learningTask);

        $autoCheck = $learningTask->autoChecks()->first();
        abort_if(! $autoCheck, 404, 'No auto-check configured');

        $validated = $request->validate([
            'answers' => 'required|array',
        ]);

        $result = $this->autoCheckService->runCheck($autoCheck, $request->user(), $validated['answers']);

        return response()->json([
            'result' => $result,
        ]);
    }

    private function authorizeStep(Request $request, LearningTask $learningTask): void
    {
        abort_unless($learningTask->step->phase->role->user_id === $request->user()->id, 403);
    }
}
