<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DormStudent;
use App\Models\FinanceAttachment;
use App\Models\FinanceAuditLog;
use App\Models\FinanceCategory;
use App\Models\FinanceDonor;
use App\Models\FinanceProject;
use App\Models\FinanceTransaction;
use App\Models\User;
use App\Support\Audit;
use App\Support\SecurityRules;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->validatedFilters($request);
        $transactionsQuery = $this->ledgerTransactionsQuery()
            ->with(['attachments', 'category', 'donor', 'project', 'recordedBy']);

        $this->applyFilters($transactionsQuery, $filters);
        $categorySummaryQuery = $this->ledgerTransactionsQuery()
            ->with('category')
            ->whereNotNull('finance_category_id');
        $this->applyFilters($categorySummaryQuery, array_merge($filters, ['category' => null]));

        $monthQuery = $this->ledgerTransactionsQuery()
            ->whereBetween('transaction_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()]);

        $registrationRevenueQuery = DormStudent::query()
            ->whereIn('registration_payment_status', ['paid', 'partial'])
            ->whereNotIn('status', ['waiting', 'on_hold', 'rejected']);
        $registrationRevenue = (int) (clone $registrationRevenueQuery)
            ->get()
            ->sum(fn (DormStudent $student) => (int) ($student->dorm_expense_fee_amount ?? 1000));
        $monthRegistrationRevenue = (int) (clone $registrationRevenueQuery)
            ->where(function ($query) {
                $query
                    ->whereBetween('registration_paid_at', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
                    ->orWhere(function ($query) {
                        $query
                            ->whereNull('registration_paid_at')
                            ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                    });
            })
            ->get()
            ->sum(fn (DormStudent $student) => (int) ($student->dorm_expense_fee_amount ?? 1000));

        $monthlyChart = $this->ledgerTransactionsQuery()
            ->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m') as month_key")
            ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income_total")
            ->selectRaw("SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expense_total")
            ->where('transaction_date', '>=', now()->subMonths(5)->startOfMonth()->toDateString())
            ->groupBy('month_key')
            ->orderBy('month_key')
            ->get();

        $projectRows = FinanceProject::query()
            ->withSum(['transactions as spent_total' => fn ($query) => $query->where('type', 'expense')], 'amount')
            ->latest()
            ->get();
        $incomeCategories = $this->incomeCategoriesQuery()->get();
        $expenseCategories = $this->expenseCategoriesQuery()->get();
        $periodReports = $this->periodReports();
        $libraryIncomeTotal = (int) $this->libraryFinanceTransactionsQuery()
            ->where('type', 'income')
            ->sum('amount');
        $libraryMonthIncome = (int) $this->libraryFinanceTransactionsQuery()
            ->where('type', 'income')
            ->whereBetween('transaction_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->sum('amount');
        $libraryTodayIncome = (int) $this->libraryFinanceTransactionsQuery()
            ->where('type', 'income')
            ->whereDate('transaction_date', today()->toDateString())
            ->sum('amount');

        return view('admin.finance.index', [
            'filters' => $filters,
            'transactions' => $transactionsQuery->latest('transaction_date')->latest()->limit(80)->get(),
            'categorySummaries' => $categorySummaryQuery
                ->selectRaw('finance_category_id, type, SUM(amount) as total_amount, COUNT(*) as records_count')
                ->groupBy('finance_category_id', 'type')
                ->orderByDesc('total_amount')
                ->limit(8)
                ->get(),
            'incomeCategories' => $incomeCategories,
            'expenseCategories' => $expenseCategories,
            'allCategories' => $incomeCategories->concat($expenseCategories),
            'donors' => FinanceDonor::query()
                ->withSum(['transactions as donated_total' => fn ($query) => $query->where('type', 'income')], 'amount')
                ->orderBy('name')
                ->get(),
            'projects' => $projectRows,
            'recorders' => User::query()->whereIn('role', User::managementRoles())->orderBy('name')->get(),
            'staffUsers' => User::query()->where('status', User::STATUS_ACTIVE)->orderBy('name')->get(),
            'registrationRevenue' => $registrationRevenue,
            'monthRegistrationRevenue' => $monthRegistrationRevenue,
            'periodReports' => $periodReports,
            'libraryIncomeTotal' => $libraryIncomeTotal,
            'libraryMonthIncome' => $libraryMonthIncome,
            'libraryTodayIncome' => $libraryTodayIncome,
            'totalIncome' => (int) $this->ledgerTransactionsQuery()->where('type', 'income')->sum('amount') + $registrationRevenue + $libraryIncomeTotal,
            'totalExpense' => (int) $this->ledgerTransactionsQuery()->where('type', 'expense')->sum('amount'),
            'monthIncome' => (int) (clone $monthQuery)->where('type', 'income')->sum('amount') + $monthRegistrationRevenue + $libraryMonthIncome,
            'monthExpense' => (int) (clone $monthQuery)->where('type', 'expense')->sum('amount'),
            'missingDocuments' => $this->ledgerTransactionsQuery()
                ->with(['category', 'donor', 'project', 'attachments'])
                ->where('attachment_required', true)
                ->doesntHave('attachments')
                ->latest('transaction_date')
                ->limit(10)
                ->get(),
            'monthlyChart' => $monthlyChart,
            'paymentMethods' => $this->paymentMethods(),
            'statusLabels' => $this->statusLabels(),
            'projectStatuses' => $this->projectStatuses(),
        ]);
    }

    public function storeTransaction(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['income', 'expense'])],
            'finance_category_id' => ['required', 'exists:finance_categories,id'],
            'finance_donor_id' => ['nullable', 'exists:finance_donors,id'],
            'finance_project_id' => ['nullable', 'exists:finance_projects,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'transaction_date' => ['required', 'date'],
            'payer_name' => ['nullable', 'string', 'max:180'],
            'payee_name' => ['nullable', 'string', 'max:180'],
            'source_or_payee' => ['nullable', 'string', 'max:180'],
            'receipt_number' => ['nullable', 'string', 'max:80', Rule::unique('finance_transactions', 'receipt_number')],
            'payment_method' => ['required', Rule::in(array_keys($this->paymentMethods()))],
            'status' => ['nullable', Rule::in(array_keys($this->statusLabels()))],
            'attachment_required' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string', 'max:1500'],
            'attachment' => SecurityRules::financeAttachment(),
        ]);
        $category = FinanceCategory::query()
            ->whereKey($validated['finance_category_id'])
            ->where('type', $validated['type'])
            ->where('is_active', true)
            ->first();

        if (
            ! $category
            || ($validated['type'] === 'income' && ! $this->incomeCategoriesQuery()->whereKey($category->id)->exists())
            || ($validated['type'] === 'expense' && ! $this->expenseCategoriesQuery()->whereKey($category->id)->exists())
        ) {
            throw ValidationException::withMessages([
                'finance_category_id' => 'دسته‌بندی مالی انتخاب‌شده معتبر نیست.',
            ]);
        }

        $validated['status'] = $this->calculatedStatus(
            (int) ($validated['expected_amount'] ?? $validated['amount']),
            (int) $validated['amount'],
            $validated['status'] ?? null
        );
        $validated['attachment_required'] = (bool) ($validated['attachment_required'] ?? false);
        $validated['receipt_number'] = $validated['receipt_number'] ?: $this->nextTransactionNumber($validated['type']);

        $validated['dorm_student_id'] = null;
        $validated['expected_amount'] = $validated['amount'];
        $validated['payment_month'] = null;
        $validated['payment_year'] = null;

        $transaction = FinanceTransaction::create(array_merge($validated, [
            'transaction_number' => $this->nextTransactionNumber($validated['type']),
            'recorded_by' => $request->user()->id,
        ]));

        if ($request->hasFile('attachment')) {
            $this->storeAttachment($transaction, $request);
        }

        FinanceAuditLog::create([
            'finance_transaction_id' => $transaction->id,
            'action' => 'created',
            'new_values' => $transaction->fresh()->toArray(),
            'performed_by' => $request->user()->id,
        ]);
        Audit::record('finance_transaction_created', $transaction, [], $transaction->fresh()->only(['transaction_number', 'type', 'amount', 'finance_category_id', 'payment_method', 'status', 'recorded_by']), $request);

        return back()->with('status', 'ثبت مالی موفقانه ذخیره شد و رسید آماده چاپ است.');
    }

    public function storeDonor(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:800'],
        ]);

        FinanceDonor::create($validated);

        return back()->with('status', 'پروفایل خیر ثبت شد.');
    }

    public function storeProject(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'category' => ['nullable', 'string', 'max:80'],
            'estimated_budget' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', Rule::in(array_keys($this->projectStatuses()))],
            'started_on' => ['nullable', 'date'],
            'completed_on' => ['nullable', 'date', 'after_or_equal:started_on'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        FinanceProject::create(array_merge($validated, [
            'estimated_budget' => $validated['estimated_budget'] ?? 0,
        ]));

        return back()->with('status', 'پروژه مالی جدید ثبت شد.');
    }

    public function receipt(FinanceTransaction $transaction): View
    {
        $transaction->load(['category', 'donor', 'project', 'student.room', 'recordedBy', 'attachments']);

        return view('admin.finance.receipt', [
            'transaction' => $transaction,
            'paymentMethods' => $this->paymentMethods(),
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    public function editTransaction(FinanceTransaction $transaction): View
    {
        $transaction->load(['category', 'donor', 'project', 'attachments']);
        $incomeCategories = $this->incomeCategoriesQuery()->get();
        $expenseCategories = $this->expenseCategoriesQuery()->get();

        return view('admin.finance.edit', [
            'transaction' => $transaction,
            'incomeCategories' => $incomeCategories,
            'expenseCategories' => $expenseCategories,
            'allCategories' => $incomeCategories->concat($expenseCategories),
            'donors' => FinanceDonor::query()->orderBy('name')->get(),
            'projects' => FinanceProject::query()->latest()->get(),
            'staffUsers' => User::query()->where('status', User::STATUS_ACTIVE)->orderBy('name')->get(),
            'paymentMethods' => $this->paymentMethods(),
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    public function updateTransaction(Request $request, FinanceTransaction $transaction): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, User::managementRoles(), true), 403);

        $validated = $request->validate([
            'type' => ['required', Rule::in(['income', 'expense'])],
            'finance_category_id' => ['required', 'exists:finance_categories,id'],
            'finance_donor_id' => ['nullable', 'exists:finance_donors,id'],
            'finance_project_id' => ['nullable', 'exists:finance_projects,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'transaction_date' => ['required', 'date'],
            'payer_name' => ['nullable', 'string', 'max:180'],
            'payee_name' => ['nullable', 'string', 'max:180'],
            'source_or_payee' => ['nullable', 'string', 'max:180'],
            'receipt_number' => ['nullable', 'string', 'max:80', Rule::unique('finance_transactions', 'receipt_number')->ignore($transaction)],
            'payment_method' => ['required', Rule::in(array_keys($this->paymentMethods()))],
            'status' => ['nullable', Rule::in(array_keys($this->statusLabels()))],
            'attachment_required' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string', 'max:1500'],
            'attachment' => SecurityRules::financeAttachment(),
        ]);

        $category = FinanceCategory::query()
            ->whereKey($validated['finance_category_id'])
            ->where('type', $validated['type'])
            ->where('is_active', true)
            ->first();

        if (
            ! $category
            || ($validated['type'] === 'income' && ! $this->incomeCategoriesQuery()->whereKey($category->id)->exists())
            || ($validated['type'] === 'expense' && ! $this->expenseCategoriesQuery()->whereKey($category->id)->exists())
        ) {
            throw ValidationException::withMessages([
                'finance_category_id' => 'دسته‌بندی مالی انتخاب‌شده معتبر نیست.',
            ]);
        }

        $oldValues = $transaction->fresh()->toArray();
        $validated['status'] = $this->calculatedStatus(
            (int) ($transaction->expected_amount ?? $validated['amount']),
            (int) $validated['amount'],
            $validated['status'] ?? null
        );
        $validated['attachment_required'] = (bool) ($validated['attachment_required'] ?? false);
        $validated['receipt_number'] = $validated['receipt_number'] ?: $transaction->receipt_number ?: $this->nextTransactionNumber($validated['type']);
        $validated['expected_amount'] = $validated['amount'];
        $validated['dorm_student_id'] = null;
        $validated['payment_month'] = null;
        $validated['payment_year'] = null;
        unset($validated['attachment']);

        $transaction->update($validated);

        if ($request->hasFile('attachment')) {
            $this->storeAttachment($transaction, $request);
        }

        FinanceAuditLog::create([
            'finance_transaction_id' => $transaction->id,
            'action' => 'updated',
            'old_values' => $oldValues,
            'new_values' => $transaction->fresh()->toArray(),
            'performed_by' => $request->user()->id,
        ]);
        Audit::record('finance_transaction_updated', $transaction, $oldValues, $transaction->fresh()->only(['transaction_number', 'type', 'amount', 'finance_category_id', 'payment_method', 'status', 'recorded_by']), $request);

        return redirect()->route('admin.finance.index')->with('status', 'ثبت مالی موفقانه ویرایش شد.');
    }

    public function report(Request $request): View
    {
        $filters = $this->validatedFilters($request);
        $query = $this->ledgerTransactionsQuery()->with(['category', 'donor', 'project', 'recordedBy', 'attachments']);
        $this->applyFilters($query, $filters);
        $transactions = $query->orderBy('transaction_date')->get();

        return view('admin.finance.report', [
            'filters' => $filters,
            'transactions' => $transactions,
            'incomeTotal' => (int) $transactions->where('type', 'income')->sum('amount'),
            'expenseTotal' => (int) $transactions->where('type', 'expense')->sum('amount'),
            'paymentMethods' => $this->paymentMethods(),
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    public function export(Request $request): Response
    {
        $filters = $this->validatedFilters($request);
        $query = $this->ledgerTransactionsQuery()->with(['category', 'donor', 'project', 'recordedBy', 'attachments']);
        $this->applyFilters($query, $filters);

        $rows = [[
            'date',
            'type',
            'category',
            'donor',
            'project',
            'payer',
            'payee',
            'paid_amount',
            'remaining_amount',
            'receipt_number',
            'payment_method',
            'status',
            'has_attachment',
            'recorded_by',
            'description',
        ]];

        foreach ($query->orderBy('transaction_date')->get() as $transaction) {
            $rows[] = [
                $transaction->transaction_date?->format('Y-m-d'),
                $transaction->type,
                $transaction->category?->name,
                $transaction->donor?->name,
                $transaction->project?->name,
                $transaction->payer_name,
                $transaction->payee_name,
                $transaction->amount,
                $transaction->remainingAmount(),
                $transaction->receipt_number,
                $transaction->payment_method,
                $transaction->status,
                $transaction->attachments->isNotEmpty() ? 'yes' : 'no',
                $transaction->recordedBy?->name,
                $transaction->description ?: $transaction->notes,
            ];
        }

        $content = collect($rows)->map(fn (array $row) => collect($row)
            ->map(fn ($value) => '"'.str_replace('"', '""', (string) $value).'"')
            ->implode(','))
            ->implode("\n");

        return response($content, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="fanous-finance-report.csv"',
        ]);
    }

    public function destroyTransaction(Request $request, FinanceTransaction $transaction): RedirectResponse
    {
        abort_unless(in_array($request->user()->role, User::managementRoles(), true), 403);

        FinanceAuditLog::create([
            'finance_transaction_id' => $transaction->id,
            'action' => 'deleted',
            'old_values' => $transaction->load(['studentPayment'])->toArray(),
            'performed_by' => $request->user()->id,
        ]);
        Audit::record('finance_transaction_deleted', $transaction, $transaction->only(['transaction_number', 'type', 'amount', 'finance_category_id', 'payment_method', 'status', 'recorded_by']), [], $request);

        $transaction->studentPayment()?->delete();
        $transaction->delete();

        return back()->with('status', 'ثبت مالی حذف شد و سابقه آن ذخیره گردید.');
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', Rule::in(['income', 'expense'])],
            'category' => ['nullable', 'integer', 'exists:finance_categories,id'],
            'payment_method' => ['nullable', Rule::in(array_keys($this->paymentMethods()))],
            'recorded_by' => ['nullable', 'integer', 'exists:users,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);
    }

    private function ledgerTransactionsQuery()
    {
        return FinanceTransaction::query()
            ->whereDoesntHave('category', fn ($query) => $query->where('name', 'like', 'کتابخانه -%'))
            ->where(function ($query) {
                $query
                    ->where('type', '!=', 'income')
                    ->orWhere(function ($incomeQuery) {
                        $incomeQuery
                            ->whereNull('dorm_student_id')
                            ->whereDoesntHave('category', function ($categoryQuery) {
                                $categoryQuery
                                    ->where('name', 'like', '%فیس%')
                                    ->orWhere('name', 'like', '%شاگرد%')
                                    ->orWhere('name', 'like', '%محصل%')
                                    ->orWhere('name', 'like', '%ثبت نام%')
                                    ->orWhere('name', 'like', '%ثبت‌نام%')
                                    ->orWhere('name', 'like', '%ضمانت%');
                            });
                    });
            });
    }

    private function applyFilters($query, array $filters): void
    {
        $query
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('finance_category_id', $category))
            ->when($filters['payment_method'] ?? null, fn ($query, $method) => $query->where('payment_method', $method))
            ->when($filters['recorded_by'] ?? null, fn ($query, $userId) => $query->where('recorded_by', $userId))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('transaction_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('transaction_date', '<=', $date))
            ->when($filters['q'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('payer_name', 'like', "%{$search}%")
                        ->orWhere('payee_name', 'like', "%{$search}%")
                        ->orWhere('source_or_payee', 'like', "%{$search}%")
                        ->orWhere('transaction_number', 'like', "%{$search}%")
                        ->orWhere('receipt_number', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('donor', fn ($donorQuery) => $donorQuery->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"))
                        ->orWhereHas('project', fn ($projectQuery) => $projectQuery->where('name', 'like', "%{$search}%"));
                });
            });
    }

    private function periodReports(): array
    {
        $periods = [
            'daily' => [
                'label' => 'روزانه',
                'caption' => 'امروز',
                'start' => today()->toDateString(),
                'end' => today()->toDateString(),
            ],
            'weekly' => [
                'label' => 'هفته‌وار',
                'caption' => 'هفته جاری',
                'start' => now()->startOfWeek()->toDateString(),
                'end' => now()->endOfWeek()->toDateString(),
            ],
            'monthly' => [
                'label' => 'ماهانه',
                'caption' => 'ماه جاری',
                'start' => now()->startOfMonth()->toDateString(),
                'end' => now()->endOfMonth()->toDateString(),
            ],
            'yearly' => [
                'label' => 'سالانه',
                'caption' => 'سال جاری',
                'start' => now()->startOfYear()->toDateString(),
                'end' => now()->endOfYear()->toDateString(),
            ],
        ];

        foreach ($periods as $key => $period) {
            $income = (int) $this->ledgerTransactionsQuery()
                ->where('type', 'income')
                ->whereBetween('transaction_date', [$period['start'], $period['end']])
                ->sum('amount');
            $expense = (int) $this->ledgerTransactionsQuery()
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [$period['start'], $period['end']])
                ->sum('amount');
            $registrationRevenue = $this->registrationRevenueBetween($period['start'], $period['end']);
            $libraryIncome = (int) $this->libraryFinanceTransactionsQuery()
                ->where('type', 'income')
                ->whereBetween('transaction_date', [$period['start'], $period['end']])
                ->sum('amount');

            $periods[$key]['income'] = $income + $registrationRevenue + $libraryIncome;
            $periods[$key]['expense'] = $expense;
            $periods[$key]['balance'] = $periods[$key]['income'] - $expense;
            $periods[$key]['library_income'] = $libraryIncome;
            $periods[$key]['registration_income'] = $registrationRevenue;
        }

        return $periods;
    }

    private function registrationRevenueBetween(string $startDate, string $endDate): int
    {
        return (int) DormStudent::query()
            ->whereIn('registration_payment_status', ['paid', 'partial'])
            ->whereNotIn('status', ['waiting', 'on_hold', 'rejected'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query
                    ->whereBetween('registration_paid_at', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query
                            ->whereNull('registration_paid_at')
                            ->whereBetween('created_at', [$startDate.' 00:00:00', $endDate.' 23:59:59']);
                    });
            })
            ->get()
            ->sum(fn (DormStudent $student) => (int) ($student->dorm_expense_fee_amount ?? 1000));
    }

    private function libraryFinanceTransactionsQuery()
    {
        return FinanceTransaction::query()
            ->whereHas('category', fn ($query) => $query->where('name', 'like', 'کتابخانه -%'));
    }

    private function storeAttachment(FinanceTransaction $transaction, Request $request): void
    {
        $file = $request->file('attachment');
        $path = $file->store('finance-attachments', 'local');

        FinanceAttachment::create([
            'finance_transaction_id' => $transaction->id,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => $request->user()->id,
        ]);
    }

    private function incomeCategoriesQuery()
    {
        return FinanceCategory::query()
            ->where('type', 'income')
            ->where('is_active', true)
            ->where(function ($query) {
                $query
                    ->where('name', 'like', '%کمک%')
                    ->orWhere('name', 'like', '%خیری%')
                    ->orWhere('name', 'like', '%موسسه%')
                    ->orWhere('name', 'like', '%مؤسسه%')
                    ->orWhere('name', 'like', '%درآمدهای دیگر%')
                    ->orWhere('name', 'like', '%درآمد متفرقه%');
            })
            ->orderBy('name');
    }

    private function expenseCategoriesQuery()
    {
        return FinanceCategory::query()
            ->where('type', 'expense')
            ->where('is_active', true)
            ->where(function ($query) {
                $query
                    ->where('name', 'like', '%ساخت%')
                    ->orWhere('name', 'like', '%معاش%')
                    ->orWhere('name', 'like', '%ترمیم%')
                    ->orWhere('name', 'like', '%خرید%')
                    ->orWhere('name', 'like', '%وسایل%')
                    ->orWhere('name', 'like', '%مصارف دیگر%');
            })
            ->orderBy('name');
    }

    private function calculatedStatus(int $expected, int $paid, ?string $requested): string
    {
        if ($paid >= $expected) {
            return 'paid';
        }

        if ($paid > 0) {
            return 'partial';
        }

        return $requested ?: 'pending';
    }

    private function nextTransactionNumber(string $type): string
    {
        $prefix = $type === 'expense' ? 'EXP' : 'INC';
        $date = now()->format('Ymd');
        $count = FinanceTransaction::withTrashed()->whereDate('created_at', now()->toDateString())->count() + 1;

        return $prefix.'-'.$date.'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    private function paymentMethods(): array
    {
        return [
            'cash' => 'نقد',
            'bank' => 'بانک',
            'hawala' => 'حواله',
            'card' => 'کارت',
            'other' => 'سایر',
        ];
    }

    private function statusLabels(): array
    {
        return [
            'paid' => 'پرداخت شده',
            'partial' => 'نیمه پرداخت',
            'pending' => 'بدهکار',
        ];
    }

    private function projectStatuses(): array
    {
        return [
            'active' => 'فعال',
            'paused' => 'متوقف',
            'completed' => 'تکمیل شده',
            'cancelled' => 'لغو شده',
        ];
    }
}
