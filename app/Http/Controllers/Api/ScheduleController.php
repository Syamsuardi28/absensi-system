<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Schedule\StoreScheduleRequest;
use App\Http\Requests\Schedule\UpdateScheduleRequest;
use App\Http\Resources\ScheduleResource;
use App\Models\Schedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $schedules = Schedule::with(['class', 'subject', 'teacher.user'])
            ->when($request->class_id, fn ($q) => $q->where('class_id', $request->class_id))
            ->when($request->teacher_id, fn ($q) => $q->where('teacher_id', $request->teacher_id))
            ->when($request->day, fn ($q) => $q->where('day', $request->day))
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => ScheduleResource::collection($schedules)->response()->getData(),
        ]);
    }

    public function store(StoreScheduleRequest $request): JsonResponse
    {
        $schedule = Schedule::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil ditambahkan.',
            'data' => new ScheduleResource($schedule->load(['class', 'subject', 'teacher.user'])),
        ], 201);
    }

    public function show(Schedule $schedule): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new ScheduleResource($schedule->load(['class', 'subject', 'teacher.user'])),
        ]);
    }

    public function update(UpdateScheduleRequest $request, Schedule $schedule): JsonResponse
    {
        $schedule->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil diperbarui.',
            'data' => new ScheduleResource($schedule->fresh()->load(['class', 'subject', 'teacher.user'])),
        ]);
    }

    public function destroy(Schedule $schedule): JsonResponse
    {
        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil dihapus.',
        ]);
    }
}
