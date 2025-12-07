<?php

use App\Enums\AutoCheckType;
use App\Enums\DifficultyLevel;
use App\Enums\LearningTaskStatus;
use App\Enums\LearningTaskType;
use App\Enums\RoleSourceType;
use App\Enums\RoleStatus;
use App\Enums\RoleVisibility;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->enum('source_type', RoleSourceType::values())->default(RoleSourceType::Manual->value);
            $table->json('source_meta')->nullable();
            $table->enum('visibility', RoleVisibility::values())->default(RoleVisibility::Private->value);
            $table->enum('status', RoleStatus::values())->default(RoleStatus::Draft->value);
            $table->unsignedSmallInteger('estimated_duration_weeks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'slug']);
            $table->index(['user_id', 'status']);
            $table->index('visibility');
        });

        Schema::create('role_phases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->unsignedSmallInteger('estimated_duration_weeks')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['role_id', 'order']);
        });

        Schema::create('phase_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('phase_id')->constrained('role_phases')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->enum('difficulty_level', DifficultyLevel::values())->default(DifficultyLevel::Intro->value);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['phase_id', 'order']);
        });

        Schema::create('learning_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('step_id')->constrained('phase_steps')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', LearningTaskType::values())->default(LearningTaskType::Study->value);
            $table->enum('status', LearningTaskStatus::values())->default(LearningTaskStatus::Pending->value);
            $table->unsignedInteger('order')->default(0);
            $table->unsignedSmallInteger('estimated_minutes')->nullable();
            $table->date('due_date')->nullable();
            $table->foreignId('linked_task_id')->nullable()->constrained('tasks')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['step_id', 'order']);
            $table->index('linked_task_id');
            $table->index('status');
        });

        Schema::create('auto_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_task_id')->constrained('learning_tasks')->cascadeOnDelete();
            $table->enum('type', AutoCheckType::values());
            $table->json('config');
            $table->timestamps();
        });

        Schema::create('auto_check_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auto_check_id')->constrained('auto_checks')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('score')->default(0);
            $table->unsignedSmallInteger('max_score')->default(100);
            $table->boolean('passed')->default(false);
            $table->json('attempt_data')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'auto_check_id']);
        });

        Schema::create('role_progress_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('completed_tasks_count')->default(0);
            $table->unsignedInteger('total_tasks_count')->default(0);
            $table->unsignedInteger('completed_steps_count')->default(0);
            $table->unsignedInteger('completed_phases_count')->default(0);
            $table->date('snapshot_date');
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'role_id', 'snapshot_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_progress_snapshots');
        Schema::dropIfExists('auto_check_results');
        Schema::dropIfExists('auto_checks');
        Schema::dropIfExists('learning_tasks');
        Schema::dropIfExists('phase_steps');
        Schema::dropIfExists('role_phases');
        Schema::dropIfExists('roles');
    }
};
