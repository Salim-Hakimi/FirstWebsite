<?php

namespace App\Http\Controllers;

use App\Models\DormStudent;
use App\Models\FoodFinance;
use App\Models\User;
use App\Support\Audit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PurchaserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', Rule::in(array_keys($this->typeLabels()))],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'period' => ['nullable', 'string', 'max:80'],
        ]);

        $students = DormStudent::query()
            ->where('status', 'active')
            ->with('room')
            ->withSum(['foodFinances as food_paid_total' => fn ($query) => $query->whereIn('type', $this->incomeTypes())], 'amount')
            ->when($filters['q'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('full_name', 'like', "%{$search}%")
                        ->orWhere('father_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('tazkira_number', 'like', "%{$search}%")
                        ->orWhereHas('room', fn ($query) => $query->where('room_number', 'like', "%{$search}%"));
                });
            })
            ->orderBy('full_name')
            ->get();

        $recordsQuery = FoodFinance::query()->with(['student', 'recordedBy']);
        $this->applyReportFilters($recordsQuery, $filters);
        $this->applyStudentSearch($recordsQuery, $filters['q'] ?? null);

        return view('dorm.purchaser.index', [
            'students' => $students,
            'records' => $recordsQuery
                ->latest('recorded_at')
                ->latest()
                ->limit(50)
                ->get(),
            'filters' => $filters,
            'typeLabels' => $this->typeLabels(),
            'incomeTypeLabels' => collect($this->typeLabels())->only($this->incomeTypes())->all(),
            'totalCollected' => (int) FoodFinance::whereIn('type', $this->incomeTypes())->sum('amount'),
            'totalExpenses' => (int) FoodFinance::where('type', 'expense')->sum('amount'),
            'canRecord' => auth()->user()->role === User::ROLE_PURCHASER,
        ]);
    }

    public function report(Request $request): View
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'period' => ['nullable', 'string', 'max:80'],
            'type' => ['nullable', Rule::in(array_keys($this->typeLabels()))],
            'group' => ['nullable', Rule::in(['daily', 'weekly', 'monthly'])],
        ]);

        $baseQuery = FoodFinance::query();
        $this->applyReportFilters($baseQuery, $filters);

        $studentTotals = DormStudent::query()
            ->where('status', 'active')
            ->with('room')
            ->withSum(['foodFinances as report_food_paid_total' => function ($query) use ($filters) {
                $query->whereIn('type', $this->incomeTypes());
                $this->applyReportFilters($query, $filters);
            }], 'amount')
            ->orderBy('full_name')
            ->get();

        $recordsQuery = FoodFinance::query()->with(['student.room', 'recordedBy']);
        $this->applyReportFilters($recordsQuery, $filters);

        $totalCollected = (clone $baseQuery)->whereIn('type', $this->incomeTypes())->sum('amount');
        $totalExpenses = (clone $baseQuery)->where('type', 'expense')->sum('amount');
        $records = $recordsQuery
            ->latest('recorded_at')
            ->latest()
            ->get();

        return view('dorm.purchaser.report', [
            'filters' => $filters,
            'group' => $filters['group'] ?? 'daily',
            'studentTotals' => $studentTotals,
            'records' => $records,
            'summaryRows' => $this->summaryRows($records, $filters['group'] ?? 'daily'),
            'typeLabels' => $this->typeLabels(),
            'totalCollected' => (int) $totalCollected,
            'totalExpenses' => (int) $totalExpenses,
            'balance' => (int) $totalCollected - (int) $totalExpenses,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'dorm_student_id' => ['required_unless:type,expense', 'nullable', 'exists:dorm_students,id'],
            'type' => ['required', Rule::in(array_keys($this->typeLabels()))],
            'amount' => ['required', 'integer', 'min:1'],
            'recorded_at' => ['required', 'date'],
            'period' => ['nullable', 'string', 'max:80'],
            'vendor_or_source' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:700'],
        ]);

        if ($validated['type'] === 'expense') {
            $validated['dorm_student_id'] = null;
        } elseif (! DormStudent::query()->whereKey($validated['dorm_student_id'])->where('status', 'active')->exists()) {
            return back()->withErrors(['dorm_student_id' => 'Student is not active or cannot receive this record.'])->withInput();
        }

        $record = FoodFinance::create(array_merge($validated, [
            'recorded_by' => $request->user()->id,
        ]));
        Audit::record('purchaser_finance_record_created', $record, [], $record->only(['dorm_student_id', 'type', 'amount', 'recorded_at', 'recorded_by']), $request);

        return redirect()
            ->route('purchaser.records.receipt', $record)
            ->with('status', 'Finance record saved. The receipt is ready to print.');
    }

    public function receipt(FoodFinance $record): View
    {
        $record->load(['student.room', 'recordedBy']);

        return view('dorm.receipts.finance', [
            'title' => $record->type === 'expense' ? 'Finance Expense Receipt' : 'Finance Collection Receipt',
            'subtitle' => 'Fanous Dormitory',
            'receiptNumber' => 'PUR-'.str_pad((string) $record->id, 6, '0', STR_PAD_LEFT),
            'typeLabel' => $this->typeLabels()[$record->type] ?? $record->type,
            'amount' => (int) $record->amount,
            'date' => $record->recorded_at,
            'period' => $record->period,
            'student' => $record->student,
            'recordedBy' => $record->recordedBy,
            'noteLabel' => $record->type === 'expense' ? 'Expense note' : 'Note',
            'note' => $record->description,
            'sourceLabel' => $record->type === 'expense' ? 'Vendor / source' : 'Source / receipt number',
            'source' => $record->vendor_or_source,
            'backRoute' => route('purchaser.index'),
            'profileRoute' => $record->student ? route('dorm.students.show', $record->student) : null,
        ]);
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

    private function applyReportFilters($query, array $filters): void
    {
        $query
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('recorded_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('recorded_at', '<=', $date))
            ->when($filters['period'] ?? null, fn ($query, $period) => $query->where('period', 'like', "%{$period}%"))
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type));
    }

    private function applyStudentSearch($query, ?string $search): void
    {
        $query->when($search, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query
                    ->where('vendor_or_source', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('student', function ($query) use ($search) {
                        $query
                            ->where('full_name', 'like', "%{$search}%")
                            ->orWhere('father_name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%")
                            ->orWhere('tazkira_number', 'like', "%{$search}%")
                            ->orWhereHas('room', fn ($query) => $query->where('room_number', 'like', "%{$search}%"));
                    });
            });
        });
    }

    private function summaryRows($records, string $group)
    {
        return $records
            ->groupBy(function (FoodFinance $record) use ($group) {
                return match ($group) {
                    'weekly' => $record->recorded_at?->copy()->startOfWeek()->format('Y-m-d').' to '.$record->recorded_at?->copy()->endOfWeek()->format('Y-m-d'),
                    'monthly' => $record->recorded_at?->format('Y-m'),
                    default => $record->recorded_at?->format('Y-m-d'),
                };
            })
            ->map(function ($items, $label) {
                $income = (int) $items->whereIn('type', $this->incomeTypes())->sum('amount');
                $expense = (int) $items->where('type', 'expense')->sum('amount');

                return [
                    'label' => $label,
                    'income' => $income,
                    'expense' => $expense,
                    'balance' => $income - $expense,
                    'count' => $items->count(),
                ];
            })
            ->values();
    }
}
