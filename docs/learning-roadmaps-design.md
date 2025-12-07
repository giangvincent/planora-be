# Learning Roadmaps Add-on (Roles → Phases → Steps → Learning Tasks)

Extension to the existing Laravel + Filament backend to support structured learning roadmaps with optional AI import, auto-checks, and gamification hooks.

## Database Shape

- `roles` (or `learning_roles` if you prefer to avoid name clash)
  - `id`, `user_id` (FK users), `title`, `slug` (unique per user), `description` text nullable
  - `source_type` enum: `manual`, `ai`; `source_meta` json nullable (prompt/model/raw text)
  - `visibility` enum: `private`, `unlisted`, `public`; `status` enum: `draft`, `active`, `archived`
  - `estimated_duration_weeks` int nullable
  - timestamps, optional soft deletes
  - Index: (`user_id`, `slug`) unique; (`user_id`, `status`)

- `role_phases`
  - `id`, `role_id` FK roles cascade, `title`, `description` text nullable
  - `order` int, `estimated_duration_weeks` int nullable
  - timestamps; index on (`role_id`, `order`)

- `phase_steps`
  - `id`, `phase_id` FK role_phases cascade, `title`, `description` text nullable
  - `order` int, `difficulty_level` enum: `intro`, `intermediate`, `advanced`
  - timestamps; index on (`phase_id`, `order`)

- `learning_tasks`
  - `id`, `step_id` FK phase_steps cascade, `title`, `description` text nullable
  - `type` enum: `study`, `practice`, `project`, `quiz`
  - `status` enum: `pending`, `completed`, `skipped`
  - `order` int, `estimated_minutes` int nullable, `due_date` date nullable
  - `linked_task_id` FK tasks nullable (lets you mirror into planner)
  - timestamps; index on (`step_id`, `order`), (`linked_task_id`)

- `auto_checks`
  - `id`, `learning_task_id` FK learning_tasks cascade
  - `type` enum: `quiz`, `text_keywords`, `code`, `rating`
  - `config` json (quiz questions, keyword list, code tests, etc.)
  - timestamps

- `auto_check_results`
  - `id`, `auto_check_id` FK auto_checks cascade, `user_id`
  - `score` int 0–100, `max_score` int, `passed` bool
  - `attempt_data` json (submitted answers, AI feedback)
  - `created_at` (no update)
  - Index on (`user_id`, `auto_check_id`)

- `role_progress_snapshots` (optional analytics)
  - `id`, `role_id`, `user_id`
  - `completed_tasks_count`, `total_tasks_count`, `completed_steps_count`, `completed_phases_count`
  - `snapshot_date` date, `created_at`

## Eloquent Models & Relationships

- `Role` → belongsTo `User`; hasMany `RolePhase`, `RoleProgressSnapshot`.
  - Cast enums for `source_type`, `visibility`, `status`.
  - Slug is unique per user; generate via observer.
- `RolePhase` → belongsTo `Role`; hasMany `PhaseStep`.
- `PhaseStep` → belongsTo `RolePhase`; hasMany `LearningTask`.
- `LearningTask` → belongsTo `PhaseStep`; optional belongsTo `Task` via `linked_task_id`; hasMany `AutoCheck`.
- `AutoCheck` → belongsTo `LearningTask`; hasMany `AutoCheckResult`.
- `AutoCheckResult` → belongsTo `AutoCheck`, belongsTo `User`.
- `RoleProgressSnapshot` → belongsTo `Role`, `User`.
- Consider `SoftDeletes` on user-owned records (roles/phases/steps/learning_tasks) to match existing patterns on tasks/goals.

Enums to add under `App\Enums` to keep validation consistent: `RoleSourceType`, `RoleVisibility`, `RoleStatus`, `DifficultyLevel`, `LearningTaskType`, `LearningTaskStatus`, `AutoCheckType`.

## Service Layer

- `RoadmapService` (app/Services/RoadmapService.php)
  - `createRole(User $user, RoleData $data)`
  - `addPhase(Role $role, PhaseData $data)` (assigns `order`)
  - `addStep(RolePhase $phase, StepData $data)` (assigns `order`)
  - `addLearningTask(PhaseStep $step, TaskData $data)` (assigns `order`, optionally mirror to `Task`)
  - `markTaskCompleted(LearningTask $task, ?int $actualMinutes = null)` (updates status, syncs linked `Task`, fires gamification/world hooks, calls `ProgressService`)
  - Enforce ownership scoping; handle cascading deletes/renumbering of `order`.

- `RoadmapParserService`
  - Input: free text/JSON from AI; Output: DTO with title/description/phases/steps/tasks.
  - Use heuristics or a secondary AI call to normalize; validate counts before persisting.

- `AutoCheckService`
  - `runCheck(AutoCheck $check, User $user, array $answers): AutoCheckResult`
  - Implement quiz (exact index matching), text_keywords (lowercase keyword count), code (delegate to sandbox/AI later), rating (numeric threshold).
  - Persist `AutoCheckResult`, mark passed flag, award bonus XP/coin via `GamificationService` when passed.

- `ProgressService`
  - `updateForRole(Role $role, User $user)` recalculates completion % from tasks/steps/phases.
  - `snapshot(Role $role, User $user)` writes `role_progress_snapshots` for charts.
  - `dashboardStats(User $user)` aggregates per-role progress.

## API Surface (all under `/api/v1`, sanctum-protected)

- Roles: `GET /roles` (list current user), `POST /roles`, `GET /roles/{role}`, `PATCH /roles/{role}`, `DELETE /roles/{role}`.
- Phases: `POST /roles/{role}/phases`, `PATCH /phases/{phase}`, `DELETE /phases/{phase}`.
- Steps: `POST /phases/{phase}/steps`, `PATCH /steps/{step}`, `DELETE /steps/{step}`.
- Learning tasks: `POST /steps/{step}/tasks`, `PATCH /learning-tasks/{id}`, `DELETE /learning-tasks/{id}`, `POST /learning-tasks/{id}/complete` (updates status, triggers gamification/world/progress), optional `POST /learning-tasks/{id}/sync-task` to create/link planner task.
- Auto-check: `GET /learning-tasks/{id}/auto-check` (config), `POST /learning-tasks/{id}/auto-check/run` (answers → result payload).
- AI import: `POST /roles/import-from-ai` with `{prompt, roleTitle?, context?}` → uses `RoadmapParserService`, returns created counts and IDs.
- Response resources should include nested role→phases→steps→learning_tasks to reduce round-trips. Scope authorization to owner unless visibility is public/unlisted.

OpenAPI additions: new `Roles` tag; schemas for Role/Phase/Step/LearningTask/AutoCheck (+ enums); request bodies for create/update/import/auto-check run.

## Filament Admin

- Resources: RoleResource, RolePhaseResource, PhaseStepResource, LearningTaskResource, AutoCheckResource, AutoCheckResultResource.
- Capabilities: CRUD templates for preset/public roles, inspect auto-check configs/results, view per-user progress snapshots.
- Add relation managers for phases/steps/tasks to allow inline ordering edits.

## Integration Touchpoints

- When `LearningTask` reaches `completed`:
  - Sync `Task` status/actual minutes if `linked_task_id` exists.
  - Call `GamificationService::onTaskCompleted` (or a new `onLearningTaskCompleted` helper) to award XP/coins; optionally pass a flag to differentiate learning tasks.
  - Trigger `WorldService` reward hook similarly to existing task/goal completions.
  - Call `ProgressService` to update completion percentages and snapshots.
- Notifications: optional push when auto-check passed or phase completed.
- Search: optionally index Role titles/descriptions with Scout for discovery of public templates.

## Rollout Plan (phased)

1) **Structural MVP**: migrations, models/relations/enums, CRUD API without AI/auto-check, optional task sync toggle.\
2) **AI Import**: add `source_type/meta`, build `RoadmapParserService`, `POST /roles/import-from-ai` happy-path prompt.\
3) **Auto-check Engine**: tables + `AutoCheckService` for quiz/text_keywords, API run endpoint, gamification bonus.\
4) **Analytics + Admin polish**: snapshots, Filament dashboards, template library, code-check support.
