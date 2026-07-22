<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScanAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Services\AttendanceService;
use App\Services\QrCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        private AttendanceService $attendanceService,
        private QrCodeService $qrService,
    ) {}

    public function scan(ScanAttendanceRequest $request): JsonResponse
    {
        try {
            $user = $this->qrService->validate($request->qr_token);

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code tidak valid atau pengguna tidak aktif.',
                ], 422);
            }

            $attendance = $this->attendanceService->recordScan($user);

            return response()->json([
                'success' => true,
                'message' => 'Absensi berhasil dicatat.',
                'data' => new AttendanceResource($attendance),
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function history(Request $request): JsonResponse
    {
        $data = $this->attendanceService->getHistory(
            $request->user(),
            $request->input('month'),
            $request->input('year'),
        );

        return response()->json([
            'success' => true,
            'data' => AttendanceResource::collection($data)->response()->getData(),
        ]);
    }

    public function session(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'schedule_id' => ['required', 'exists:schedules,id'],
            'date' => ['required', 'date'],
            'attendances' => ['required', 'array'],
            'attendances.*.user_id' => ['required', 'exists:users,id'],
            'attendances.*.status' => ['required', 'string'],
            'attendances.*.notes' => ['nullable', 'string'],
        ]);

        $results = $this->attendanceService->recordSession(
            $validated['schedule_id'],
            $validated['attendances'],
            $request->user(),
        );

        return response()->json([
            'success' => true,
            'message' => 'Absensi sesi berhasil disimpan.',
            'data' => AttendanceResource::collection(collect($results)),
        ]);
    }

    public function report(Request $request): JsonResponse
    {
        $data = $this->attendanceService->getReport($request->only([
            'class_id', 'start_date', 'end_date', 'status',
        ]));

        return response()->json([
            'success' => true,
            'data' => AttendanceResource::collection($data)->response()->getData(),
        ]);
    }
}
