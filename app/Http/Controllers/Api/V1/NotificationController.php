<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\Notifications\WebPushService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        private WebPushService $webPushService
    ) {}

    /**
     * Get user's notifications
     */
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()
            ->orderBy('scheduled_for', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'notifications' => $notifications,
        ]);
    }

    /**
     * Subscribe to push notifications
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string',
            'keys' => 'required|array',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
            'contentEncoding' => 'nullable|string',
        ]);

        $subscription = $this->webPushService->subscribe(
            $request->user(),
            $request->all()
        );

        return response()->json([
            'subscription' => $subscription,
            'message' => 'Successfully subscribed to push notifications',
        ], 201);
    }

    /**
     * Unsubscribe from push notifications
     */
    public function unsubscribe(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string',
        ]);

        $this->webPushService->unsubscribe(
            $request->user(),
            $request->endpoint
        );

        return response()->json([
            'message' => 'Successfully unsubscribed from push notifications',
        ]);
    }
}
