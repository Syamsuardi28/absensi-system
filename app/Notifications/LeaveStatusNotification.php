<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        private LeaveRequest $leaveRequest,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type = $this->leaveRequest->type->label();
        $status = $this->leaveRequest->status->label();
        $start = $this->leaveRequest->start_date->format('d F Y');
        $end = $this->leaveRequest->end_date->format('d F Y');

        $message = (new MailMessage)
            ->subject("Status Pengajuan {$type}");

        if ($this->leaveRequest->status->value === 'approved') {
            $message->line("Pengajuan {$type} Anda pada {$start} s/d {$end} telah **disetujui**.");
        } elseif ($this->leaveRequest->status->value === 'rejected') {
            $message->line("Pengajuan {$type} Anda pada {$start} s/d {$end} **ditolak**.");
            if ($this->leaveRequest->rejection_note) {
                $message->line("Alasan: {$this->leaveRequest->rejection_note}");
            }
        }

        return $message->salutation('Hormat kami, SIAP System');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'leave_request_id' => $this->leaveRequest->id,
            'type' => $this->leaveRequest->type->value,
            'status' => $this->leaveRequest->status->value,
            'start_date' => $this->leaveRequest->start_date->toDateString(),
            'end_date' => $this->leaveRequest->end_date->toDateString(),
        ];
    }
}
