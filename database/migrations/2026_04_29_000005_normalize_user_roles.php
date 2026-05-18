<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'student')
            ->update(['role' => 'guard']);

        $hasAdmin = DB::table('users')->where('role', 'admin')->exists();

        if (! $hasAdmin) {
            $firstUser = DB::table('users')->orderBy('id')->first();

            if ($firstUser) {
                DB::table('users')
                    ->where('id', $firstUser->id)
                    ->update([
                        'role' => 'admin',
                        'status' => 'active',
                    ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
