<?php

declare(strict_types=1);

namespace App\Services;

class RoadmapParserService
{
    /**
     * Normalize free-form or structured input into a roadmap array.
     *
     * @param string|array<string,mixed> $input
     * @return array{
     *     title: string,
     *     description?: string|null,
     *     phases: array<int, array{
     *         title: string,
     *         description?: string|null,
     *         steps: array<int, array{
     *             title: string,
     *             description?: string|null,
     *             tasks: array<int, array{
     *                 title: string,
     *                 description?: string|null,
     *                 type?: string|null,
     *             }>
     *         }>
     *     }>
     * }
     */
    public function parse(string|array $input): array
    {
        if (is_array($input)) {
            return $this->normalizeArrayInput($input);
        }

        return $this->fromText($input);
    }

    /**
     * @param array<string,mixed> $data
     */
    private function normalizeArrayInput(array $data): array
    {
        return [
            'title' => $data['title'] ?? 'Imported Role',
            'description' => $data['description'] ?? null,
            'phases' => collect($data['phases'] ?? [])->map(function ($phase, int $index) {
                return [
                    'title' => $phase['title'] ?? 'Phase '.($index + 1),
                    'description' => $phase['description'] ?? null,
                    'steps' => collect($phase['steps'] ?? [])->map(function ($step, int $stepIndex) {
                        return [
                            'title' => $step['title'] ?? 'Step '.($stepIndex + 1),
                            'description' => $step['description'] ?? null,
                            'tasks' => collect($step['tasks'] ?? [])->map(function ($task, int $taskIndex) {
                                return [
                                    'title' => $task['title'] ?? 'Task '.($taskIndex + 1),
                                    'description' => $task['description'] ?? null,
                                    'type' => $task['type'] ?? null,
                                ];
                            })->all(),
                        ];
                    })->all(),
                ];
            })->all(),
        ];
    }

    private function fromText(string $text): array
    {
        $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $text) ?: [])));
        $phases = [];
        $currentPhase = null;
        $currentStep = null;

        foreach ($lines as $line) {
            if (str_starts_with($line, '#')) {
                if ($currentPhase) {
                    $phases[] = $currentPhase;
                }
                $currentPhase = [
                    'title' => ltrim($line, "# \t"),
                    'description' => null,
                    'steps' => [],
                ];
                $currentStep = null;
                continue;
            }

            if (str_starts_with($line, '-')) {
                $stepTitle = ltrim($line, "- \t");
                $currentStep = [
                    'title' => $stepTitle,
                    'description' => null,
                    'tasks' => [],
                ];
                if ($currentPhase) {
                    $currentPhase['steps'][] = $currentStep;
                }
                continue;
            }

            if ($currentStep) {
                $currentStep['tasks'][] = [
                    'title' => $line,
                    'description' => null,
                ];
                $currentPhase['steps'][array_key_last($currentPhase['steps'])] = $currentStep;
            } elseif ($currentPhase) {
                $currentPhase['description'] = trim(($currentPhase['description'] ?? '').' '.$line);
            }
        }

        if ($currentPhase) {
            $phases[] = $currentPhase;
        }

        return [
            'title' => $lines[0] ?? 'Imported Role',
            'description' => null,
            'phases' => $phases,
        ];
    }
}
