<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SchoolYearResource;
use App\Models\SchoolYear;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SchoolYearController extends Controller
{
    public function index(): JsonResponse
    {
        $schoolYears = SchoolYear::latest()->paginate(20);

        return response()->json([
            'success' => true,
            'data' => SchoolYearResource::collection($schoolYears)->response()->getData(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['boolean'],
        ]);

        $schoolYear = SchoolYear::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Tahun ajaran berhasil ditambahkan.',
            'data' => new SchoolYearResource($schoolYear),
        ], 201);
    }

    public function show(SchoolYear $schoolYear): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new SchoolYearResource($schoolYear),
        ]);
    }

    public function update(Request $request, SchoolYear $schoolYear): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'is_active' => ['boolean'],
        ]);

        $schoolYear->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Tahun ajaran berhasil diperbarui.',
            'data' => new SchoolYearResource($schoolYear),
        ]);
    }

    public function destroy(SchoolYear $schoolYear): JsonResponse
    {
        $schoolYear->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tahun ajaran berhasil dihapus.',
        ]);
    }
}
