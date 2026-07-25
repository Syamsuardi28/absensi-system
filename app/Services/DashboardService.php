<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Student;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getSummary(): array
    {
        $today = now()->toDateString();

        $todayStats = Attendance::whereDate('scan_time', $today)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalStudents = Student::count();

        $recentAttendances = Attendance::with(['user.student.class'])
            ->whereDate('scan_time', $today)
            ->latest('scan_time')
            ->limit(10)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'name' => $a->user->name,
                'status' => $a->status->value,
                'status_label' => $a->status->label(),
                'time' => $a->scan_time->format('H:i'),
            ]);

        return [
            'today' => [
                'hadir' => $todayStats['hadir'] ?? 0,
                'terlambat' => $todayStats['terlambat'] ?? 0,
                'izin' => $todayStats['izin'] ?? 0,
                'sakit' => $todayStats['sakit'] ?? 0,
                'alpa' => $todayStats['alpa'] ?? 0,
            ],
            'total_students' => $totalStudents,
            'recent_attendances' => $recentAttendances,
        ];
    }
}
