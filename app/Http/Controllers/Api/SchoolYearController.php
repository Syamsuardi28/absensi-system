<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolYear\StoreSchoolYearRequest;
use App\Http\Requests\SchoolYear\UpdateSchoolYearRequest;
use App\Http\Resources\SchoolYearResource;
use App\Models\SchoolYear;
use Illuminate\Http\JsonResponse;

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

    public function store(StoreSchoolYearRequest $request): JsonResponse
    {
        $schoolYear = SchoolYear::create($request->validated());

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

    public function update(UpdateSchoolYearRequest $request, SchoolYear $schoolYear): JsonResponse
    {
        $schoolYear->update($request->validated());

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
