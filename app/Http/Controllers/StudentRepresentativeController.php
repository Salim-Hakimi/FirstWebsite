<?php

namespace App\Http\Controllers;

use App\Models\DormStudent;
use App\Models\StudentCollection;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentRepresentativeController extends Controller
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

        $collectionsQuery = StudentCollection::query()->with(['student.room', 'recordedBy']);
        $this->applyReportFilters($collectionsQuery, $filters);
        $this->applyStudentSearch($collectionsQuery, $filters['q'] ?? null);

        return view('dorm.representative.index', [
            'students' => $students,
            'collections' => $collectionsQuery
                ->latest('collected_at')
                ->latest()
                ->limit(50)
                ->get(),
            'filters' => $filters,
            'typeLabels' => $this->typeLabels(),
            'incomeTypeLabels' => collect($this->typeLabels())->except('expense')->all(),
            'totalMonthly' => (int) StudentCollection::where('type', 'monthly_fee')->sum('amount'),
            'totalElectricity' => (int) StudentCollection::where('type', 'electricity')->sum('amount'),
            'totalFines' => (int) StudentCollection::where('type', 'fine')->sum('amount'),
            'totalWater' => (int) StudentCollection::where('type', 'water')->sum('amount'),
            'totalIncome' => (int) StudentCollection::whereIn('type', $this->incomeTypes())->sum('amount'),
            'totalExpenses' => (int) StudentCollection::where('type', 'expense')->sum('amount'),
            'canRecord' => auth()->user()->role === User::ROLE_STUDENT_REPRESENTATIVE,
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

        $recordsQuery = StudentCollection::query()->with(['student.room', 'recordedBy']);
        $this->applyReportFilters($recordsQuery, $filters);
        $records = $recordsQuery
            ->latest('collected_at')
            ->latest()
            ->get();

        $totalIncome = (int) $records->whereIn('type', $this->incomeTypes())->sum('amount');
        $totalExpenses = (int) $records->where('type', 'expense')->sum('amount');

        return view('dorm.representative.report', [
            'filters' => $filters,
            'group' => $filters['group'] ?? 'daily',
            'records' => $records,
            'summaryRows' => $this->summaryRows($records, $filters['group'] ?? 'daily'),
            'typeLabels' => $this->typeLabels(),
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'balance' => $totalIncome - $totalExpenses,
            'totalsByType' => $records->groupBy('type')->map(fn ($items) => (int) $items->sum('amount')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'dorm_student_id' => ['required_unless:type,expense', 'nullable', 'exists:dorm_students,id'],
            'type' => ['required', Rule::in(array_keys($this->typeLabels()))],
            'amount' => ['required', 'integer', 'min:1'],
            'collected_at' => ['required', 'date'],
            'period' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validated['type'] === 'expense') {
            $validated['dorm_student_id'] = null;
        }

        $collection = StudentCollection::create(array_merge($validated, [
            'recorded_by' => $request->user()->id,
        ]));

        return redirect()
            ->route('representative.collections.receipt', $collection)
            ->with('status', 'ثبت مالی نماینده ذخیره شد. رسید آماده چاپ است.');
    }

    public function receipt(StudentCollection $collection): View
    {
        $collection->load(['student.room', 'recordedBy']);

        return view('dorm.receipts.finance', [
            'title' => 'رسید حساب نماینده محصلین',
            'subtitle' => 'لیلیه فانوس',
            'receiptNumber' => 'REP-'.str_pad((string) $collection->id, 6, '0', STR_PAD_LEFT),
            'typeLabel' => $this->typeLabels()[$collection->type] ?? $collection->type,
            'amount' => (int) $collection->amount,
            'date' => $collection->collected_at,
            'period' => $collection->period,
            'student' => $collection->student,
            'recordedBy' => $collection->recordedBy,
            'noteLabel' => 'یادداشت',
            'note' => $collection->notes,
            'backRoute' => route('representative.index'),
            'profileRoute' => $collection->student ? route('dorm.students.show', $collection->student) : null,
        ]);
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

    private function applyReportFilters($query, array $filters): void
    {
        $query
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('collected_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('collected_at', '<=', $date))
            ->when($filters['period'] ?? null, fn ($query, $period) => $query->where('period', 'like', "%{$period}%"))
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type));
    }

    private function applyStudentSearch($query, ?string $search): void
    {
        $query->when($search, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query
                    ->where('notes', 'like', "%{$search}%")
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
            ->groupBy(function (StudentCollection $record) use ($group) {
                return match ($group) {
                    'weekly' => $record->collected_at?->copy()->startOfWeek()->format('Y-m-d').' تا '.$record->collected_at?->copy()->endOfWeek()->format('Y-m-d'),
                    'monthly' => $record->collected_at?->format('Y-m'),
                    default => $record->collected_at?->format('Y-m-d'),
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
