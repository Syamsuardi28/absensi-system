<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\AttendanceType;
use App\Enums\LeaveStatus;
use App\Jobs\SendNotificationJob;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
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

    private function syncAttendanceOnDecision(LeaveRequest $leaveRequest, string $decision): void
    {
        $status = $decision === 'approved'
            ? ($leaveRequest->type->value === 'sakit' ? AttendanceStatus::Sakit : AttendanceStatus::Izin)
            : null;

        $startDate = Carbon::parse($leaveRequest->start_date);
        $endDate = Carbon::parse($leaveRequest->end_date);

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            if ($decision === 'approved') {
                Attendance::updateOrCreate(
                    [
                        'user_id' => $leaveRequest->user_id,
                        'type' => AttendanceType::SelfIn,
                        'schedule_id' => null,
                    ],
                    [
                        'status' => $status,
                        'scan_time' => $date->toDateString().' 00:00:00',
                        'notes' => 'Otomatis dari pengajuan '.$leaveRequest->type->label(),
                    ]
                );
            }
        }
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

        $this->syncAttendanceOnDecision($leaveRequest, 'approved');

        AuditLogService::log('leave_request_approve', LeaveRequest::class, $leaveRequest->id, $approver);

        SendNotificationJob::dispatch($leaveRequest->user, 'email', 'leave_decision', [
            'leave_request_id' => $leaveRequest->id,
            'type' => $leaveRequest->type->value,
            'status' => 'approved',
            'start_date' => $leaveRequest->start_date->toDateString(),
            'end_date' => $leaveRequest->end_date->toDateString(),
        ]);

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

        SendNotificationJob::dispatch($leaveRequest->user, 'email', 'leave_decision', [
            'leave_request_id' => $leaveRequest->id,
            'type' => $leaveRequest->type->value,
            'status' => 'rejected',
            'start_date' => $leaveRequest->start_date->toDateString(),
            'end_date' => $leaveRequest->end_date->toDateString(),
            'rejection_note' => $note,
        ]);

        return $leaveRequest;
    }
}
