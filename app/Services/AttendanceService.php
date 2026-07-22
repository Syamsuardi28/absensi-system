<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\AttendanceType;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;

class AttendanceService
{
    public function recordScan(User $user, ?Schedule $schedule = null): Attendance
    {
        $now = Carbon::now();

        $existing = Attendance::where('user_id', $user->id)
            ->whereDate('scan_time', $now->toDateString())
            ->where('type', $schedule ? AttendanceType::Session : AttendanceType::Self)
            ->when($schedule, fn ($q) => $q->where('schedule_id', $schedule->id))
            ->first();

        if ($existing) {
            throw new \RuntimeException('Anda sudah melakukan absensi hari ini.');
        }

        $status = AttendanceStatus::Hadir;

        if ($schedule) {
            $start = Carbon::parse($schedule->start_time);
            $gracePeriod = (int) config('attendance.grace_period', 15);
            if ($now->gt($start->addMinutes($gracePeriod))) {
                $status = AttendanceStatus::Terlambat;
            }
        } else {
            $cutoffTime = Carbon::now()->setTimeFromTimeString(
                config('attendance.entry_cutoff', '07:30')
            );
            $gracePeriod = (int) config('attendance.grace_period', 15);
            if ($now->gt($cutoffTime->addMinutes($gracePeriod))) {
                $status = AttendanceStatus::Terlambat;
            }
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'schedule_id' => $schedule?->id,
            'type' => $schedule ? AttendanceType::Session : AttendanceType::Self,
            'status' => $status,
            'scan_time' => $now,
        ]);

        AuditLogService::log('attendance_scan', Attendance::class, $attendance->id, $user);

        return $attendance->load('user');
    }

    public function recordSession(int $scheduleId, array $studentStatuses, User $recordedBy): array
    {
        $results = [];

        foreach ($studentStatuses as $item) {
            $existing = Attendance::where('user_id', $item['user_id'])
                ->where('schedule_id', $scheduleId)
                ->whereDate('scan_time', Carbon::now()->toDateString())
                ->first();

            if ($existing) {
                continue;
            }

            $results[] = Attendance::create([
                'user_id' => $item['user_id'],
                'schedule_id' => $scheduleId,
                'type' => AttendanceType::Session,
                'status' => AttendanceStatus::from($item['status']),
                'scan_time' => now(),
                'notes' => $item['notes'] ?? null,
                'recorded_by' => $recordedBy->id,
            ]);
        }

        return $results;
    }

    public function getHistory(User $user, ?string $month = null, ?string $year = null)
    {
        $query = Attendance::where('user_id', $user->id)
            ->with(['schedule.subject', 'schedule.class']);

        if ($month && $year) {
            $query->whereMonth('scan_time', $month)->whereYear('scan_time', $year);
        }

        return $query->latest('scan_time')->paginate(20);
    }

    public function getReport(array $filters)
    {
        $query = Attendance::query()
            ->with(['user.student.class', 'user.teacher', 'schedule']);

        if (! empty($filters['class_id'])) {
            $query->whereHas('user.student', fn ($q) => $q->where('class_id', $filters['class_id']));
        }

        if (! empty($filters['start_date'])) {
            $query->whereDate('scan_time', '>=', $filters['start_date']);
        }

        if (! empty($filters['end_date'])) {
            $query->whereDate('scan_time', '<=', $filters['end_date']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest('scan_time')->paginate(20);
    }
}
