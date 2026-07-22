<?php

namespace Database\Seeders;

use App\Models\SchoolYear;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['super_admin', 'admin', 'teacher', 'student'];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@sekolah.test'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $superAdmin->assignRole('super_admin');

        $admin = User::firstOrCreate(
            ['email' => 'tu@sekolah.test'],
            [
                'name' => 'Admin TU',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $admin->assignRole('admin');

        $teacher = User::firstOrCreate(
            ['email' => 'guru@sekolah.test'],
            [
                'name' => 'Guru Contoh',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $teacher->assignRole('teacher');

        Teacher::firstOrCreate(
            ['user_id' => $teacher->id],
            ['nip' => '198501012010011001']
        );

        $student = User::firstOrCreate(
            ['email' => 'siswa@sekolah.test'],
            [
                'name' => 'Siswa Contoh',
                'password' => bcrypt('password'),
                'is_active' => true,
            ]
        );
        $student->assignRole('student');

        SchoolYear::firstOrCreate(
            ['name' => 'Tahun Ajaran 2025/2026'],
            [
                'start_date' => '2025-07-14',
                'end_date' => '2026-06-30',
                'is_active' => true,
            ]
        );
    }
}
