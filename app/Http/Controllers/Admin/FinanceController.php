<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DormStudent;
use App\Models\FinanceCategory;
use App\Models\FinanceTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', Rule::in([FinanceTransaction::TYPE_INCOME, FinanceTransaction::TYPE_EXPENSE])],
            'category' => ['nullable', 'integer', 'exists:finance_categories,id'],
            'payment_status' => ['nullable', Rule::in(array_keys($this->paymentStatusLabels()))],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'period' => ['nullable', 'string', 'max:80'],
        ]);

        $transactionsQuery = FinanceTransaction::query()->with(['category', 'student.room', 'recordedBy']);
        $this->applyFilters($transactionsQuery, $filters);

        $summaryQuery = FinanceTransaction::query();
        $this->applyFilters($summaryQuery, $filters);

        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $incomeTotal = (int) (clone $summaryQuery)->where('type', FinanceTransaction::TYPE_INCOME)->sum('amount');
        $expenseTotal = (int) (clone $summaryQuery)->where('type', FinanceTransaction::TYPE_EXPENSE)->sum('amount');
        $monthIncome = (int) FinanceTransaction::where('type', FinanceTransaction::TYPE_INCOME)
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->sum('amount');
        $monthExpense = (int) FinanceTransaction::where('type', FinanceTransaction::TYPE_EXPENSE)
            ->whereBetween('transaction_date', [$monthStart, $monthEnd])
            ->sum('amount');
        $studentDebt = (int) FinanceTransaction::whereNotNull('dorm_student_id')
            ->whereNotNull('expected_amount')
            ->whereIn('payment_status', ['partial', 'unpaid'])
            ->get()
            ->sum(fn (FinanceTransaction $transaction) => $transaction->balance);

        $recentTransactions = $transactionsQuery
            ->latest('transaction_date')
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $monthlyRows = FinanceTransaction::query()
            ->latest('transaction_date')
            ->limit(500)
            ->get()
            ->groupBy(fn (FinanceTransaction $transaction) => $transaction->transaction_date?->format('Y-m') ?? 'Unknown')
            ->map(function ($items, string $month) {
                $income = (int) $items->where('type', FinanceTransaction::TYPE_INCOME)->sum('amount');
                $expense = (int) $items->where('type', FinanceTransaction::TYPE_EXPENSE)->sum('amount');

                return [
                    'month' => $month,
                    'income' => $income,
                    'expense' => $expense,
                    'balance' => $income - $expense,
                ];
            })
            ->values();

        return view('admin.finance.index', [
            'filters' => $filters,
            'transactions' => $recentTransactions,
            'categories' => FinanceCategory::query()->where('is_active', true)->orderBy('type')->orderBy('name')->get(),
            'incomeCategories' => FinanceCategory::query()->where('type', FinanceCategory::TYPE_INCOME)->where('is_active', true)->orderBy('name')->get(),
            'expenseCategories' => FinanceCategory::query()->where('type', FinanceCategory::TYPE_EXPENSE)->where('is_active', true)->orderBy('name')->get(),
            'students' => DormStudent::query()->with('room')->orderBy('full_name')->get(),
            'paymentMethods' => $this->paymentMethodLabels(),
            'paymentStatuses' => $this->paymentStatusLabels(),
            'incomeTotal' => $incomeTotal,
            'expenseTotal' => $expenseTotal,
            'balance' => $incomeTotal - $expenseTotal,
            'monthIncome' => $monthIncome,
            'monthExpense' => $monthExpense,
            'studentDebt' => $studentDebt,
            'monthlyRows' => $monthlyRows,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in([FinanceTransaction::TYPE_INCOME, FinanceTransaction::TYPE_EXPENSE])],
            'finance_category_id' => ['required', 'exists:finance_categories,id'],
            'dorm_student_id' => ['nullable', 'exists:dorm_students,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'expected_amount' => ['nullable', 'integer', 'min:1'],
            'transaction_date' => ['required', 'date'],
            'period' => ['nullable', 'string', 'max:80'],
            'payment_method' => ['required', Rule::in(array_keys($this->paymentMethodLabels()))],
            'payment_status' => ['required', Rule::in(array_keys($this->paymentStatusLabels()))],
            'payer_name' => ['nullable', 'string', 'max:160'],
            'payee_name' => ['nullable', 'string', 'max:160'],
            'donor_name' => ['nullable', 'string', 'max:160'],
            'donor_phone' => ['nullable', 'string', 'max:60'],
            'project_name' => ['nullable', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'max:4096', 'mimes:jpg,jpeg,png,pdf,webp'],
        ]);

        $category = FinanceCategory::findOrFail($validated['finance_category_id']);
        abort_unless($category->type === $validated['type'], 422);

        if ($validated['type'] === FinanceTransaction::TYPE_EXPENSE) {
            $validated['dorm_student_id'] = null;
            $validated['payer_name'] = null;
            $validated['donor_name'] = null;
            $validated['donor_phone'] = null;
            $validated['payment_status'] = 'completed';
            $validated['expected_amount'] = null;
        }

        if ($request->hasFile('attachment')) {
            $validated['attachment_path'] = $request->file('attachment')->store('finance-attachments', 'public');
        }

        unset($validated['attachment']);

        $transaction = FinanceTransaction::create(array_merge($validated, [
            'transaction_number' => $this->nextTransactionNumber($validated['type']),
            'recorded_by' => $request->user()->id,
        ]));

        return redirect()
            ->route('admin.finance.receipt', $transaction)
            ->with('status', 'Finance transaction saved. The receipt is ready to print.');
    }

    public function receipt(FinanceTransaction $transaction): View
    {
        $transaction->load(['category', 'student.room', 'recordedBy']);

        return view('admin.finance.receipt', [
            'transaction' => $transaction,
            'paymentMethods' => $this->paymentMethodLabels(),
            'paymentStatuses' => $this->paymentStatusLabels(),
        ]);
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['q'] ?? null, function (Builder $query, string $search) {
                $query->where(function (Builder $query) use ($search) {
                    $query
                        ->where('transaction_number', 'like', "%{$search}%")
                        ->orWhere('payer_name', 'like', "%{$search}%")
                        ->orWhere('payee_name', 'like', "%{$search}%")
                        ->orWhere('donor_name', 'like', "%{$search}%")
                        ->orWhere('project_name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('student', fn (Builder $query) => $query
                            ->where('full_name', 'like', "%{$search}%")
                            ->orWhere('father_name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%"));
                });
            })
            ->when($filters['type'] ?? null, fn (Builder $query, string $type) => $query->where('type', $type))
            ->when($filters['category'] ?? null, fn (Builder $query, int $category) => $query->where('finance_category_id', $category))
            ->when($filters['payment_status'] ?? null, fn (Builder $query, string $status) => $query->where('payment_status', $status))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->whereDate('transaction_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->whereDate('transaction_date', '<=', $date))
            ->when($filters['period'] ?? null, fn (Builder $query, string $period) => $query->where('period', 'like', "%{$period}%"));
    }

    private function nextTransactionNumber(string $type): string
    {
        $prefix = $type === FinanceTransaction::TYPE_INCOME ? 'INC' : 'EXP';

        do {
            $number = $prefix.'-'.now()->format('Ymd').'-'.Str::upper(Str::random(5));
        } while (FinanceTransaction::where('transaction_number', $number)->exists());

        return $number;
    }

    private function paymentMethodLabels(): array
    {
        return [
            'cash' => 'Cash',
            'bank' => 'Bank',
            'hawala' => 'Hawala',
            'card' => 'Card',
            'other' => 'Other',
        ];
    }

    private function paymentStatusLabels(): array
    {
        return [
            'completed' => 'Completed',
            'partial' => 'Partial',
            'unpaid' => 'Unpaid',
        ];
    }
}
