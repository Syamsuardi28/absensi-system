<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeacherRequest;
use App\Http\Requests\UpdateTeacherRequest;
use App\Http\Resources\TeacherResource;
use App\Models\Teacher;
use App\Models\User;
use App\Services\QrCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $teachers = Teacher::with(['user', 'subjects'])
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => TeacherResource::collection($teachers)->response()->getData(),
        ]);
    }

    public function store(StoreTeacherRequest $request, QrCodeService $qrService): JsonResponse
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data, $qrService) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'] ?? null,
            ]);

            $user->assignRole('teacher');

            $teacher = Teacher::create([
                'user_id' => $user->id,
                'nip' => $data['nip'],
            ]);

            if (! empty($data['subject_ids'])) {
                $teacher->subjects()->sync($data['subject_ids']);
            }

            $qrService->generate($user);

            return $user;
        });

        $teacher = $user->teacher()->with('subjects')->first();

        return response()->json([
            'success' => true,
            'message' => 'Guru berhasil ditambahkan.',
            'data' => new TeacherResource($teacher->load('user')),
        ], 201);
    }

    public function show(Teacher $teacher): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new TeacherResource($teacher->load(['user', 'subjects'])),
        ]);
    }

    public function update(UpdateTeacherRequest $request, Teacher $teacher): JsonResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $teacher) {
            $userData = array_filter([
                'name' => $data['name'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
            ]);

            if (! empty($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }

            if ($userData) {
                $teacher->user->update($userData);
            }

            $teacherData = array_filter(['nip' => $data['nip'] ?? null]);
            if ($teacherData) {
                $teacher->update($teacherData);
            }

            if (array_key_exists('subject_ids', $data)) {
                $teacher->subjects()->sync($data['subject_ids'] ?? []);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Data guru berhasil diperbarui.',
            'data' => new TeacherResource($teacher->fresh()->load(['user', 'subjects'])),
        ]);
    }

    public function destroy(Teacher $teacher): JsonResponse
    {
        $teacher->user()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Guru berhasil dihapus.',
        ]);
    }
}
