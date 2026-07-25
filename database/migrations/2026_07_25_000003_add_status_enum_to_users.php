<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $nullTokens = DB::table('users')->whereNull('qr_token')->pluck('id');
        foreach ($nullTokens as $id) {
            DB::table('users')->where('id', $id)->update([
                'qr_token' => (string) Str::uuid(),
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'status')) {
                $table->string('status', 20)->default('active')->after('qr_token');
            }
        });

        DB::table('users')->where('is_active', 0)->update(['status' => 'inactive']);

        Schema::table('users', function (Blueprint $table) {
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn('status');
        });
    }
};
