<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $email = env('FANOUS_ADMIN_EMAIL');
        $password = env('FANOUS_ADMIN_PASSWORD');

        if (! $email || ! $password) {
            return;
        }

        DB::table('users')->updateOrInsert(
            ['email' => $email],
            [
                'name' => env('FANOUS_ADMIN_NAME', 'Admin'),
                'phone' => env('FANOUS_ADMIN_PHONE', '0000000000'),
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACTIVE,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        //
    }
};
