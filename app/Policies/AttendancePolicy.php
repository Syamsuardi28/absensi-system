<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Attendance $attendance): bool
    {
        if ($user->hasAnyRole(['super_admin', 'admin'])) {
            return true;
        }

        return $attendance->user_id === $user->id;
    }

    public function scan(User $user): bool
    {
        return true;
    }

    public function recordSession(User $user): bool
    {
        return $user->hasRole('teacher');
    }

    public function report(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin', 'teacher']);
    }
}
