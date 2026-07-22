<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SchoolClassResource;
use App\Models\SchoolClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $classes = SchoolClass::with(['schoolYear', 'homeroomTeacher.user'])
            ->withCount('students')
            ->when($request->school_year_id, fn ($q) => $q->where('school_year_id', $request->school_year_id))
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => SchoolClassResource::collection($classes)->response()->getData(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'school_year_id' => ['required', 'exists:school_years,id'],
            'homeroom_teacher_id' => ['nullable', 'exists:teachers,id'],
        ]);

        $class = SchoolClass::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Kelas berhasil ditambahkan.',
            'data' => new SchoolClassResource($class->load('schoolYear', 'homeroomTeacher')),
        ], 201);
    }

    public function show(SchoolClass $class): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new SchoolClassResource($class->load(['schoolYear', 'homeroomTeacher.user', 'students.user'])),
        ]);
    }

    public function update(Request $request, SchoolClass $class): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'school_year_id' => ['sometimes', 'exists:school_years,id'],
            'homeroom_teacher_id' => ['nullable', 'exists:teachers,id'],
        ]);

        $class->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Kelas berhasil diperbarui.',
            'data' => new SchoolClassResource($class->fresh()->load(['schoolYear', 'homeroomTeacher'])),
        ]);
    }

    public function destroy(SchoolClass $class): JsonResponse
    {
        $class->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kelas berhasil dihapus.',
        ]);
    }
}
