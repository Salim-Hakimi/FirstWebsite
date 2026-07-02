<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookCopy;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LibraryInventoryCopiesController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(array_keys($this->statusLabels()))],
            'shelf' => ['nullable', 'string', 'max:80'],
            'category' => ['nullable', 'string', 'max:120'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:30'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 10);
        $canWrite = $request->user()?->role === User::ROLE_LIBRARIAN;

        $query = BookCopy::query()->with('book');
        $this->applyFilters($query, $validated);

        $copies = $query
            ->orderBy('status')
            ->orderBy('shelf_code')
            ->orderBy('copy_code')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'data' => $copies->getCollection()
                ->map(fn (BookCopy $copy): array => $this->copyPayload($copy, $canWrite))
                ->values(),
            'meta' => [
                'current_page' => $copies->currentPage(),
                'last_page' => $copies->lastPage(),
                'per_page' => $copies->perPage(),
                'total' => $copies->total(),
                'from' => $copies->firstItem(),
                'to' => $copies->lastItem(),
            ],
            'summary' => $this->summaryPayload(),
            'filters' => [
                'q' => $validated['q'] ?? '',
                'status' => $validated['status'] ?? '',
                'shelf' => $validated['shelf'] ?? '',
                'category' => $validated['category'] ?? '',
                'statuses' => $this->statusLabels(),
                'categories' => $this->categoryOptions(),
            ],
        ]);
    }

    private function applyFilters($query, array $filters): void
    {
        $query
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['shelf'] ?? null, fn ($query, string $shelf) => $query->where('shelf_code', 'like', "%{$shelf}%"))
            ->when($filters['category'] ?? null, fn ($query, string $category) => $query->whereHas('book', fn ($bookQuery) => $bookQuery->where('category', $category)))
            ->when($filters['q'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('copy_code', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('shelf_code', 'like', "%{$search}%")
                        ->orWhere('condition', 'like', "%{$search}%")
                        ->orWhereHas('book', function ($bookQuery) use ($search): void {
                            $bookQuery
                                ->where('title', 'like', "%{$search}%")
                                ->orWhere('author', 'like', "%{$search}%")
                                ->orWhere('isbn', 'like', "%{$search}%")
                                ->orWhere('category', 'like', "%{$search}%");
                        });
                });
            });
    }

    private function copyPayload(BookCopy $copy, bool $canWrite): array
    {
        return [
            'id' => $copy->id,
            'copy_code' => $copy->copy_code,
            'barcode' => $copy->barcode,
            'shelf_code' => $copy->shelf_code,
            'status' => $copy->status,
            'status_label' => $this->statusLabels()[$copy->status] ?? $copy->status,
            'condition' => $copy->condition,
            'purchase_price' => (int) $copy->purchase_price,
            'notes' => $copy->notes,
            'book' => $copy->book ? [
                'id' => $copy->book->id,
                'title' => $copy->book->title,
                'author' => $copy->book->author,
                'isbn' => $copy->book->isbn,
                'category' => $copy->book->category,
            ] : null,
            'links' => [
                'manage' => ($canWrite && $copy->book) ? route('library.books.edit', $copy->book) : null,
            ],
        ];
    }

    private function summaryPayload(): array
    {
        $counts = BookCopy::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'total' => (int) $counts->sum(),
            'available' => (int) $counts->get('available', 0),
            'on_loan' => (int) $counts->get('on_loan', 0),
            'problem' => (int) $counts->get('damaged', 0) + (int) $counts->get('lost', 0),
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
            'available' => 'موجود',
            'on_loan' => 'در امانت',
            'damaged' => 'خراب',
            'lost' => 'گم‌شده',
            'archived' => 'آرشیف',
        ];
    }
}
