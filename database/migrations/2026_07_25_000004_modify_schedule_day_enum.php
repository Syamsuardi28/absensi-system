<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE schedules MODIFY COLUMN day enum('senin','selasa','rabu','kamis','jumat','sabtu') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE schedules MODIFY COLUMN day enum('senin','selasa','rabu','kamis','jumat','sabtu','minggu') NOT NULL");
        }
    }
};
