<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dorm_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number', 40)->unique();
            $table->unsignedTinyInteger('capacity');
            $table->string('floor', 40)->nullable();
            $table->string('status', 40)->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('dorm_students', function (Blueprint $table) {
            $table->foreignId('dorm_room_id')
                ->nullable()
                ->after('registered_by')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('dorm_students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dorm_room_id');
        });

        Schema::dropIfExists('dorm_rooms');
    }
};
