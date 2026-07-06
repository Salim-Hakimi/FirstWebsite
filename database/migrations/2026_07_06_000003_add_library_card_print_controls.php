<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('membership_cards')) {
            Schema::table('membership_cards', function (Blueprint $table): void {
                if (! Schema::hasColumn('membership_cards', 'card_printed')) {
                    $table->boolean('card_printed')->default(false)->after('paid_at');
                }

                if (! Schema::hasColumn('membership_cards', 'printed_at')) {
                    $table->timestamp('printed_at')->nullable()->after('card_printed');
                }

                if (! Schema::hasColumn('membership_cards', 'replacement_reason')) {
                    $table->string('replacement_reason', 255)->nullable()->after('printed_at');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('membership_cards')) {
            Schema::table('membership_cards', function (Blueprint $table): void {
                foreach (['replacement_reason', 'printed_at', 'card_printed'] as $column) {
                    if (Schema::hasColumn('membership_cards', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
