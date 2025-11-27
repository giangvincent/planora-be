<?php

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
        Schema::create('worlds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('level')->default(1);
            $table->string('theme')->default('default');
            $table->string('weather')->default('sunny');
            $table->json('state')->nullable();
            $table->timestamps();
        });

        Schema::create('world_tiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('world_id')->constrained()->cascadeOnDelete();
            $table->integer('x');
            $table->integer('y');
            $table->string('terrain');
            $table->json('data')->nullable();
            $table->index('world_id');
        });

        Schema::create('world_objects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('world_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->integer('x');
            $table->integer('y');
            $table->string('sprite_key');
            $table->json('state')->nullable();
            $table->timestamps();
            $table->index('world_id');
        });

        Schema::create('cosmetics', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('category'); // object, theme, weather, avatar
            $table->string('name');
            $table->string('rarity')->default('common');
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('user_cosmetics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cosmetic_id')->constrained()->cascadeOnDelete();
            $table->dateTime('unlocked_at');
            $table->boolean('equipped')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_cosmetics');
        Schema::dropIfExists('cosmetics');
        Schema::dropIfExists('world_objects');
        Schema::dropIfExists('world_tiles');
        Schema::dropIfExists('worlds');
    }
};
