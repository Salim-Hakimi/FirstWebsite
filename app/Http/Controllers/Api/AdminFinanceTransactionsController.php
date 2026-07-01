<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinanceCategory;
use App\Models\FinanceTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminFinanceTransactionsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', Rule::in(['income', 'expense'])],
            'category' => ['nullable', 'integer', 'exists:finance_categories,id'],
            'payment_method' => ['nullable', 'string', 'max:60'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:5', 'max:30'],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 10);

        $query = $this->ledgerTransactionsQuery()
            ->with(['category', 'recordedBy', 'donor', 'project']);

        $this->applyFilters($query, $validated);

        $transactions = $query
            ->latest('transaction_date')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'data' => $transactions->getCollection()
                ->map(fn (FinanceTransaction $transaction): array => $this->transactionPayload($transaction))
                ->values(),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
                'from' => $transactions->firstItem(),
                'to' => $transactions->lastItem(),
            ],
            'filters' => [
                'q' => $validated['q'] ?? '',
                'type' => $validated['type'] ?? '',
                'category' => $validated['category'] ?? '',
                'payment_method' => $validated['payment_method'] ?? '',
                'date_from' => $validated['date_from'] ?? '',
                'date_to' => $validated['date_to'] ?? '',
                'categories' => $this->categoryOptions(),
                'payment_methods' => $this->paymentMethodOptions(),
            ],
        ]);
    }

    private function ledgerTransactionsQuery()
    {
        return FinanceTransaction::query()
            ->whereDoesntHave('category', fn ($query) => $query->where('name', 'like', 'کتابخانه -%'))
            ->where(function ($query): void {
                $query
                    ->where('type', '!=', 'income')
                    ->orWhere(function ($incomeQuery): void {
                        $incomeQuery
                            ->whereNull('dorm_student_id')
                            ->whereDoesntHave('category', function ($categoryQuery): void {
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
            ->when($filters['type'] ?? null, fn ($query, string $type) => $query->where('type', $type))
            ->when($filters['category'] ?? null, fn ($query, int $category) => $query->where('finance_category_id', $category))
            ->when($filters['payment_method'] ?? null, fn ($query, string $method) => $query->where('payment_method', $method))
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('transaction_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('transaction_date', '<=', $date))
            ->when($filters['q'] ?? null, function ($query, string $search): void {
                $query->where(function ($query) use ($search): void {
                    $query
                        ->where('payer_name', 'like', "%{$search}%")
                        ->orWhere('payee_name', 'like', "%{$search}%")
                        ->orWhere('source_or_payee', 'like', "%{$search}%")
                        ->orWhere('transaction_number', 'like', "%{$search}%")
                        ->orWhere('receipt_number', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('donor', fn ($donorQuery) => $donorQuery->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"))
                        ->orWhereHas('project', fn ($projectQuery) => $projectQuery->where('name', 'like', "%{$search}%"));
                });
            });
    }

    private function transactionPayload(FinanceTransaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'type' => $transaction->type,
            'type_label' => $transaction->type === 'income' ? 'درآمد' : 'مصرف',
            'status' => $transaction->status,
            'status_label' => $this->statusLabels()[$transaction->status] ?? $transaction->status,
            'amount' => (int) $transaction->amount,
            'transaction_date' => $transaction->transaction_date?->toDateString(),
            'counterparty' => $transaction->displayPerson(),
            'category' => [
                'id' => $transaction->category?->id,
                'name' => $transaction->category?->name ?: 'بدون دسته',
                'type' => $transaction->category?->type,
            ],
            'receipt_number' => $transaction->receipt_number ?: $transaction->transaction_number,
            'payment_method' => $transaction->payment_method,
            'description' => $transaction->description,
            'recorded_by' => $transaction->recordedBy?->name ?: 'سیستم',
            'links' => [
                'edit' => route('admin.finance.transactions.edit', $transaction),
                'receipt' => route('admin.finance.transactions.receipt', $transaction),
            ],
        ];
    }

    private function categoryOptions(): array
    {
        return FinanceCategory::query()
            ->where('name', 'not like', 'کتابخانه -%')
            ->orderBy('type')
            ->orderBy('name')
            ->get(['id', 'name', 'type'])
            ->map(fn (FinanceCategory $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'type' => $category->type,
            ])
            ->values()
            ->all();
    }

    private function paymentMethodOptions(): array
    {
        return $this->ledgerTransactionsQuery()
            ->whereNotNull('payment_method')
            ->where('payment_method', '!=', '')
            ->distinct()
            ->orderBy('payment_method')
            ->pluck('payment_method')
            ->values()
            ->all();
    }

    private function statusLabels(): array
    {
        return [
            'paid' => 'پرداخت شده',
            'partial' => 'قسمتی',
            'pending' => 'در انتظار',
            'unpaid' => 'پرداخت نشده',
        ];
    }
}
