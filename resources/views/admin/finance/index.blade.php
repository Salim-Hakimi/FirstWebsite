@extends('admin.layout')

@section('title', 'مالی - ادمین فانوس')
@section('content_wrapper_class', 'fanous-dashboard-wrapper')

@section('content')
    @php
        use App\Support\Locale;

        $typeLabels = ['income' => 'درآمد', 'expense' => 'مصرف'];
        $balance = $totalIncome - $totalExpense;
        $monthBalance = $monthIncome - $monthExpense;
        $maxChart = max(1, (int) $monthlyChart->max(fn ($row) => max((int) $row->income_total, (int) $row->expense_total)));

        $statusTone = [
            'paid' => 'success',
            'partial' => 'warning',
            'pending' => 'danger',
        ];
        $quickFilters = [
            'today' => [
                'label' => 'امروز',
                'from' => today()->toDateString(),
                'to' => today()->toDateString(),
            ],
            'week' => [
                'label' => 'این هفته',
                'from' => now()->startOfWeek()->toDateString(),
                'to' => now()->endOfWeek()->toDateString(),
            ],
            'month' => [
                'label' => 'این ماه',
                'from' => now()->startOfMonth()->toDateString(),
                'to' => now()->endOfMonth()->toDateString(),
            ],
            'year' => [
                'label' => 'امسال',
                'from' => now()->startOfYear()->toDateString(),
                'to' => now()->endOfYear()->toDateString(),
            ],
        ];

        /*
        $unusedCategorySummaries = $transactions
            ->groupBy(fn ($transaction) => $transaction->category?->name ?: ($transaction->type === 'income' ? 'درآمد بدون دسته' : 'مصرف بدون دسته'))
            ->map(function ($rows, $name) {
                $first = $rows->first();

                return [
                    'name' => $name,
                    'type' => $first->type,
                    'total' => (int) $rows->sum('amount'),
                    'count' => $rows->count(),
                ];
            })
            ->sortByDesc('total')
            ->take(5);
        */

    @endphp

    <div class="fanous-finance-page" id="finance-page-top" dir="rtl">
        <section class="fanous-page-header fanous-finance-hero">
            <div>
                <span class="dashboard-section-kicker">دفتر مالی</span>
                <h1>مالی</h1>
                <p>کمک‌ها، درآمدهای عمومی، مصارف لیلیه، معاش کارمندان و گزارش‌های مالی را از همین صفحه مدیریت کنید.</p>
            </div>

            <div class="fanous-page-actions">
                <x-ds.button variant="outline" :href="route('admin.finance.export', $filters)">خروجی Excel</x-ds.button>
                <x-ds.button variant="outline" :href="route('admin.finance.report', $filters)">گزارش مالی</x-ds.button>
                <x-ds.button href="#finance-income-modal">
                    <x-ds.icon name="plus" />
                    ثبت کمک / درآمد
                </x-ds.button>
            </div>
        </section>

        <section class="fanous-finance-summary">
            <article class="dashboard-stat fanous-finance-stat">
                <div>
                    <span>دریافت ماه جاری</span>
                    <strong>{{ Locale::money($monthIncome) }}</strong>
                    <small>درآمد ثبت‌شده در ماه جاری</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="cash" /></span>
            </article>

            <article class="dashboard-stat fanous-finance-stat">
                <div>
                    <span>مصارف ماه جاری</span>
                    <strong>{{ Locale::money($monthExpense) }}</strong>
                    <small>مصارف ثبت‌شده در همین ماه</small>
                </div>
                <span class="dashboard-stat-icon is-danger"><x-ds.icon name="cash-minus" /></span>
            </article>

            <article class="dashboard-stat fanous-finance-stat">
                <div>
                    <span>باقی‌مانده افغانی</span>
                    <strong>{{ Locale::money($balance) }}</strong>
                    <small>کل دریافت منهای کل مصرف</small>
                </div>
                <span class="dashboard-stat-icon is-blue"><x-ds.icon name="chart" /></span>
            </article>

            <article class="dashboard-stat fanous-finance-stat">
                <div>
                    <span>درآمد ثبت‌نام</span>
                    <strong>{{ Locale::money($monthRegistrationRevenue) }}</strong>
                    <small>فقط مصارف لیلیه؛ ضمانت شامل درآمد نیست</small>
                </div>
                <span class="dashboard-stat-icon is-purple"><x-ds.icon name="cash" /></span>
            </article>

            <article class="dashboard-stat fanous-finance-stat">
                <div>
                    <span>درآمد کتابخانه</span>
                    <strong>{{ Locale::money($libraryIncomeTotal) }}</strong>
                    <small>درآمد امروز: {{ Locale::money($libraryTodayIncome) }} · مصرف امروز: {{ Locale::money($libraryTodayExpense) }} · مصرف کل: {{ Locale::money($libraryExpenseTotal) }}</small>
                </div>
                <span class="dashboard-stat-icon is-blue"><x-ds.icon name="cash" /></span>
            </article>

            <article class="dashboard-stat fanous-finance-stat">
                <div>
                    <span>خالص ماه جاری</span>
                    <strong>{{ Locale::money($monthBalance) }}</strong>
                    <small>دریافت ماه جاری منهای مصارف همین ماه؛ شامل درآمد ثبت‌نام و کتابخانه</small>
                </div>
                <span class="dashboard-stat-icon is-green"><x-ds.icon name="chart" /></span>
            </article>
        </section>

        <section class="dashboard-panel fanous-period-report-card">
            <div class="dashboard-panel-header">
                <div>
                    <span class="dashboard-section-kicker">گزارش مالی</span>
                    <h2>درآمد و مصرف روزانه، هفته‌وار، ماهانه و سالانه</h2>
                    <p>درآمد ثبت‌نام شاگردان و درآمد/مصرف کتابخانه در جمع کل سیستم حساب می‌شود تا باقی‌مانده اشتباه نشود.</p>
                </div>
            </div>

            <div class="fanous-period-report-grid">
                @foreach ($periodReports as $period)
                    <article class="fanous-period-report-item">
                        <div class="fanous-period-report-head">
                            <span>{{ $period['label'] }}</span>
                            <small>{{ $period['caption'] }}</small>
                        </div>

                        <div class="fanous-period-report-values">
                            <div class="is-income">
                                <span>درآمد</span>
                                <strong>{{ Locale::money((int) $period['income']) }}</strong>
                            </div>
                            <div class="is-expense">
                                <span>مصرف</span>
                                <strong>{{ Locale::money((int) $period['expense']) }}</strong>
                            </div>
                            <div>
                                <span>باقی‌مانده</span>
                                <strong>{{ Locale::money((int) $period['balance']) }}</strong>
                            </div>
                        </div>

                        <footer>
                            <span>کتابخانه: {{ Locale::money((int) $period['library_income']) }} / {{ Locale::money((int) $period['library_expense']) }}</span>
                            <span>ثبت‌نام: {{ Locale::money((int) $period['registration_income']) }}</span>
                        </footer>
                    </article>
                @endforeach
            </div>

            <div class="fanous-document-alert {{ $missingDocuments->isNotEmpty() ? 'is-warning' : 'is-complete' }}">
                <span>{{ Locale::number($missingDocuments->count()) }}</span>
                <div>
                    <strong>اسناد ناقص</strong>
                    <p>
                        @if ($missingDocuments->isNotEmpty())
                            {{ Locale::number($missingDocuments->count()) }} ثبت مالی سند لازم دارد اما هنوز ضمیمه نشده است.
                        @else
                            همه ثبت‌های نیازمند سند تکمیل شده‌اند.
                        @endif
                    </p>
                </div>
                @if ($missingDocuments->isNotEmpty())
                    <a href="#finance-missing-documents">دیدن موارد</a>
                @endif
            </div>
        </section>

        <section class="fanous-finance-layout">
            <div class="fanous-finance-main">
                <article class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">جستجو و فیلتر</span>
                            <h2>فیلتر ثبت‌های مالی</h2>
                            <p>ثبت‌ها را بر اساس تاریخ، نوع پرداخت، دسته‌بندی، روش پرداخت یا ثبت‌کننده محدود کنید.</p>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('admin.finance.index') }}" class="fanous-filter-grid fanous-finance-filter-grid">
                        <div class="fanous-quick-filters">
                            @foreach ($quickFilters as $quickFilter)
                                @php
                                    $isActiveQuickFilter = ($filters['date_from'] ?? '') === $quickFilter['from']
                                        && ($filters['date_to'] ?? '') === $quickFilter['to'];
                                    $quickFilterParams = array_filter(array_merge($filters, [
                                        'date_from' => $quickFilter['from'],
                                        'date_to' => $quickFilter['to'],
                                    ]), fn ($value) => filled($value));
                                @endphp
                                <a class="{{ $isActiveQuickFilter ? 'is-active' : '' }}" href="{{ route('admin.finance.index', $quickFilterParams) }}">{{ $quickFilter['label'] }}</a>
                            @endforeach
                        </div>

                        <label>
                            <span>جستجو</span>
                            <input class="form-control" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="نام شخص، رسید، پروژه یا توضیحات">
                        </label>

                        <label>
                            <span>نوع</span>
                            <select class="form-control" name="type">
                                <option value="">همه نوع‌ها</option>
                                @foreach ($typeLabels as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label>
                            <span>دسته‌بندی</span>
                            <select class="form-control" name="category">
                                <option value="">همه دسته‌ها</option>
                                @foreach ($allCategories as $category)
                                    <option value="{{ $category->id }}" @selected((string) ($filters['category'] ?? '') === (string) $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label>
                            <span>روش پرداخت</span>
                            <select class="form-control" name="payment_method">
                                <option value="">همه روش‌ها</option>
                                @foreach ($paymentMethods as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['payment_method'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label>
                            <span>از تاریخ</span>
                            <input class="form-control" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}">
                        </label>

                        <label>
                            <span>تا تاریخ</span>
                            <input class="form-control" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}">
                        </label>

                        <label>
                            <span>ثبت‌کننده</span>
                            <select class="form-control" name="recorded_by">
                                <option value="">همه ثبت‌کنندگان</option>
                                @foreach ($recorders as $user)
                                    <option value="{{ $user->id }}" @selected((string) ($filters['recorded_by'] ?? '') === (string) $user->id)>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </label>

                        <div class="fanous-filter-actions">
                            <x-ds.button type="submit">جستجو</x-ds.button>
                            <x-ds.button variant="outline" :href="route('admin.finance.index')">پاک کردن</x-ds.button>
                            <x-ds.button variant="outline" :href="route('admin.finance.report', $filters)">گزارش</x-ds.button>
                        </div>
                    </form>
                </article>

                <article class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">ثبت‌های مالی</span>
                            <h2>آخرین ثبت‌های مالی</h2>
                            <p>پرداخت‌ها و مصارف اخیر سیستم در این جدول نمایش داده می‌شود.</p>
                        </div>

                        <div class="fanous-page-actions">
                            <x-ds.button href="#finance-income-modal" size="sm">ثبت درآمد</x-ds.button>
                            <x-ds.button href="#finance-expense-modal" variant="outline" size="sm">ثبت مصرف</x-ds.button>
                        </div>
                    </div>

                    <div class="fanous-table-wrap">
                        <table class="fanous-finance-table">
                            <thead>
                                <tr>
                                    <th>تاریخ</th>
                                    <th>شخص / منبع</th>
                                    <th>نوع</th>
                                    <th>رسید</th>
                                    <th>مبلغ</th>
                                    <th>ثبت‌کننده</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($transactions as $transaction)
                                    @php
                                        $tone = $transaction->type === 'income' ? 'success' : 'danger';
                                        $status = $statusLabels[$transaction->status] ?? $transaction->status;
                                        $statusBadgeTone = $statusTone[$transaction->status] ?? 'primary';
                                    @endphp
                                    <tr>
                                        <td>{{ $transaction->transaction_date ? Locale::number($transaction->transaction_date->format('Y/m/d')) : 'ثبت نشده' }}</td>
                                        <td>
                                            <strong>{{ $transaction->displayPerson() }}</strong>
                                            <small>{{ $transaction->category?->name ?: 'بدون دسته' }}</small>
                                        </td>
                                        <td>
                                            <x-ds.badge :tone="$tone">{{ $typeLabels[$transaction->type] ?? $transaction->type }}</x-ds.badge>
                                            <x-ds.badge :tone="$statusBadgeTone">{{ $status }}</x-ds.badge>
                                        </td>
                                        <td><span class="ltr-text">{{ $transaction->receipt_number ?: $transaction->transaction_number }}</span></td>
                                        <td><strong>{{ Locale::money((int) $transaction->amount) }}</strong></td>
                                        <td>{{ $transaction->recordedBy?->name ?: 'سیستم' }}</td>
                                        <td>
                                            <div class="fanous-row-actions">
                                                <x-ds.button variant="outline" size="sm" :href="route('admin.finance.transactions.edit', $transaction)">ویرایش</x-ds.button>
                                                <x-ds.button variant="outline" size="sm" :href="route('admin.finance.transactions.receipt', $transaction)">رسید</x-ds.button>
                                                <form method="POST" action="{{ route('admin.finance.transactions.destroy', $transaction) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <x-ds.button variant="danger" size="sm" type="submit">حذف</x-ds.button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7"><div class="dashboard-empty">هیچ ثبت مالی پیدا نشد.</div></td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="dashboard-panel" id="finance-missing-documents">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">اسناد ناقص</span>
                            <h2>ثبت‌های نیازمند سند</h2>
                            <p>ثبت‌هایی که سند لازم دارند اما هنوز ضمیمه نشده‌اند.</p>
                        </div>
                    </div>

                    <div class="fanous-missing-document-list">
                        @forelse ($missingDocuments as $documentTransaction)
                            <div>
                                <span class="fanous-finance-small-icon is-expense"><x-ds.icon name="cash-minus" /></span>
                                <p>
                                    <strong>{{ $documentTransaction->displayPerson() }}</strong>
                                    <small>{{ $documentTransaction->category?->name ?: 'بدون دسته' }} · {{ $documentTransaction->transaction_date ? Locale::number($documentTransaction->transaction_date->format('Y/m/d')) : 'تاریخ ندارد' }}</small>
                                </p>
                                <div class="fanous-row-actions">
                                    <b>{{ Locale::money((int) $documentTransaction->amount) }}</b>
                                    <x-ds.button variant="outline" size="sm" :href="route('admin.finance.transactions.edit', $documentTransaction)">تکمیل سند</x-ds.button>
                                </div>
                            </div>
                        @empty
                            <div class="dashboard-empty">فعلاً هیچ ثبت مالی با سند ناقص وجود ندارد.</div>
                        @endforelse
                    </div>
                </article>
            </div>

            <aside class="fanous-finance-sidebar">
                <article class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">نوع‌های مالی</span>
                            <h2>جمع‌بندی دریافت و مصرف</h2>
                            <p>بیشترین دسته‌های مالی در ثبت‌های اخیر.</p>
                        </div>
                    </div>

                    <div class="fanous-finance-type-list">
                        @forelse ($categorySummaries as $summary)
                            @php
                                $summaryCategory = $summary->category;
                                $summaryFilters = array_filter(array_merge($filters, [
                                    'type' => $summary->type,
                                    'category' => $summary->finance_category_id,
                                ]), fn ($value) => filled($value));
                                $isActiveSummary = (string) ($filters['category'] ?? '') === (string) $summary->finance_category_id;
                            @endphp
                            <a class="fanous-finance-type-item {{ $isActiveSummary ? 'is-active' : '' }}" href="{{ route('admin.finance.index', $summaryFilters) }}">
                                <div class="fanous-finance-type-main">
                                    <span class="fanous-finance-small-icon {{ $summary->type === 'income' ? 'is-income' : 'is-expense' }}">
                                        <x-ds.icon :name="$summary->type === 'income' ? 'cash' : 'cash-minus'" />
                                    </span>
                                    <div>
                                        <strong>{{ $summaryCategory?->name ?: ($summary->type === 'income' ? 'درآمد بدون دسته' : 'مصرف بدون دسته') }}</strong>
                                        <span>{{ Locale::number((int) $summary->records_count) }} ثبت ذخیره شده</span>
                                    </div>
                                </div>
                                <b>{{ Locale::money((int) $summary->total_amount) }}</b>
                            </a>
                        @empty
                            <div class="dashboard-empty">هنوز دسته مالی برای نمایش وجود ندارد.</div>
                        @endforelse
                    </div>
                </article>

                <article class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">فعالیت مالی</span>
                            <h2>ثبت فعالیت مالی</h2>
                        <p>درآمد عمومی، کمک‌ها، معاش کارمندان، خرید، ترمیم و سایر مصارف لیلیه را از همین بخش ثبت کنید.</p>
                        </div>
                    </div>

                    <div class="fanous-finance-info">
                        <span>i</span>
                        <p>ثبت‌های مالی باید با تاریخ، مبلغ و رسید دقیق ذخیره شوند تا گزارش‌ها درست ساخته شوند.</p>
                    </div>

                    <div class="fanous-finance-side-actions">
                        <x-ds.button href="#finance-income-modal">ثبت کمک / درآمد</x-ds.button>
                        <x-ds.button variant="outline" href="#finance-expense-modal">ثبت مصرف</x-ds.button>
                    </div>
                </article>

                <article class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">خلاصه ماهانه</span>
                            <h2>درآمد و مصرف</h2>
                        </div>
                    </div>

                    <div class="fanous-chart-list">
                        @forelse ($monthlyChart as $row)
                            <div class="fanous-chart-row">
                                <div>
                                    <strong class="ltr-text">{{ $row->month_key }}</strong>
                                    <span>{{ Locale::money((int) $row->income_total) }} / {{ Locale::money((int) $row->expense_total) }}</span>
                                </div>
                                <div class="fanous-chart-bars">
                                    <span class="is-income" style="width: {{ ((int) $row->income_total / $maxChart) * 100 }}%"></span>
                                    <span class="is-expense" style="width: {{ ((int) $row->expense_total / $maxChart) * 100 }}%"></span>
                                </div>
                            </div>
                        @empty
                            <div class="dashboard-empty">هنوز معلومات کافی برای نمودار وجود ندارد.</div>
                        @endforelse
                    </div>
                </article>

                <article class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">پروژه‌ها</span>
                            <h2>ثبت و خلاصه پروژه</h2>
                        </div>
                    </div>

                    <details class="fanous-project-details">
                        <summary>ثبت پروژه جدید</summary>
                        <form method="POST" action="{{ route('admin.finance.projects.store') }}" class="fanous-project-form">
                            @csrf
                            <input class="form-control" name="name" placeholder="نام پروژه" required>
                            <input class="form-control" name="category" placeholder="نوع پروژه">
                            <input class="form-control" name="estimated_budget" type="number" min="0" placeholder="بودجه تخمینی">
                            <select class="form-control" name="status">
                                @foreach ($projectStatuses as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <input class="form-control" name="started_on" type="date">
                            <input class="form-control" name="completed_on" type="date">
                            <textarea class="form-control fanous-form-wide" name="notes" rows="2" placeholder="یادداشت"></textarea>
                            <x-ds.button type="submit">ثبت پروژه</x-ds.button>
                        </form>
                    </details>

                    <div class="fanous-follow-list">
                        @forelse ($projects->take(4) as $project)
                            @php
                                $spent = (int) $project->spent_total;
                                $remaining = max(0, (int) $project->estimated_budget - $spent);
                            @endphp
                            <div class="fanous-follow-row">
                                <strong>{{ $project->name }}</strong>
                                <span>بودجه: {{ Locale::money((int) $project->estimated_budget) }} · مصرف: {{ Locale::money($spent) }} · باقی: {{ Locale::money($remaining) }}</span>
                            </div>
                        @empty
                            <div class="dashboard-empty">هنوز پروژه ثبت نشده است.</div>
                        @endforelse
                    </div>
                </article>
            </aside>
        </section>

        <section class="fanous-finance-modals" aria-label="فرم‌های مالی">
            <article class="dashboard-panel fanous-finance-modal" id="finance-income-modal">
                <div class="dashboard-panel-header">
                    <div>
                        <span class="dashboard-section-kicker">ثبت درآمد</span>
                        <h2>ثبت کمک یا درآمد عمومی لیلیه</h2>
                        <p>فیس شاگردان از این فورم ثبت نمی‌شود. ضمانت درآمد نیست؛ فقط کمک‌ها و درآمدهای عمومی را این‌جا ثبت کنید.</p>
                    </div>
                    <x-ds.button variant="outline" size="sm" href="#finance-page-top">بستن</x-ds.button>
                </div>

                <form method="POST" action="{{ route('admin.finance.transactions.store') }}" enctype="multipart/form-data" class="fanous-finance-form">
                    @csrf
                    <input type="hidden" name="type" value="income">

                    <label>
                        <span>نوع درآمد</span>
                        <select class="form-control" name="finance_category_id" required>
                            <option value="">انتخاب نوع درآمد</option>
                            @foreach ($incomeCategories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        <span>مبلغ دریافتی</span>
                        <input class="form-control" name="amount" type="number" min="1" required>
                    </label>

                    <label>
                        <span>تاریخ دریافت</span>
                        <input class="form-control" name="transaction_date" type="date" value="{{ now()->format('Y-m-d') }}" required>
                    </label>

                    <label>
                        <span>روش دریافت</span>
                        <select class="form-control" name="payment_method">
                            @foreach ($paymentMethods as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        <span>کمک‌کننده / منبع درآمد</span>
                        <input class="form-control" name="payer_name" placeholder="نام شخص، موسسه یا منبع درآمد">
                    </label>

                    <label>
                        <span>شماره رسید</span>
                        <input class="form-control ltr-text" name="receipt_number" placeholder="در صورت خالی بودن خودکار ساخته می‌شود">
                    </label>

                    <label class="fanous-file-upload">
                        <span>ضمیمه سند</span>
                        <input class="fanous-file-input" name="attachment" type="file">
                        <span class="fanous-file-control">
                            <b>انتخاب سند</b>
                            <small>PDF، عکس یا فایل مجاز را انتخاب کنید.</small>
                        </span>
                    </label>

                    <label>
                        <span>ضرورت سند</span>
                        <select class="form-control" name="attachment_required">
                            <option value="0">ضروری نیست</option>
                            <option value="1">سند لازم دارد</option>
                        </select>
                    </label>

                    <label class="fanous-form-wide">
                        <span>توضیحات</span>
                        <textarea class="form-control" name="description" rows="3" placeholder="جزئیات کمک، منبع درآمد یا یادداشت مالی"></textarea>
                    </label>

                    <div class="fanous-form-actions">
                        <x-ds.button variant="outline" href="#finance-page-top">لغو</x-ds.button>
                        <x-ds.button type="submit">ذخیره درآمد و رسید</x-ds.button>
                    </div>
                </form>
            </article>

            <article class="dashboard-panel fanous-finance-modal" id="finance-expense-modal">
                <div class="dashboard-panel-header">
                    <div>
                        <span class="dashboard-section-kicker">ثبت مصرف</span>
                        <h2>ثبت مصرف لیلیه</h2>
                        <p>برای ساخت‌وساز، معاش، ترمیم، خریداری و سایر مصارف لیلیه از این فرم استفاده کنید.</p>
                    </div>
                    <x-ds.button variant="outline" size="sm" href="#finance-page-top">بستن</x-ds.button>
                </div>

                <form method="POST" action="{{ route('admin.finance.transactions.store') }}" enctype="multipart/form-data" class="fanous-finance-form">
                    @csrf
                    <input type="hidden" name="type" value="expense">

                    <label>
                        <span>عنوان مصرف</span>
                        <input class="form-control" name="payee_name" list="finance-staff-payees" placeholder="نام کارمند، فروشنده یا عنوان مصرف">
                        <datalist id="finance-staff-payees">
                            @foreach ($staffUsers as $staffUser)
                                <option value="{{ $staffUser->name }}">{{ $staffUser->role }}</option>
                            @endforeach
                        </datalist>
                    </label>

                    <label>
                        <span>دسته‌بندی مصرف</span>
                        <select class="form-control" name="finance_category_id" required>
                            <option value="">انتخاب دسته مصرف</option>
                            @foreach ($expenseCategories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        <span>پروژه / کار</span>
                        <select class="form-control" name="finance_project_id">
                            <option value="">بدون پروژه</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        <span>مبلغ</span>
                        <input class="form-control" name="amount" type="number" min="1" required>
                    </label>

                    <label>
                        <span>تاریخ</span>
                        <input class="form-control" name="transaction_date" type="date" value="{{ now()->format('Y-m-d') }}" required>
                    </label>

                    <label>
                        <span>روش پرداخت</span>
                        <select class="form-control" name="payment_method">
                            @foreach ($paymentMethods as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label>
                        <span>پرداخت‌کننده</span>
                        <input class="form-control" name="payer_name" placeholder="نام شخصی که پول را پرداخت کرده">
                    </label>

                    <label>
                        <span>شماره رسید</span>
                        <input class="form-control ltr-text" name="receipt_number" placeholder="در صورت خالی بودن خودکار ساخته می‌شود">
                    </label>

                    <label class="fanous-file-upload">
                        <span>ضمیمه سند</span>
                        <input class="fanous-file-input" name="attachment" type="file">
                        <span class="fanous-file-control">
                            <b>انتخاب سند</b>
                            <small>PDF، عکس یا فایل مجاز را انتخاب کنید.</small>
                        </span>
                    </label>

                    <label>
                        <span>ضرورت سند</span>
                        <select class="form-control" name="attachment_required">
                            <option value="0">ضروری نیست</option>
                            <option value="1">سند لازم دارد</option>
                        </select>
                    </label>

                    <label class="fanous-form-wide">
                        <span>توضیحات</span>
                        <textarea class="form-control" name="description" rows="3" placeholder="جزئیات مصرف، جنس خریداری‌شده یا محل استفاده"></textarea>
                    </label>

                    <div class="fanous-form-actions">
                        <x-ds.button variant="outline" href="#finance-page-top">لغو</x-ds.button>
                        <x-ds.button type="submit">ذخیره مصرف و رسید</x-ds.button>
                    </div>
                </form>
            </article>
        </section>
    </div>
@endsection
