<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\AttendanceType;
use App\Jobs\SendNotificationJob;
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
            ->where('type', $schedule ? AttendanceType::Session : AttendanceType::SelfIn)
            ->when($schedule, fn ($q) => $q->where('schedule_id', $schedule->id))
            ->first();

        $existingSelfOut = Attendance::where('user_id', $user->id)
            ->whereDate('scan_time', $now->toDateString())
            ->where('type', AttendanceType::SelfOut)
            ->exists();

        if ($existing) {
            throw new \RuntimeException('Anda sudah melakukan absensi hari ini.');
        }

        $status = AttendanceStatus::Hadir;

        if (! $schedule) {
            if ($existingSelfOut) {
                throw new \RuntimeException('Anda sudah melakukan absensi pulang hari ini.');
            }

            $hasSelfIn = Attendance::where('user_id', $user->id)
                ->whereDate('scan_time', $now->toDateString())
                ->where('type', AttendanceType::SelfIn)
                ->exists();

            if ($hasSelfIn) {
                $attendance = Attendance::create([
                    'user_id' => $user->id,
                    'type' => AttendanceType::SelfOut,
                    'status' => AttendanceStatus::Hadir,
                    'scan_time' => $now,
                ]);

                AuditLogService::log('attendance_scan_out', Attendance::class, $attendance->id, $user);

                return $attendance->load('user');
            }

            $cutoffTime = Carbon::now()->setTimeFromTimeString(
                config('attendance.entry_cutoff', '07:30')
            );
            $gracePeriod = (int) config('attendance.grace_period', 15);
            if ($now->gt($cutoffTime->addMinutes($gracePeriod))) {
                $status = AttendanceStatus::Terlambat;
            }

            $type = AttendanceType::SelfIn;
        } else {
            $start = Carbon::parse($schedule->start_time);
            $gracePeriod = (int) config('attendance.grace_period', 15);
            if ($now->gt($start->addMinutes($gracePeriod))) {
                $status = AttendanceStatus::Terlambat;
            }

            $type = AttendanceType::Session;
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'schedule_id' => $schedule?->id,
            'type' => $type,
            'status' => $status,
            'scan_time' => $now,
        ]);

        AuditLogService::log('attendance_scan', Attendance::class, $attendance->id, $user);

        if ($status === AttendanceStatus::Alpa && $user->student?->parent_email) {
            SendNotificationJob::dispatch($user, 'email', 'alpa_alert', [
                'attendance_id' => $attendance->id,
                'student_name' => $user->name,
                'date' => $now->toDateString(),
            ]);
        }

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

            $status = AttendanceStatus::from($item['status']);

            $results[] = Attendance::create([
                'user_id' => $item['user_id'],
                'schedule_id' => $scheduleId,
                'type' => AttendanceType::Session,
                'status' => $status,
                'scan_time' => now(),
                'notes' => $item['notes'] ?? null,
                'recorded_by' => $recordedBy->id,
            ]);

            if ($status === AttendanceStatus::Alpa) {
                $student = User::find($item['user_id']);
                if ($student?->student?->parent_email) {
                    SendNotificationJob::dispatch($student, 'email', 'alpa_alert', [
                        'student_name' => $student->name,
                        'date' => now()->toDateString(),
                    ]);
                }
            }
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
