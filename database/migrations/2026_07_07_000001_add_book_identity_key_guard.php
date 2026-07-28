<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('books')) {
            return;
        }

        if (! Schema::hasColumn('books', 'identity_key')) {
            Schema::table('books', function (Blueprint $table): void {
                $table->string('identity_key', 40)->nullable()->after('edition');
            });
        }

        DB::table('books')
            ->whereNull('identity_key')
            ->orderBy('id')
            ->get(['id', 'title', 'author', 'publisher', 'edition'])
            ->each(function ($book): void {
                DB::table('books')
                    ->where('id', $book->id)
                    ->update(['identity_key' => $this->identityKey((array) $book)]);
            });

        $this->addUniqueWhenClean('books', ['identity_key'], 'books_identity_key_unique');
    }

    public function down(): void
    {
        if (! Schema::hasTable('books') || ! Schema::hasColumn('books', 'identity_key')) {
            return;
        }

        if ($this->indexExists('books', 'books_identity_key_unique')) {
            Schema::table('books', fn (Blueprint $table) => $table->dropIndex('books_identity_key_unique'));
        }

        Schema::table('books', fn (Blueprint $table) => $table->dropColumn('identity_key'));
    }

    private function identityKey(array $book): string
    {
        $parts = [
            $book['title'] ?? '',
            $book['author'] ?? '',
            $book['publisher'] ?? '',
            $book['edition'] ?? '',
        ];

        $normalized = array_map(function ($value): string {
            $value = mb_strtolower(trim((string) $value));

            return preg_replace('/\s+/u', ' ', $value) ?: '';
        }, $parts);

        return sha1(implode('|', $normalized));
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

        $hasDuplicates = DB::table($table)
            ->select($columns)
            ->whereNotNull($columns[0])
            ->groupBy($columns)
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicates) {
            return;
        }

        Schema::table($table, fn (Blueprint $table) => $table->unique($columns, $index));
    }

    private function indexExists(string $table, string $name): bool
    {
        return collect(Schema::getIndexes($table))
            ->contains(fn (array $index): bool => ($index['name'] ?? null) === $name);
    }
};
