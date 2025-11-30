<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MeController extends Controller
{
    /**
     * Get current user profile
     */
    public function show(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load(['gamificationProfile', 'world']),
        ]);
    }

    /**
     * Update current user profile
     */
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $request->user()->id,
            'timezone' => 'sometimes|string',
            'notification_channel' => 'sometimes|in:push,email,both,none',
            'settings' => 'sometimes|array',
        ]);

        $request->user()->update($request->only([
            'name',
            'email',
            'timezone',
            'notification_channel',
            'settings',
        ]));

        return response()->json([
            'user' => $request->user()->fresh(),
        ]);
    }

    /**
     * Update password
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $request->user()->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password does not match your current password.'],
            ]);
        }

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Password updated successfully',
        ]);
    }
}
