<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RepresentativeCollectionsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', Rule::in(array_keys($this->typeLabels()))],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'period' => ['nullable', 'string', 'max:80'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:30'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 10);

        $query = StudentCollection::query()->with(['student.room', 'recordedBy']);
        $this->applyFilters($query, $validated);

        $collections = $query
            ->latest('collected_at')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'data' => $collections->getCollection()
                ->map(fn (StudentCollection $collection): array => $this->collectionPayload($collection))
                ->values(),
            'meta' => [
                'current_page' => $collections->currentPage(),
                'last_page' => $collections->lastPage(),
                'per_page' => $collections->perPage(),
                'total' => $collections->total(),
                'from' => $collections->firstItem(),
                'to' => $collections->lastItem(),
            ],
            'summary' => $this->summaryPayload(),
            'filters' => [
                'q' => $validated['q'] ?? '',
                'type' => $validated['type'] ?? '',
                'date_from' => $validated['date_from'] ?? '',
                'date_to' => $validated['date_to'] ?? '',
                'period' => $validated['period'] ?? '',
                'types' => $this->typeLabels(),
            ],
        ]);
    }

    private function applyFilters($query, array $filters): void
    {
        $query
            ->when($filters['type'] ?? null, fn ($query, string $type) => $query->where('type', $type))
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('collected_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('collected_at', '<=', $date))
            ->when($filters['period'] ?? null, fn ($query, string $period) => $query->where('period', 'like', "%{$period}%"))
            ->when($filters['q'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('notes', 'like', "%{$search}%")
                        ->orWhereHas('student', function ($studentQuery) use ($search): void {
                            $studentQuery
                                ->where('full_name', 'like', "%{$search}%")
                                ->orWhere('father_name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->orWhere('tazkira_number', 'like', "%{$search}%")
                                ->orWhereHas('room', fn ($roomQuery) => $roomQuery->where('room_number', 'like', "%{$search}%"));
                        });
                });
            });
    }

    private function collectionPayload(StudentCollection $collection): array
    {
        $student = $collection->student;

        return [
            'id' => $collection->id,
            'type' => $collection->type,
            'type_label' => $this->typeLabels()[$collection->type] ?? $collection->type,
            'is_expense' => $collection->type === 'expense',
            'amount' => (int) $collection->amount,
            'collected_at' => $collection->collected_at?->toDateString(),
            'period' => $collection->period,
            'notes' => $collection->notes,
            'student' => $student ? [
                'id' => $student->id,
                'full_name' => $student->full_name,
                'father_name' => $student->father_name,
                'phone' => $student->phone,
                'room_number' => $student->room?->room_number ?: $student->room_number,
                'profile_url' => route('dorm.students.show', $student),
            ] : null,
            'recorded_by' => $collection->recordedBy?->name ?: 'سیستم',
            'links' => [
                'receipt' => route('representative.collections.receipt', $collection),
                'student' => $student ? route('dorm.students.show', $student) : null,
            ],
        ];
    }

    private function summaryPayload(): array
    {
        $income = (int) StudentCollection::query()
            ->whereIn('type', $this->incomeTypes())
            ->sum('amount');
        $expense = (int) StudentCollection::query()
            ->where('type', 'expense')
            ->sum('amount');

        return [
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
        ];
    }

    private function typeLabels(): array
    {
        return [
            'monthly_fee' => 'پول ماهانه',
            'electricity' => 'پول برق',
            'fine' => 'جریمه',
            'water' => 'پول آب',
            'expense' => 'مصرف نماینده',
        ];
    }

    private function incomeTypes(): array
    {
        return ['monthly_fee', 'electricity', 'fine', 'water'];
    }
}
