<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LibraryBooksController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(array_keys($this->statusLabels()))],
            'category' => ['nullable', 'string', 'max:80'],
            'shelf' => ['nullable', 'string', 'max:80'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:30'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 10);
        $canWrite = $request->user()?->role === User::ROLE_LIBRARIAN;

        $books = Book::query()
            ->withCount('copies')
            ->when($validated['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($validated['category'] ?? null, fn ($query, string $category) => $query->where('category', $category))
            ->when($validated['shelf'] ?? null, fn ($query, string $shelf) => $query->where('shelf_code', 'like', "%{$shelf}%"))
            ->when($validated['q'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('author', 'like', "%{$search}%")
                        ->orWhere('publisher', 'like', "%{$search}%")
                        ->orWhere('isbn', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhere('shelf_code', 'like', "%{$search}%")
                        ->orWhereHas('copies', function ($copyQuery) use ($search): void {
                            $copyQuery
                                ->where('copy_code', 'like', "%{$search}%")
                                ->orWhere('barcode', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'data' => $books->getCollection()
                ->map(fn (Book $book): array => $this->bookPayload($book, $canWrite))
                ->values(),
            'meta' => [
                'current_page' => $books->currentPage(),
                'last_page' => $books->lastPage(),
                'per_page' => $books->perPage(),
                'total' => $books->total(),
                'from' => $books->firstItem(),
                'to' => $books->lastItem(),
            ],
            'summary' => $this->summaryPayload(),
            'filters' => [
                'q' => $validated['q'] ?? '',
                'status' => $validated['status'] ?? '',
                'category' => $validated['category'] ?? '',
                'shelf' => $validated['shelf'] ?? '',
                'statuses' => $this->statusLabels(),
                'categories' => $this->categoryOptions(),
            ],
        ]);
    }

    private function bookPayload(Book $book, bool $canWrite): array
    {
        return [
            'id' => $book->id,
            'title' => $book->title,
            'author' => $book->author,
            'publisher' => $book->publisher,
            'isbn' => $book->isbn,
            'category' => $book->category,
            'shelf_code' => $book->shelf_code,
            'barcode' => $book->barcode,
            'status' => $book->status,
            'status_label' => $this->statusLabels()[$book->status] ?? $book->status,
            'total_copies' => (int) $book->total_copies,
            'available_copies' => (int) $book->available_copies,
            'physical_copies' => (int) $book->copies_count,
            'links' => [
                'edit' => $canWrite ? route('library.books.edit', $book) : null,
                'labels' => $canWrite ? route('library.books.copy-labels', $book) : null,
            ],
        ];
    }

    private function summaryPayload(): array
    {
        return [
            'titles' => Book::query()->count(),
            'available_copies' => (int) Book::query()->sum('available_copies'),
            'total_copies' => (int) Book::query()->sum('total_copies'),
            'needs_attention' => Book::query()->whereIn('status', ['damaged', 'lost'])->count(),
        ];
    }

    private function categoryOptions(): array
    {
        return Book::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->values()
            ->all();
    }

    private function statusLabels(): array
    {
        return [
            'available' => 'قابل استفاده',
            'damaged' => 'خراب',
            'lost' => 'گم‌شده',
            'archived' => 'آرشیف',
        ];
    }
}
