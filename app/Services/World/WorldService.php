<?php

declare(strict_types=1);

namespace App\Services\World;

use App\Models\Cosmetic;
use App\Models\User;
use App\Models\World;
use App\Models\WorldObject;
use App\Services\Gamification\WorldReward;

class WorldService
{
    public function __construct(
        private WorldGenerator $generator
    ) {}

    /**
     * Get world for user (with tiles and objects)
     */
    public function getWorldFor(User $user): array
    {
        $world = $user->world()->with(['tiles', 'objects'])->first();

        if (!$world) {
            $world = $this->generator->generateInitialWorld($user);
        }

        $equippedCosmetics = $user->userCosmetics()
            ->where('equipped', true)
            ->with('cosmetic')
            ->get();

        return [
            'world' => $world,
            'tiles' => $world->tiles,
            'objects' => $world->objects,
            'equipped_cosmetics' => $equippedCosmetics,
        ];
    }

    /**
     * Apply reward to user's world
     */
    public function applyReward(User $user, WorldReward $reward): void
    {
        $world = $user->world;

        if (!$world) {
            $world = $this->generator->generateInitialWorld($user);
        }

        // Find or create cosmetic
        $cosmetic = Cosmetic::firstOrCreate(
            ['key' => $reward->key],
            [
                'category' => $reward->type,
                'name' => ucfirst(str_replace('_', ' ', $reward->key)),
                'rarity' => $reward->rarity,
                'meta' => [],
            ]
        );

        // Unlock cosmetic for user
        $user->userCosmetics()->firstOrCreate(
            ['cosmetic_id' => $cosmetic->id],
            ['unlocked_at' => now()]
        );

        // If it's an object, add it to the world
        if ($reward->type === 'object') {
            $this->addObjectToWorld($world, $cosmetic);
        }
    }

    /**
     * Update world layout (move objects, change theme, etc.)
     */
    public function updateLayout(User $user, array $changes): void
    {
        $world = $user->world;

        if (!$world) {
            return;
        }

        // Update theme or weather if provided
        if (isset($changes['theme'])) {
            $world->theme = $changes['theme'];
        }

        if (isset($changes['weather'])) {
            $world->weather = $changes['weather'];
        }

        $world->save();

        // Update object positions
        if (isset($changes['objects'])) {
            foreach ($changes['objects'] as $objectUpdate) {
                WorldObject::where('id', $objectUpdate['id'])
                    ->where('world_id', $world->id)
                    ->update([
                        'x' => $objectUpdate['x'],
                        'y' => $objectUpdate['y'],
                    ]);
            }
        }
    }

    /**
     * Add object to world at a suitable position
     */
    private function addObjectToWorld(World $world, Cosmetic $cosmetic): void
    {
        // Find next available position (simple strategy)
        $existingCount = $world->objects()->count();
        $x = ($existingCount % 10) * 2;
        $y = (int)($existingCount / 10) * 2;

        $world->objects()->create([
            'type' => $cosmetic->category,
            'x' => $x,
            'y' => $y,
            'sprite_key' => $cosmetic->key,
            'state' => [],
        ]);
    }
}
