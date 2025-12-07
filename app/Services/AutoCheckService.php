<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AutoCheckType;
use App\Models\AutoCheck;
use App\Models\AutoCheckResult;
use App\Models\User;
use App\Services\Gamification\GamificationService;

class AutoCheckService
{
    public function __construct(
        private GamificationService $gamificationService
    ) {}

    /**
     * @param array<string, mixed> $answers
     */
    public function runCheck(AutoCheck $autoCheck, User $user, array $answers): AutoCheckResult
    {
        [$score, $maxScore, $passed, $feedback] = match ($autoCheck->type) {
            AutoCheckType::Quiz => $this->evaluateQuiz($autoCheck, $answers),
            AutoCheckType::TextKeywords => $this->evaluateTextKeywords($autoCheck, $answers),
            AutoCheckType::Rating => $this->evaluateRating($autoCheck, $answers),
            AutoCheckType::Code => $this->evaluateCode($autoCheck, $answers),
        };

        $result = $autoCheck->results()->create([
            'user_id' => $user->id,
            'score' => $score,
            'max_score' => $maxScore,
            'passed' => $passed,
            'attempt_data' => [
                'submitted' => $answers,
                'feedback' => $feedback,
            ],
        ]);

        if ($passed) {
            $this->gamificationService->grantAutoCheckBonus($user);
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $answers
     * @return array{int,int,bool,array<string,mixed>}
     */
    private function evaluateQuiz(AutoCheck $autoCheck, array $answers): array
    {
        $config = $autoCheck->config ?? [];
        $questions = $config['questions'] ?? [];
        $submitted = $answers['answers'] ?? $answers;
        $totalQuestions = max(count($questions), 1);
        $correct = 0;

        foreach ($questions as $index => $question) {
            $expected = $question['correct_index'] ?? null;
            $userAnswer = $submitted[$index] ?? null;
            if ($expected !== null && (int) $userAnswer === (int) $expected) {
                $correct++;
            }
        }

        $score = (int) round(($correct / $totalQuestions) * 100);
        $passingScore = $config['passing_score'] ?? 70;

        return [$score, 100, $score >= $passingScore, ['correct' => $correct, 'total' => $totalQuestions]];
    }

    /**
     * @param array<string, mixed> $answers
     * @return array{int,int,bool,array<string,mixed>}
     */
    private function evaluateTextKeywords(AutoCheck $autoCheck, array $answers): array
    {
        $config = $autoCheck->config ?? [];
        $keywords = array_map('strtolower', $config['keywords'] ?? []);
        $text = strtolower((string) ($answers['text'] ?? implode(' ', $answers)));

        $matches = 0;
        foreach ($keywords as $keyword) {
            if ($keyword !== '' && str_contains($text, $keyword)) {
                $matches++;
            }
        }

        $totalKeywords = max(count($keywords), 1);
        $score = (int) round(($matches / $totalKeywords) * 100);
        $minMatches = $config['min_matches'] ?? ceil($totalKeywords * 0.6);

        return [$score, 100, $matches >= $minMatches, ['matches' => $matches, 'keywords' => $keywords]];
    }

    /**
     * @param array<string, mixed> $answers
     * @return array{int,int,bool,array<string,mixed>}
     */
    private function evaluateRating(AutoCheck $autoCheck, array $answers): array
    {
        $config = $autoCheck->config ?? [];
        $rating = (int) ($answers['rating'] ?? 0);
        $max = (int) ($config['max_score'] ?? 5);
        $passing = (int) ($config['passing_score'] ?? ceil($max * 0.6));

        $clampedRating = max(0, min($rating, $max));
        $score = (int) round(($clampedRating / max($max, 1)) * 100);

        return [$score, 100, $clampedRating >= $passing, ['rating' => $clampedRating, 'max' => $max]];
    }

    /**
     * @param array<string, mixed> $answers
     * @return array{int,int,bool,array<string,mixed>}
     */
    private function evaluateCode(AutoCheck $autoCheck, array $answers): array
    {
        $config = $autoCheck->config ?? [];

        return [
            0,
            100,
            false,
            [
                'message' => 'Code checks are not yet implemented in this version.',
                'config' => $config,
                'submitted' => $answers,
            ],
        ];
    }
}
