<?php

namespace App\Services;

use App\Enums\LeaveStatus;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class LeaveRequestService
{
    public function submit(array $data, User $user): LeaveRequest
    {
        $leaveRequest = LeaveRequest::create([
            'user_id' => $user->id,
            'type' => $data['type'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'reason' => $data['reason'],
            'attachment_path' => $data['attachment_path'] ?? null,
            'status' => LeaveStatus::Pending,
        ]);

        AuditLogService::log('leave_request_submit', LeaveRequest::class, $leaveRequest->id, $user);

        return $leaveRequest;
    }

    public function getUserRequests(User $user): LengthAwarePaginator
    {
        return LeaveRequest::where('user_id', $user->id)
            ->with(['approvedBy'])
            ->latest()
            ->paginate(20);
    }

    public function getPending(): LengthAwarePaginator
    {
        return LeaveRequest::where('status', LeaveStatus::Pending->value)
            ->with('user')
            ->latest()
            ->paginate(20);
    }

    public function approve(LeaveRequest $leaveRequest, User $approver, ?string $note = null): LeaveRequest
    {
        if ($leaveRequest->status !== LeaveStatus::Pending) {
            throw new \RuntimeException('Pengajuan izin sudah diproses sebelumnya.');
        }

        $leaveRequest->update([
            'status' => LeaveStatus::Approved,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'rejection_note' => null,
        ]);

        AuditLogService::log('leave_request_approve', LeaveRequest::class, $leaveRequest->id, $approver);

        return $leaveRequest;
    }

    public function reject(LeaveRequest $leaveRequest, User $approver, string $note): LeaveRequest
    {
        if ($leaveRequest->status !== LeaveStatus::Pending) {
            throw new \RuntimeException('Pengajuan izin sudah diproses sebelumnya.');
        }

        $leaveRequest->update([
            'status' => LeaveStatus::Rejected,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'rejection_note' => $note,
        ]);

        AuditLogService::log('leave_request_reject', LeaveRequest::class, $leaveRequest->id, $approver);

        return $leaveRequest;
    }
}
