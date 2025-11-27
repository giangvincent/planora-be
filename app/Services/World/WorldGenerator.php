<?php

declare(strict_types=1);

namespace App\Services\World;

use App\Models\User;
use App\Models\World;

class WorldGenerator
{
    /**
     * Generate initial world for a new user
     */
    public function generateInitialWorld(User $user): World
    {
        // Create the world
        $world = $user->world()->create([
            'level' => 1,
            'theme' => 'default',
            'weather' => 'sunny',
            'state' => [],
        ]);

        // Generate basic tile layout (10x10 grid)
        $this->generateBasicTiles($world);

        // Add starter objects
        $this->addStarterObjects($world);

        return $world;
    }

    /**
     * Generate basic tile layout
     */
    private function generateBasicTiles(World $world): void
    {
        $tiles = [];

        for ($x = 0; $x < 10; $x++) {
            for ($y = 0; $y < 10; $y++) {
                $tiles[] = [
                    'world_id' => $world->id,
                    'x' => $x,
                    'y' => $y,
                    'terrain' => $this->getTerrainType($x, $y),
                    'data' => null,
                ];
            }
        }

        $world->tiles()->createMany($tiles);
    }

    /**
     * Determine terrain type based on position
     */
    private function getTerrainType(int $x, int $y): string
    {
        // Make a simple pattern: grass mostly, water at edges
        if ($x === 0 || $x === 9 || $y === 0 || $y === 9) {
            return 'water';
        }

        return 'grass';
    }

    /**
     * Add starter objects to the world
     */
    private function addStarterObjects(World $world): void
    {
        $world->objects()->create([
            'type' => 'tree',
            'x' => 5,
            'y' => 5,
            'sprite_key' => 'tree_basic',
            'state' => [],
        ]);
    }
}
