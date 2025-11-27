<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DailyDigestMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param array<int, array<string, mixed>> $tasks
     */
    public function __construct(
        public readonly User $user,
        public readonly string $date,
        public readonly array $tasks
    ) {
    }

    public function build(): self
    {
        return $this->subject('Planora Daily Digest')
            ->view('emails.daily-digest');
    }
}
