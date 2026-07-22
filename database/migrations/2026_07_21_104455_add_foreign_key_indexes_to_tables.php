<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->index('schedule_id');
            $table->index('recorded_by');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('approved_by');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index('user_id');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index('user_id');
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->index('class_id');
            $table->index('subject_id');
            $table->index('teacher_id');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['schedule_id']);
            $table->dropIndex(['recorded_by']);
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['approved_by']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        Schema::table('schedules', function (Blueprint $table) {
            $table->dropIndex(['class_id']);
            $table->dropIndex(['subject_id']);
            $table->dropIndex(['teacher_id']);
        });
    }
};
