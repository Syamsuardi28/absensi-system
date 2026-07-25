<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Models\Student;
use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $students = Student::with(['user', 'class'])
            ->when($request->class_id, fn ($q) => $q->where('class_id', $request->class_id))
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => StudentResource::collection($students)->response()->getData(),
        ]);
    }

    public function store(StoreStudentRequest $request, QrCodeService $qrService): JsonResponse
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data, $qrService) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'] ?? null,
            ]);

            $user->assignRole('student');

            Student::create([
                'user_id' => $user->id,
                'class_id' => $data['class_id'],
                'nis' => $data['nis'],
                'parent_name' => $data['parent_name'] ?? null,
                'parent_phone' => $data['parent_phone'] ?? null,
                'parent_email' => $data['parent_email'] ?? null,
            ]);

            $qrService->generate($user);

            return $user;
        });

        $student = $user->student()->with('class')->first();

        return response()->json([
            'success' => true,
            'message' => 'Siswa berhasil ditambahkan.',
            'data' => new StudentResource($student->load('user')),
        ], 201);
    }

    public function show(Student $student): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new StudentResource($student->load(['user', 'class'])),
        ]);
    }

    public function update(UpdateStudentRequest $request, Student $student): JsonResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $student) {
            $userData = array_filter([
                'name' => $data['name'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
            ]);

            if (! empty($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }

            if ($userData) {
                $student->user->update($userData);
            }

            $studentData = array_filter([
                'nis' => $data['nis'] ?? null,
                'class_id' => $data['class_id'] ?? null,
                'parent_name' => $data['parent_name'] ?? null,
                'parent_phone' => $data['parent_phone'] ?? null,
                'parent_email' => $data['parent_email'] ?? null,
            ]);

            if ($studentData) {
                $student->update($studentData);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Data siswa berhasil diperbarui.',
            'data' => new StudentResource($student->fresh()->load(['user', 'class'])),
        ]);
    }

    public function destroy(Student $student): JsonResponse
    {
        $student->user()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Siswa berhasil dihapus.',
        ]);
    }
}
