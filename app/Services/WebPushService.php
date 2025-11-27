<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Arr;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use RuntimeException;

class WebPushService
{
    public function send(User $user, array $payload): void
    {
        $subscription = $user->routeNotificationForWebPush();

        if (! is_array($subscription)) {
            return;
        }

        $webPush = $this->client();
        $jsonPayload = json_encode($payload, JSON_THROW_ON_ERROR);

        $webPush->queueNotification(
            Subscription::create($subscription),
            $jsonPayload
        );

        foreach ($webPush->flush() as $report) {
            if (! $report->isSuccess()) {
                throw new RuntimeException($report->getReason());
            }
        }
    }

    private function client(): WebPush
    {
        $config = config('services.webpush.vapid');

        return new WebPush([
            'VAPID' => [
                'subject' => Arr::get($config, 'subject'),
                'publicKey' => Arr::get($config, 'public_key'),
                'privateKey' => Arr::get($config, 'private_key'),
            ],
        ]);
    }
}
