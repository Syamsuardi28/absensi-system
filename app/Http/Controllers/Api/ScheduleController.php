<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'teacher_id' => ['required', 'exists:teachers,id'],
            'day' => ['required', 'in:senin,selasa,rabu,kamis,jumat,sabtu,minggu'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ]);

        $schedule = Schedule::create($data);

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

    public function update(Request $request, Schedule $schedule): JsonResponse
    {
        $data = $request->validate([
            'class_id' => ['sometimes', 'exists:school_classes,id'],
            'subject_id' => ['sometimes', 'exists:subjects,id'],
            'teacher_id' => ['sometimes', 'exists:teachers,id'],
            'day' => ['sometimes', 'in:senin,selasa,rabu,kamis,jumat,sabtu,minggu'],
            'start_time' => ['sometimes', 'date_format:H:i'],
            'end_time' => ['sometimes', 'date_format:H:i', function ($attribute, $value, $fail) use ($request, $schedule) {
                $start = $request->input('start_time', $schedule->start_time);
                if ($start && $value <= $start) {
                    $fail('Jam selesai harus setelah jam mulai.');
                }
            }],
        ]);

        $schedule->update($data);

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
