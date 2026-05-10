<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->updateOrInsert(
            ['email' => 'admin123@gmail.com'],
            [
                'name' => 'Admin',
                'phone' => '0000000000',
                'role' => User::ROLE_OWNER,
                'status' => User::STATUS_ACTIVE,
                'password' => '$2y$10$ODUZZnwxjm7xQDDsWcL9UevuVrwl/YqqFvj.j3Ee2R4RfnCTn.r1q',
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
