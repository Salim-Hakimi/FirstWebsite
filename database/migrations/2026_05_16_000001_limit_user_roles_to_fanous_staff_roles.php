<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereIn('role', ['owner', 'manager'])
            ->update(['role' => 'admin']);

        DB::table('users')
            ->whereIn('role', ['cook', 'dorm_student', 'library_member', 'applicant'])
            ->update(['role' => 'guard']);
    }

    public function down(): void
    {
        //
    }
};
