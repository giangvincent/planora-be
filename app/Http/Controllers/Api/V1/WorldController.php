<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cosmetic;
use App\Services\World\WorldService;
use Illuminate\Http\Request;

class WorldController extends Controller
{
    public function __construct(
        private WorldService $worldService
    ) {}

    /**
     * Get user's world
     */
    public function index(Request $request)
    {
        $world = $this->worldService->getWorldFor($request->user());

        return response()->json($world);
    }

    /**
     * Update world layout
     */
    public function update(Request $request)
    {
        $request->validate([
            'theme' => 'sometimes|string',
            'weather' => 'sometimes|string',
            'objects' => 'sometimes|array',
            'objects.*.id' => 'required|integer',
            'objects.*.x' => 'required|integer',
            'objects.*.y' => 'required|integer',
        ]);

        $this->worldService->updateLayout($request->user(), $request->all());

        return response()->json([
            'message' => 'World updated successfully',
            'world' => $this->worldService->getWorldFor($request->user()),
        ]);
    }

    /**
     * Get all cosmetics
     */
    public function cosmetics(Request $request)
    {
        $allCosmetics = Cosmetic::all();
        $userCosmetics = $request->user()->userCosmetics()
            ->with('cosmetic')
            ->get();

        return response()->json([
            'all_cosmetics' => $allCosmetics,
            'user_cosmetics' => $userCosmetics,
        ]);
    }

    /**
     * Equip a cosmetic
     */
    public function equipCosmetic(Request $request, Cosmetic $cosmetic)
    {
        // Check if user has this cosmetic
        $userCosmetic = $request->user()->userCosmetics()
            ->where('cosmetic_id', $cosmetic->id)
            ->firstOrFail();

        // Unequip other cosmetics of the same category
        $request->user()->userCosmetics()
            ->whereHas('cosmetic', function ($query) use ($cosmetic) {
                $query->where('category', $cosmetic->category);
            })
            ->update(['equipped' => false]);

        // Equip this cosmetic
        $userCosmetic->update(['equipped' => true]);

        return response()->json([
            'message' => 'Cosmetic equipped successfully',
            'cosmetic' => $userCosmetic->load('cosmetic'),
        ]);
    }
}
