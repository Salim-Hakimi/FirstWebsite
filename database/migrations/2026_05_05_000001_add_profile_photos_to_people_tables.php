<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'profile_photo_path')) {
                $table->string('profile_photo_path')->nullable()->after('theme');
            }
        });

        Schema::table('dorm_students', function (Blueprint $table) {
            if (! Schema::hasColumn('dorm_students', 'profile_photo_path')) {
                $table->string('profile_photo_path')->nullable()->after('document_names');
            }
        });

        Schema::table('library_members', function (Blueprint $table) {
            if (! Schema::hasColumn('library_members', 'profile_photo_path')) {
                $table->string('profile_photo_path')->nullable()->after('address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('library_members', function (Blueprint $table) {
            if (Schema::hasColumn('library_members', 'profile_photo_path')) {
                $table->dropColumn('profile_photo_path');
            }
        });

        Schema::table('dorm_students', function (Blueprint $table) {
            if (Schema::hasColumn('dorm_students', 'profile_photo_path')) {
                $table->dropColumn('profile_photo_path');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'profile_photo_path')) {
                $table->dropColumn('profile_photo_path');
            }
        });
    }
};
