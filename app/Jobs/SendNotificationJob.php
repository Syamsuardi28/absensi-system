<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Models\User;
use App\Notifications\AlpaNotification;
use App\Notifications\LeaveStatusNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

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
            if ($this->type === 'alpa_alert' && ! empty($this->payload['attendance_id'])) {
                $attendance = \App\Models\Attendance::find($this->payload['attendance_id']);
                if ($attendance) {
                    Mail::to($this->user->email)->queue(new AlpaNotification($attendance));
                }
            } elseif ($this->type === 'leave_decision' && ! empty($this->payload['leave_request_id'])) {
                $leaveRequest = \App\Models\LeaveRequest::find($this->payload['leave_request_id']);
                if ($leaveRequest) {
                    Mail::to($this->user->email)->queue(new LeaveStatusNotification($leaveRequest));
                }
            }

            $notification->markAsSent();
        } catch (\Throwable $e) {
            $notification->markAsFailed();
            $this->fail($e);
        }
    }
}
