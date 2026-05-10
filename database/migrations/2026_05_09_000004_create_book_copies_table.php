<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_copies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->string('copy_code', 80)->unique();
            $table->string('barcode', 100)->nullable()->unique();
            $table->string('shelf_code', 80)->nullable();
            $table->string('status', 40)->default('available');
            $table->string('condition', 120)->nullable();
            $table->unsignedInteger('purchase_price')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::table('book_loans', function (Blueprint $table) {
            if (! Schema::hasColumn('book_loans', 'book_copy_id')) {
                $table->foreignId('book_copy_id')->nullable()->after('book_id')->constrained('book_copies')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('book_loans', function (Blueprint $table) {
            if (Schema::hasColumn('book_loans', 'book_copy_id')) {
                $table->dropConstrainedForeignId('book_copy_id');
            }
        });

        Schema::dropIfExists('book_copies');
    }
};
