<?php

namespace App\Notifications;

use App\Models\Attendance;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AlpaNotification extends Notification
{
    use Queueable;

    public function __construct(
        private Attendance $attendance,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $studentName = $this->attendance->user->name;
        $date = $this->attendance->scan_time->format('d F Y');

        return (new MailMessage)
            ->subject("Notifikasi Ketidakhadiran - {$studentName}")
            ->line("Diberitahukan bahwa putra/putri Anda, **{$studentName}** tercatat **tidak hadir (Alpa)** pada tanggal **{$date}**.")
            ->line('Mohon segera menghubungi pihak sekolah untuk klarifikasi.')
            ->salutation('Hormat kami, SIAP System');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'attendance_id' => $this->attendance->id,
            'student_name' => $this->attendance->user->name,
            'date' => $this->attendance->scan_time->toDateString(),
            'status' => $this->attendance->status->value,
        ];
    }
}
