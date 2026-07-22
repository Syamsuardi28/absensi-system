<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeaveRequest;
use App\Http\Resources\LeaveRequestResource;
use App\Models\LeaveRequest;
use App\Services\LeaveRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveRequestController extends Controller
{
    public function __construct(private LeaveRequestService $leaveService) {}

    public function store(StoreLeaveRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('attachments', 'public');
        }

        $leaveRequest = $this->leaveService->submit($data, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan izin berhasil dikirim.',
            'data' => new LeaveRequestResource($leaveRequest->load('user')),
        ], 201);
    }

    public function myRequests(Request $request): JsonResponse
    {
        $requests = $this->leaveService->getUserRequests($request->user());

        return response()->json([
            'success' => true,
            'data' => LeaveRequestResource::collection($requests)->response()->getData(),
        ]);
    }

    public function pending(): JsonResponse
    {
        $requests = $this->leaveService->getPending();

        return response()->json([
            'success' => true,
            'data' => LeaveRequestResource::collection($requests)->response()->getData(),
        ]);
    }

    public function approve(int $id, Request $request): JsonResponse
    {
        $leave = LeaveRequest::findOrFail($id);
        $this->leaveService->approve($leave, $request->user(), $request->input('note'));

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan izin disetujui.',
            'data' => new LeaveRequestResource($leave->fresh()->load(['user', 'approvedBy'])),
        ]);
    }

    public function reject(int $id, Request $request): JsonResponse
    {
        $request->validate(['rejection_note' => ['required', 'string']]);

        $leave = LeaveRequest::findOrFail($id);
        $this->leaveService->reject($leave, $request->user(), $request->input('rejection_note'));

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan izin ditolak.',
            'data' => new LeaveRequestResource($leave->fresh()->load(['user', 'approvedBy'])),
        ]);
    }
}
