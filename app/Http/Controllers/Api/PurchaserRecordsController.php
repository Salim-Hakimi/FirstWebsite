<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FoodFinance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PurchaserRecordsController extends Controller
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

        $query = FoodFinance::query()->with(['student.room', 'recordedBy']);
        $this->applyFilters($query, $validated);

        $records = $query
            ->latest('recorded_at')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'data' => $records->getCollection()
                ->map(fn (FoodFinance $record): array => $this->recordPayload($record))
                ->values(),
            'meta' => [
                'current_page' => $records->currentPage(),
                'last_page' => $records->lastPage(),
                'per_page' => $records->perPage(),
                'total' => $records->total(),
                'from' => $records->firstItem(),
                'to' => $records->lastItem(),
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
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('recorded_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('recorded_at', '<=', $date))
            ->when($filters['period'] ?? null, fn ($query, string $period) => $query->where('period', 'like', "%{$period}%"))
            ->when($filters['q'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('vendor_or_source', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
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

    private function recordPayload(FoodFinance $record): array
    {
        $student = $record->student;

        return [
            'id' => $record->id,
            'type' => $record->type,
            'type_label' => $this->typeLabels()[$record->type] ?? $record->type,
            'is_expense' => $record->type === 'expense',
            'amount' => (int) $record->amount,
            'recorded_at' => $record->recorded_at?->toDateString(),
            'period' => $record->period,
            'vendor_or_source' => $record->vendor_or_source,
            'description' => $record->description,
            'student' => $student ? [
                'id' => $student->id,
                'full_name' => $student->full_name,
                'father_name' => $student->father_name,
                'phone' => $student->phone,
                'room_number' => $student->room?->room_number ?: $student->room_number,
                'profile_url' => route('dorm.students.show', $student),
            ] : null,
            'recorded_by' => $record->recordedBy?->name ?: 'سیستم',
            'links' => [
                'receipt' => route('purchaser.records.receipt', $record),
                'student' => $student ? route('dorm.students.show', $student) : null,
            ],
        ];
    }

    private function summaryPayload(): array
    {
        $income = (int) FoodFinance::query()
            ->whereIn('type', $this->incomeTypes())
            ->sum('amount');
        $expense = (int) FoodFinance::query()
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
            'contribution' => 'Food contribution',
            'weekly_food' => 'Weekly food',
            'monthly_fee' => 'Monthly fee',
            'electricity' => 'Electricity',
            'water' => 'Water',
            'expense' => 'Expense and purchase',
        ];
    }

    private function incomeTypes(): array
    {
        return ['contribution', 'weekly_food', 'monthly_fee', 'electricity', 'water'];
    }
}
