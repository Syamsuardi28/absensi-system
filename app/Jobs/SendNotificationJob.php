<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private User $user,
        private string $channel,
        private string $type,
        private array $payload,
    ) {}

    public function handle(): void
    {
        $notification = Notification::create([
            'user_id' => $this->user->id,
            'type' => $this->type,
            'channel' => $this->channel,
            'payload' => $this->payload,
            'status' => 'pending',
        ]);

        if ($this->channel === 'email') {
            $this->sendEmail($notification);
        }
    }

    private function sendEmail(Notification $notification): void
    {
        try {
            // TODO: Integrate with mail service when mail config is set up
            // Mail::to($this->user->email)->send(...);

            $notification->markAsSent();
        } catch (\Throwable $e) {
            $notification->markAsFailed();
            $this->fail($e);
        }
    }
}
