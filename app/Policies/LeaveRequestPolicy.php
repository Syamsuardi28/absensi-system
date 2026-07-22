<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($user->hasAnyRole(['super_admin', 'admin', 'teacher'])) {
            return true;
        }

        return $leaveRequest->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function approve(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($leaveRequest->status->value !== 'pending') {
            return false;
        }

        return $user->hasAnyRole(['super_admin', 'admin', 'teacher']);
    }

    public function reject(User $user, LeaveRequest $leaveRequest): bool
    {
        if ($leaveRequest->status->value !== 'pending') {
            return false;
        }

        return $user->hasAnyRole(['super_admin', 'admin', 'teacher']);
    }

    public function viewPending(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'admin', 'teacher']);
    }
}
