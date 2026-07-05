<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('dorm_rooms') && ! Schema::hasColumn('dorm_rooms', 'building')) {
            Schema::table('dorm_rooms', function (Blueprint $table): void {
                $table->string('building', 80)->nullable()->after('room_number');
            });
        }

        if (Schema::hasTable('dorm_students')) {
            Schema::table('dorm_students', function (Blueprint $table): void {
                if (! Schema::hasColumn('dorm_students', 'left_at')) {
                    $table->date('left_at')->nullable()->after('joined_at');
                }

                if (! Schema::hasColumn('dorm_students', 'family_phone')) {
                    $table->string('family_phone', 30)->nullable()->after('whatsapp');
                }

                if (! Schema::hasColumn('dorm_students', 'district')) {
                    $table->string('district', 100)->nullable()->after('province');
                }

                if (! Schema::hasColumn('dorm_students', 'school_graduation_year')) {
                    $table->unsignedSmallInteger('school_graduation_year')->nullable()->after('department_or_grade');
                }

                if (! Schema::hasColumn('dorm_students', 'active_bed_key')) {
                    $table->string('active_bed_key', 120)->nullable()->after('bed_number');
                }
            });

            DB::table('dorm_students')
                ->where('status', 'active')
                ->whereNotNull('dorm_room_id')
                ->whereNotNull('bed_number')
                ->orderBy('id')
                ->get(['id', 'dorm_room_id', 'bed_number'])
                ->each(function ($student): void {
                    DB::table('dorm_students')
                        ->where('id', $student->id)
                        ->update(['active_bed_key' => $student->dorm_room_id.':'.$student->bed_number]);
                });
        }

        if (Schema::hasTable('books')) {
            DB::table('books')->where('isbn', '')->update(['isbn' => null]);
            $this->addUniqueWhenClean('books', ['isbn'], 'books_isbn_unique');
        }

        if (Schema::hasTable('library_members') && ! Schema::hasColumn('library_members', 'left_at')) {
            Schema::table('library_members', function (Blueprint $table): void {
                $table->date('left_at')->nullable()->after('joined_at');
            });
        }

        if (Schema::hasTable('book_loans')) {
            Schema::table('book_loans', function (Blueprint $table): void {
                if (! Schema::hasColumn('book_loans', 'active_book_copy_id')) {
                    $table->unsignedBigInteger('active_book_copy_id')->nullable()->after('book_copy_id');
                }
            });

            DB::table('book_loans')
                ->whereIn('status', ['borrowed', 'late'])
                ->whereNotNull('book_copy_id')
                ->orderBy('id')
                ->get(['id', 'book_copy_id'])
                ->each(function ($loan): void {
                    DB::table('book_loans')
                        ->where('id', $loan->id)
                        ->update(['active_book_copy_id' => $loan->book_copy_id]);
                });

            $this->addUniqueWhenClean('book_loans', ['active_book_copy_id'], 'book_loans_active_copy_unique');
        }

        $this->addUniqueWhenClean('dorm_students', ['active_bed_key'], 'dorm_students_active_bed_unique');
        $this->addUniqueWhenClean('dorm_rooms', ['building', 'floor', 'room_number'], 'dorm_rooms_building_floor_room_unique');
    }

    public function down(): void
    {
        $this->dropIndex('dorm_rooms', 'dorm_rooms_building_floor_room_unique');
        $this->dropIndex('dorm_students', 'dorm_students_active_bed_unique');
        $this->dropIndex('book_loans', 'book_loans_active_copy_unique');
        $this->dropIndex('books', 'books_isbn_unique');

        if (Schema::hasTable('library_members') && Schema::hasColumn('library_members', 'left_at')) {
            Schema::table('library_members', fn (Blueprint $table) => $table->dropColumn('left_at'));
        }

        if (Schema::hasTable('book_loans') && Schema::hasColumn('book_loans', 'active_book_copy_id')) {
            Schema::table('book_loans', fn (Blueprint $table) => $table->dropColumn('active_book_copy_id'));
        }

        if (Schema::hasTable('dorm_students')) {
            Schema::table('dorm_students', function (Blueprint $table): void {
                if (Schema::hasColumn('dorm_students', 'active_bed_key')) {
                    $table->dropColumn('active_bed_key');
                }

                if (Schema::hasColumn('dorm_students', 'left_at')) {
                    $table->dropColumn('left_at');
                }

                foreach (['family_phone', 'district', 'school_graduation_year'] as $column) {
                    if (Schema::hasColumn('dorm_students', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('dorm_rooms') && Schema::hasColumn('dorm_rooms', 'building')) {
            Schema::table('dorm_rooms', fn (Blueprint $table) => $table->dropColumn('building'));
        }
    }

    private function addUniqueWhenClean(string $table, array $columns, string $index): void
    {
        if (! Schema::hasTable($table) || $this->indexExists($table, $index)) {
            return;
        }

        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return;
            }
        }

        $query = DB::table($table)
            ->select($columns)
            ->whereNotNull($columns[0])
            ->groupBy($columns)
            ->havingRaw('COUNT(*) > 1');

        if ($query->exists()) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($columns, $index): void {
            $tableBlueprint->unique($columns, $index);
        });
    }

    private function dropIndex(string $table, string $name): void
    {
        if (! Schema::hasTable($table) || ! $this->indexExists($table, $name)) {
            return;
        }

        Schema::table($table, fn (Blueprint $tableBlueprint) => $tableBlueprint->dropIndex($name));
    }

    private function indexExists(string $table, string $name): bool
    {
        return collect(Schema::getIndexes($table))
            ->contains(fn (array $index): bool => ($index['name'] ?? null) === $name);
    }
};
