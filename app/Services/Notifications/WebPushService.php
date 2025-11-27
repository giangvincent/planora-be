<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Models\PushSubscription;
use App\Models\Task;
use App\Models\User;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    private WebPush $webPush;

    public function __construct()
    {
        $this->webPush = new WebPush([
            'VAPID' => [
                'subject' => config('app.url'),
                'publicKey' => config('services.webpush.public_key'),
                'privateKey' => config('services.webpush.private_key'),
            ],
        ]);
    }

    /**
     * Subscribe user to push notifications
     */
    public function subscribe(User $user, array $subscriptionData): PushSubscription
    {
        return $user->pushSubscriptions()->create([
            'endpoint' => $subscriptionData['endpoint'],
            'public_key' => $subscriptionData['keys']['p256dh'] ?? null,
            'auth_token' => $subscriptionData['keys']['auth'] ?? null,
            'content_encoding' => $subscriptionData['contentEncoding'] ?? 'aesgcm',
        ]);
    }

    /**
     * Unsubscribe user from push notifications
     */
    public function unsubscribe(User $user, string $endpoint): void
    {
        $user->pushSubscriptions()
            ->where('endpoint', $endpoint)
            ->delete();
    }

    /**
     * Send daily digest push notification
     */
    public function sendDailyDigest(User $user, array $digest): void
    {
        $payload = [
            'title' => 'Good morning! 🌅',
            'body' => sprintf(
                'You have %d tasks today. Your streak: %d days!',
                count($digest['tasks_today']),
                $digest['current_streak']
            ),
            'icon' => '/icon.png',
            'badge' => '/badge.png',
            'tag' => 'daily-digest',
            'data' => [
                'type' => 'daily_digest',
                'url' => '/dashboard',
            ],
        ];

        $this->sendToUser($user, $payload);
    }

    /**
     * Send task reminder push notification
     */
    public function sendTaskReminder(User $user, Task $task): void
    {
        $payload = [
            'title' => 'Task Reminder ⏰',
            'body' => sprintf('"%s" is due soon!', $task->title),
            'icon' => '/icon.png',
            'badge' => '/badge.png',
            'tag' => 'task-reminder-' . $task->id,
            'data' => [
                'type' => 'task_reminder',
                'task_id' => $task->id,
                'url' => '/tasks/' . $task->id,
            ],
        ];

        $this->sendToUser($user, $payload);
    }

    /**
     * Send push notification to all user's subscriptions
     */
    private function sendToUser(User $user, array $payload): void
    {
        $subscriptions = $user->pushSubscriptions;

        foreach ($subscriptions as $subscription) {
            $webPushSubscription = Subscription::create([
                'endpoint' => $subscription->endpoint,
                'keys' => [
                    'p256dh' => $subscription->public_key,
                    'auth' => $subscription->auth_token,
                ],
                'contentEncoding' => $subscription->content_encoding ?? 'aesgcm',
            ]);

            $this->webPush->queueNotification(
                $webPushSubscription,
                json_encode($payload)
            );
        }

        // Send queued notifications
        foreach ($this->webPush->flush() as $report) {
            if (!$report->isSuccess()) {
                // Handle failed notification (e.g., remove invalid subscription)
                $this->handleFailedNotification($user, $report);
            }
        }
    }

    /**
     * Handle failed notification
     */
    private function handleFailedNotification(User $user, $report): void
    {
        // If subscription is expired or invalid, remove it
        if ($report->isSubscriptionExpired()) {
            $endpoint = $report->getRequest()->getUri()->__toString();
            $this->unsubscribe($user, $endpoint);
        }
    }
}
