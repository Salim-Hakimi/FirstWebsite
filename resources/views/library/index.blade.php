@extends('admin.layout')

@section('title', 'مدیریت کتابخانه - ادمین فانوس')
@section('content_wrapper_class', 'fanous-dashboard-wrapper')

@section('content')
    @php
        use App\Support\Locale;

        $activeMemberCount = $activeMemberCount ?? $members->where('status', 'active')->count();
        $bookTitleCount = $bookTitleCount ?? $books->count();
        $activeLoanCount = $activeLoanCount ?? $loans->whereIn('status', ['borrowed', 'late'])->count();
        $availableCopyCount = $availableCopyCount ?? $books->sum('available_copies');
        $totalCopiesCount = (int) $books->sum('total_copies');
        $borrowedCopyCount = max(0, $totalCopiesCount - (int) $availableCopyCount);
        $damagedLostCount = (int) $books->whereIn('status', ['damaged', 'lost'])->count();
        $overdueLoans = $overdueLoans ?? collect();
        $followUpCount = $expiringMembers->count() + $expiredCards->count() + $overdueLoans->count();
        $hasMemberFilters = filled($filters['q'] ?? null) || filled($filters['status'] ?? null);
        $isLibraryFinancePage = request()->routeIs('library.finance.*');
        $financeTypeLabels = ['income' => 'درآمد', 'expense' => 'مصرف'];
        $libraryFinanceBalance = $libraryIncomeTotal - $libraryExpenseTotal;
        $libraryMonthBalance = $libraryMonthIncome - $libraryMonthExpense;
        $libraryQuickFilters = [
            'today' => ['label' => 'امروز', 'from' => today()->toDateString(), 'to' => today()->toDateString()],
            'week' => ['label' => 'این هفته', 'from' => now()->startOfWeek()->toDateString(), 'to' => now()->endOfWeek()->toDateString()],
            'month' => ['label' => 'این ماه', 'from' => now()->startOfMonth()->toDateString(), 'to' => now()->endOfMonth()->toDateString()],
            'year' => ['label' => 'امسال', 'from' => now()->startOfYear()->toDateString(), 'to' => now()->endOfYear()->toDateString()],
        ];

        $memberStatusMeta = [
            'active' => ['label' => 'فعال', 'tone' => 'success'],
            'suspended' => ['label' => 'مسدود', 'tone' => 'danger'],
            'left' => ['label' => 'خارج شده', 'tone' => 'warning'],
        ];
        $bookStatusMeta = [
            'available' => ['label' => 'موجود', 'tone' => 'success'],
            'damaged' => ['label' => 'خراب', 'tone' => 'warning'],
            'lost' => ['label' => 'گم‌شده', 'tone' => 'danger'],
            'archived' => ['label' => 'آرشیف', 'tone' => 'primary'],
        ];
        $loanStatusMeta = [
            'borrowed' => ['label' => 'امانت', 'tone' => 'warning'],
            'returned' => ['label' => 'برگشت‌شده', 'tone' => 'success'],
            'lost' => ['label' => 'گم‌شده', 'tone' => 'danger'],
            'late' => ['label' => 'دیرشده', 'tone' => 'danger'],
        ];
    @endphp

    <div class="fanous-library-page" dir="rtl">
        <section class="fanous-page-header">
            <div>
                <span class="dashboard-section-kicker">کتابخانه فانوس</span>
                <h1>مدیریت کتابخانه</h1>
                <p>مدیریت کتاب‌ها، اعضا، امانت‌ها و وضعیت برگشت کتاب‌ها را از این بخش انجام دهید.</p>
            </div>

            <div class="fanous-page-actions">
                @if ($canWriteLibrary)
                    <x-ds.button href="#new-library-book" data-library-panel-trigger="new-library-book" aria-controls="new-library-book" aria-expanded="false">
                        <x-ds.icon name="plus" />
                        افزودن کتاب جدید
                    </x-ds.button>
                    <x-ds.button variant="outline" href="#new-library-loan" data-library-panel-trigger="new-library-loan" aria-controls="new-library-loan" aria-expanded="false">ثبت امانت</x-ds.button>
                @endif
                <x-ds.button variant="outline" :href="route('library.inventory.export', request()->query())">خروجی CSV</x-ds.button>
            </div>
        </section>

        <section class="dashboard-stat-grid" aria-label="خلاصه کتابخانه">
            <article class="dashboard-stat">
                <div>
                    <span>کتاب‌های فعال</span>
                    <strong>{{ Locale::number($bookTitleCount) }}</strong>
                    <small>{{ Locale::number($availableCopyCount) }} نسخه آماده امانت</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="books" /></span>
            </article>

            <article class="dashboard-stat">
                <div>
                    <span>امانت‌های فعال</span>
                    <strong>{{ Locale::number($activeLoanCount) }}</strong>
                    <small>کتاب‌هایی که هنوز برگشت نشده‌اند</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="book" /></span>
            </article>

            <article class="dashboard-stat">
                <div>
                    <span>برگشت‌نشده</span>
                    <strong>{{ Locale::number($overdueLoans->count()) }}</strong>
                    <small>امانت‌های گذشته از تاریخ برگشت</small>
                </div>
                <span class="dashboard-stat-icon is-danger"><x-ds.icon name="bell" /></span>
            </article>

            <article class="dashboard-stat">
                <div>
                    <span>تمدیدها / تأخیرها</span>
                    <strong>{{ Locale::number($followUpCount) }}</strong>
                    <small>کارت‌ها، فیس‌ها و امانت‌های نیازمند پیگیری</small>
                </div>
                <span class="dashboard-stat-icon is-blue"><x-ds.icon name="calendar" /></span>
            </article>
        </section>

        @if ($isLibraryFinancePage)
        <section class="dashboard-stat-grid" aria-label="خلاصه مالی کتابخانه">
            <article class="dashboard-stat">
                <div>
                    <span>درآمد کتابخانه</span>
                    <strong>{{ Locale::money($libraryIncomeTotal) }}</strong>
                    <small>فیس ماهانه، قیمت کارت و دریافت‌های کتابخانه</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="cash" /></span>
            </article>

            <article class="dashboard-stat">
                <div>
                    <span>مصارف کتابخانه</span>
                    <strong>{{ Locale::money($libraryExpenseTotal) }}</strong>
                    <small>خرید کتاب، ترمیم، وسایل و مصارف عمومی</small>
                </div>
                <span class="dashboard-stat-icon is-danger"><x-ds.icon name="cash-minus" /></span>
            </article>

            <article class="dashboard-stat">
                <div>
                    <span>توازن کتابخانه</span>
                    <strong>{{ Locale::money($libraryFinanceBalance) }}</strong>
                    <small>درآمد منهای مصرف کتابخانه</small>
                </div>
                <span class="dashboard-stat-icon is-blue"><x-ds.icon name="chart" /></span>
            </article>

            <article class="dashboard-stat">
                <div>
                    <span>خالص ماه جاری</span>
                    <strong>{{ Locale::money($libraryMonthBalance) }}</strong>
                    <small>درآمد ماه: {{ Locale::money($libraryMonthIncome) }} · مصرف ماه: {{ Locale::money($libraryMonthExpense) }}</small>
                </div>
                <span class="dashboard-stat-icon is-purple"><x-ds.icon name="calendar" /></span>
            </article>
        </section>

        <section class="dashboard-panel fanous-period-report-card" id="library-finance-ledger">
            <div class="dashboard-panel-header">
                <div>
                    <span class="dashboard-section-kicker">دفتر مالی کتابخانه</span>
                    <h2>گزارش درآمد و مصرف کتابخانه</h2>
                    <p>درآمد کارت، فیس ماهانه، کمک‌ها، خرید کتاب، ترمیم و مصارف عمومی کتابخانه در همین دفتر جداگانه مدیریت می‌شود.</p>
                </div>
                <div class="fanous-filter-actions">
                    <x-ds.button size="sm" variant="outline" :href="route('library.finance.export', request()->query())">خروجی CSV</x-ds.button>
                    @if ($canWriteLibrary)
                        <x-ds.button size="sm" type="button" data-library-panel-trigger="library-finance-record" aria-controls="library-finance-record" aria-expanded="false">ثبت مالی جدید</x-ds.button>
                    @endif
                </div>
            </div>

            <div class="fanous-period-report-grid">
                @foreach ($libraryFinancePeriods as $period)
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
                    </article>
                @endforeach
            </div>

            <form method="GET" action="{{ route('library.finance.index') }}#library-finance-ledger" class="fanous-filter-grid fanous-finance-filter-grid">
                <div class="fanous-quick-filters">
                    @foreach ($libraryQuickFilters as $quickFilter)
                        @php
                            $isActiveQuickFilter = ($libraryFinanceFilters['finance_date_from'] ?? '') === $quickFilter['from']
                                && ($libraryFinanceFilters['finance_date_to'] ?? '') === $quickFilter['to'];
                            $quickFilterParams = array_filter(array_merge(request()->except(['finance_date_from', 'finance_date_to']), [
                                'finance_date_from' => $quickFilter['from'],
                                'finance_date_to' => $quickFilter['to'],
                            ]), fn ($value) => filled($value));
                        @endphp
                        <a class="{{ $isActiveQuickFilter ? 'is-active' : '' }}" href="{{ route('library.finance.index', $quickFilterParams) }}#library-finance-ledger">{{ $quickFilter['label'] }}</a>
                    @endforeach
                </div>

                <label>
                    <span>جستجو</span>
                    <input class="form-control" name="finance_q" value="{{ $libraryFinanceFilters['finance_q'] ?? '' }}" placeholder="شخص، رسید، دسته‌بندی یا توضیحات">
                </label>

                <label>
                    <span>نوع</span>
                    <select class="form-control" name="finance_type">
                        <option value="">همه نوع‌ها</option>
                        @foreach ($financeTypeLabels as $value => $label)
                            <option value="{{ $value }}" @selected(($libraryFinanceFilters['finance_type'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>دسته‌بندی</span>
                    <select class="form-control" name="finance_category">
                        <option value="">همه دسته‌ها</option>
                        @foreach ($libraryFinanceCategoryOptions as $category)
                            <option value="{{ $category->id }}" @selected((string) ($libraryFinanceFilters['finance_category'] ?? '') === (string) $category->id)>{{ str_replace('کتابخانه - ', '', $category->name) }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>روش پرداخت</span>
                    <select class="form-control" name="finance_payment_method">
                        <option value="">همه روش‌ها</option>
                        @foreach ($libraryPaymentMethods as $value => $label)
                            <option value="{{ $value }}" @selected(($libraryFinanceFilters['finance_payment_method'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>از تاریخ</span>
                    <input class="form-control" name="finance_date_from" type="date" value="{{ $libraryFinanceFilters['finance_date_from'] ?? '' }}">
                </label>

                <label>
                    <span>تا تاریخ</span>
                    <input class="form-control" name="finance_date_to" type="date" value="{{ $libraryFinanceFilters['finance_date_to'] ?? '' }}">
                </label>

                <div class="fanous-filter-actions">
                    <x-ds.button type="submit">جستجو</x-ds.button>
                    <x-ds.button variant="outline" :href="route('library.finance.export', request()->query())">CSV</x-ds.button>
                    <x-ds.button variant="outline" :href="route('library.finance.index').'#library-finance-ledger'">پاک کردن</x-ds.button>
                </div>
            </form>

            @if ($libraryFinanceCategorySummaries->isNotEmpty())
                <div class="fanous-period-report-grid">
                    @foreach ($libraryFinanceCategorySummaries as $summary)
                        <article class="fanous-period-report-item">
                            <div class="fanous-period-report-head">
                                <span>{{ $summary->category?->name ? str_replace('کتابخانه - ', '', $summary->category->name) : 'کتابخانه' }}</span>
                                <small>{{ $financeTypeLabels[$summary->type] ?? $summary->type }} · {{ Locale::number((int) $summary->records_count) }} ثبت</small>
                            </div>
                            <div class="fanous-period-report-values">
                                <div class="{{ $summary->type === 'income' ? 'is-income' : 'is-expense' }}">
                                    <span>مجموع</span>
                                    <strong>{{ Locale::money((int) $summary->total_amount) }}</strong>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
        @endif

        <section class="fanous-library-notice">
            <span>i</span>
            <p>کتابخانه فعال است. اعضا، امانت‌ها و برگشت کتاب‌ها را از این بخش مدیریت کنید.</p>
        </section>

        @unless ($canWriteLibrary)
            <section class="fanous-library-notice is-warning">
                <span>!</span>
                <p>حالت مشاهده فعال است. ثبت عضو، کتاب، امانت و برگشت کتاب مخصوص حساب کتابدار است.</p>
            </section>
        @endunless

        @if ($canWriteLibrary)
            <section class="dashboard-panel">
                <div class="dashboard-panel-header">
                    <div>
                        <span class="dashboard-section-kicker">دسترسی سریع</span>
                        <h2>کارهای روزانه کتابدار</h2>
                        <p>برای ثبت عضو، کتاب، امانت یا برگشت کتاب یکی از گزینه‌های زیر را باز کنید.</p>
                    </div>
                </div>

                <div class="fanous-library-action-grid">
                    <button class="fanous-library-action" type="button" data-library-panel-trigger="new-library-member" aria-controls="new-library-member" aria-expanded="false">
                        <span><x-ds.icon name="user" /></span>
                        <strong>ثبت عضو جدید</strong>
                        <small>پروفایل، فیس و کارت کتابخانه</small>
                    </button>
                    <button class="fanous-library-action" type="button" data-library-panel-trigger="new-library-book" aria-controls="new-library-book" aria-expanded="false">
                        <span><x-ds.icon name="book" /></span>
                        <strong>افزودن کتاب</strong>
                        <small>عنوان، نویسنده و نسخه‌ها</small>
                    </button>
                    <button class="fanous-library-action" type="button" data-library-panel-trigger="new-library-loan" aria-controls="new-library-loan" aria-expanded="false">
                        <span><x-ds.icon name="books" /></span>
                        <strong>ثبت امانت</strong>
                        <small>عضو، کتاب و تاریخ برگشت</small>
                    </button>
                    <button class="fanous-library-action" type="button" data-library-panel-trigger="return-library-copy" aria-controls="return-library-copy" aria-expanded="false">
                        <span><x-ds.icon name="edit" /></span>
                        <strong>برگشت کتاب</strong>
                        <small>اسکن کد نسخه و ثبت برگشت</small>
                    </button>
                    <a class="fanous-library-action" href="{{ route('library.fee-reminders.index') }}">
                        <span><x-ds.icon name="bell" /></span>
                        <strong>پیگیری فیس</strong>
                        <small>یادآوری پرداخت ماهانه</small>
                    </a>
                    <a class="fanous-library-action" href="{{ route('library.inventory.report') }}">
                        <span><x-ds.icon name="report" /></span>
                        <strong>گزارش موجودی</strong>
                        <small>نسخه‌ها و وضعیت کتاب‌ها</small>
                    </a>
                    @if ($isLibraryFinancePage)
                        <button class="fanous-library-action" type="button" data-library-panel-trigger="library-finance-record" aria-controls="library-finance-record" aria-expanded="false">
                            <span><x-ds.icon name="cash" /></span>
                            <strong>ثبت مالی کتابخانه</strong>
                            <small>درآمد یا مصرف خارج از فیس و کارت</small>
                        </button>
                    @endif
                </div>
            </section>

            <section class="fanous-library-forms" id="library-action-forms">
                <article class="dashboard-panel fanous-library-form-empty" data-library-panel-empty>
                    <div class="fanous-library-notice">
                        <span>i</span>
                        <p>از کارت‌های دسترسی سریع بالا یک عملیات را انتخاب کنید تا فرم مربوط در همین بخش باز شود.</p>
                    </div>
                </article>

                @if ($isLibraryFinancePage)
                <article class="dashboard-panel fanous-library-form-panel" id="library-finance-record" data-library-panel>
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">مالی کتابخانه</span>
                            <h2>ثبت درآمد یا مصرف کتابخانه</h2>
                            <p>فیس ماهانه و قیمت کارت به صورت اتومات ثبت می‌شود؛ این فرم برای درآمد و مصرف‌های اضافی کتابخانه است.</p>
                        </div>
                        <x-ds.button variant="outline" size="sm" type="button" data-library-panel-close>بستن</x-ds.button>
                    </div>

                    <form method="POST" action="{{ route('library.finance.store') }}" class="fanous-library-form">
                        @csrf
                        <label>
                            <span>نوع ثبت</span>
                            <select class="form-control" name="type" required>
                                <option value="income">درآمد</option>
                                <option value="expense">مصرف</option>
                            </select>
                        </label>
                        <label>
                            <span>دسته‌بندی</span>
                            <select class="form-control" name="category_key" required>
                                @foreach ($libraryFinanceCategories as $key => $category)
                                    <option value="{{ $key }}">{{ $category['label'] }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label><span>مبلغ</span><input class="form-control" name="amount" type="number" min="1" required></label>
                        <label><span>تاریخ</span><input class="form-control" name="transaction_date" type="date" value="{{ now()->format('Y-m-d') }}" required></label>
                        <label>
                            <span>روش پرداخت</span>
                            <select class="form-control" name="payment_method">
                                @foreach ($libraryPaymentMethods as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label><span>شخص / منبع / مصرف‌شونده</span><input class="form-control" name="source_or_payee" placeholder="نام شخص، فروشنده یا منبع درآمد"></label>
                        <label><span>شماره رسید</span><input class="form-control ltr-text" name="receipt_number" placeholder="در صورت خالی بودن خودکار ساخته می‌شود"></label>
                        <label class="fanous-form-wide"><span>توضیحات</span><textarea class="form-control" name="description" rows="3" placeholder="جزئیات درآمد یا مصرف کتابخانه"></textarea></label>
                        <div class="fanous-form-actions">
                            <x-ds.button type="submit">ذخیره ثبت مالی</x-ds.button>
                        </div>
                    </form>
                </article>
                @endif

                <article class="dashboard-panel fanous-library-form-panel" id="new-library-member" data-library-panel>
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">عضویت</span>
                            <h2>ثبت عضو کتابخانه</h2>
                            <p>پروفایل عضو را بسازید و در صورت نیاز کارت ماهانه چاپ کنید.</p>
                        </div>
                        <x-ds.button variant="outline" size="sm" type="button" data-library-panel-close>بستن</x-ds.button>
                    </div>

                    <form method="POST" action="{{ route('library.members.store') }}" enctype="multipart/form-data" class="fanous-library-form fanous-guarded-card-form" data-card-required-form data-card-required-message="برای ثبت عضو و محاسبه مالی، فیلدهای ضروری را تکمیل کرده و از دکمه ذخیره و چاپ کارت استفاده کنید.">
                        @csrf
                        <label><span>کد عضویت</span><input class="form-control ltr-text" name="member_code" placeholder="خودکار اگر خالی باشد"></label>
                        <label><span>نام کامل</span><input class="form-control" name="full_name" required></label>
                        <label><span>نام پدر</span><input class="form-control" name="father_name" required></label>
                        <label><span>شماره تماس</span><input class="form-control ltr-text" name="phone" required></label>
                        <label><span>ایمیل</span><input class="form-control ltr-text" name="email" type="email"></label>
                        <label><span>عکس پروفایل</span><input class="form-control" name="profile_photo" type="file" accept="image/*"></label>
                        <label><span>تذکره / ID</span><input class="form-control ltr-text" name="tazkira_number"></label>
                        <label><span>محل تحصیل</span><input class="form-control" name="education_place"></label>
                        <label><span>دیپارتمنت / صنف</span><input class="form-control" name="department_or_grade"></label>
                        <label><span>فیس ماهانه</span><input class="form-control" name="membership_fee" type="number" min="0" value="0"></label>
                        <label><span>قیمت کارت</span><input class="form-control" name="card_fee_amount" type="number" min="0" value="50"></label>
                        <label>
                            <span>وضعیت پرداخت</span>
                            <select class="form-control" name="payment_status">
                                <option value="unpaid">پرداخت نشده</option>
                                <option value="paid">پرداخت شده</option>
                            </select>
                        </label>
                        <label><span>تاریخ عضویت</span><input class="form-control" name="joined_at" type="date" value="{{ now()->format('Y-m-d') }}"></label>
                        <label>
                            <span>وضعیت</span>
                            <select class="form-control" name="status">
                                @foreach ($memberStatusMeta as $value => $meta)
                                    <option value="{{ $value }}">{{ $meta['label'] }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="fanous-form-wide"><span>آدرس</span><input class="form-control" name="address"></label>
                        <label class="fanous-form-wide"><span>یادداشت</span><textarea class="form-control" name="notes" rows="3"></textarea></label>
                        <div class="fanous-form-actions">
                            <x-ds.button type="submit" data-disabled-until-card disabled>ذخیره عضو</x-ds.button>
                            <x-ds.button variant="outline" name="issue_card" value="1" type="submit" data-card-submit disabled>ذخیره و چاپ کارت</x-ds.button>
                        </div>
                    </form>
                </article>

                <article class="dashboard-panel fanous-library-form-panel" id="new-library-book" data-library-panel>
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">کتاب</span>
                            <h2>افزودن کتاب جدید</h2>
                            <p>مشخصات کتاب، قفسه، تعداد نسخه‌ها و وضعیت فعلی را ثبت کنید.</p>
                        </div>
                        <x-ds.button variant="outline" size="sm" type="button" data-library-panel-close>بستن</x-ds.button>
                    </div>

                    <form method="POST" action="{{ route('library.books.store') }}" class="fanous-library-form">
                        @csrf
                        <label><span>ISBN</span><input class="form-control ltr-text" name="isbn"></label>
                        <label><span>بارکد</span><input class="form-control ltr-text" name="barcode" placeholder="خودکار اگر خالی باشد"></label>
                        <label class="fanous-form-wide"><span>عنوان کتاب</span><input class="form-control" name="title" required></label>
                        <label><span>نویسنده</span><input class="form-control" name="author"></label>
                        <label><span>ناشر</span><input class="form-control" name="publisher"></label>
                        <label><span>زبان</span><input class="form-control" name="language"></label>
                        <label><span>چاپ / ویرایش</span><input class="form-control" name="edition"></label>
                        <label><span>سال نشر</span><input class="form-control" name="published_year" type="number" min="1000" max="{{ now()->year }}"></label>
                        <label><span>صفحات</span><input class="form-control" name="pages" type="number" min="1"></label>
                        <label><span>دسته‌بندی</span><input class="form-control" name="category"></label>
                        <label><span>کد قفسه</span><input class="form-control ltr-text" name="shelf_code"></label>
                        <label><span>تعداد نسخه‌ها</span><input class="form-control" name="total_copies" type="number" min="1" value="1" required></label>
                        <label>
                            <span>وضعیت</span>
                            <select class="form-control" name="status">
                                @foreach ($bookStatusMeta as $value => $meta)
                                    <option value="{{ $value }}">{{ $meta['label'] }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="fanous-form-wide"><span>یادداشت</span><textarea class="form-control" name="notes" rows="3"></textarea></label>
                        <div class="fanous-form-actions"><x-ds.button type="submit">ذخیره کتاب</x-ds.button></div>
                    </form>
                </article>

                <article class="dashboard-panel fanous-library-form-panel" id="new-library-loan" data-library-panel>
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">امانت</span>
                            <h2>ثبت امانت کتاب</h2>
                            <p>عضو فعال و کتاب موجود را انتخاب کنید؛ سیستم تعداد نسخه‌های موجود را مدیریت می‌کند.</p>
                        </div>
                        <x-ds.button variant="outline" size="sm" type="button" data-library-panel-close>بستن</x-ds.button>
                    </div>

                    <form method="POST" action="{{ route('library.loans.store') }}" class="fanous-library-form">
                        @csrf
                        <label><span>کد امانت</span><input class="form-control ltr-text" name="loan_code" placeholder="خودکار اگر خالی باشد"></label>
                        <label>
                            <span>عضو</span>
                            <select class="form-control" name="library_member_id" required>
                                <option value="">انتخاب عضو</option>
                                @foreach ($activeMembers as $member)
                                    <option value="{{ $member->id }}">{{ $member->full_name }} - {{ $member->member_code }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span>کتاب</span>
                            <select class="form-control" name="book_id" required>
                                <option value="">انتخاب کتاب</option>
                                @foreach ($availableBooks as $book)
                                    <option value="{{ $book->id }}">{{ $book->title }} - {{ Locale::number($book->available_copies) }} موجود</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span>بارکد / کد نسخه</span>
                            <input class="form-control ltr-text" name="copy_code" list="available-copy-codes" placeholder="اسکن یا درج کد نسخه" required>
                            <datalist id="available-copy-codes">
                                @foreach ($availableBooks as $book)
                                    @foreach ($book->availableCopies as $copy)
                                        <option value="{{ $copy->copy_code }}">{{ $book->title }} - {{ $copy->shelf_code ?: 'بدون قفسه' }}</option>
                                    @endforeach
                                @endforeach
                            </datalist>
                        </label>
                        <label><span>تاریخ امانت</span><input class="form-control" name="borrowed_at" type="date" value="{{ now()->format('Y-m-d') }}" required></label>
                        <label><span>تاریخ برگشت</span><input class="form-control" name="due_at" type="date" value="{{ now()->addDays(7)->format('Y-m-d') }}"></label>
                        <label><span>وضعیت هنگام خروج</span><input class="form-control" name="condition_out"></label>
                        <label class="fanous-form-wide"><span>یادداشت</span><input class="form-control" name="notes"></label>
                        <div class="fanous-form-actions"><x-ds.button type="submit">ذخیره امانت</x-ds.button></div>
                    </form>
                </article>

                <article class="dashboard-panel fanous-library-form-panel" id="return-library-copy" data-library-panel>
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">برگشت</span>
                            <h2>برگشت کتاب با بارکد</h2>
                            <p>کد نسخه را اسکن کنید تا امانت فعال پیدا و برگشت کتاب ثبت شود.</p>
                        </div>
                        <x-ds.button variant="outline" size="sm" type="button" data-library-panel-close>بستن</x-ds.button>
                    </div>

                    <form method="POST" action="{{ route('library.loans.return-by-copy') }}" class="fanous-library-form">
                        @csrf
                        <label>
                            <span>بارکد / کد نسخه</span>
                            <input class="form-control ltr-text @error('copy_code') is-invalid @enderror" name="copy_code" value="{{ old('copy_code') }}" placeholder="اسکن نسخه برگشتی" required autofocus>
                            @error('copy_code') <span class="text-danger small">{{ $message }}</span> @enderror
                        </label>
                        <label><span>تاریخ برگشت</span><input class="form-control" name="returned_at" type="date" value="{{ now()->format('Y-m-d') }}" required></label>
                        <label><span>جریمه</span><input class="form-control" name="fine_amount" type="number" min="0" value="0"></label>
                        <label>
                            <span>وضعیت برگشت</span>
                            <select class="form-control" name="return_status">
                                <option value="available">سالم / قابل امانت</option>
                                <option value="damaged">خراب</option>
                                <option value="lost">گم‌شده</option>
                            </select>
                        </label>
                        <label><span>وضعیت هنگام برگشت</span><input class="form-control" name="condition_in" placeholder="سالم / خراب"></label>
                        <div class="fanous-form-actions"><x-ds.button type="submit">ثبت برگشت</x-ds.button></div>
                    </form>
                </article>
            </section>
        @endif

        @if ($isLibraryFinancePage)
        <section class="dashboard-panel">
            <div class="dashboard-panel-header">
                <div>
                    <span class="dashboard-section-kicker">دفتر مالی کتابخانه</span>
                    <h2>آخرین ثبت‌های مالی کتابخانه</h2>
                    <p>درآمدهای اتومات از فیس و کارت، همراه با درآمد و مصرف‌های دستی کتابدار.</p>
                </div>
                @if ($canWriteLibrary)
                    <x-ds.button size="sm" type="button" data-library-panel-trigger="library-finance-record" aria-controls="library-finance-record" aria-expanded="false">ثبت مالی جدید</x-ds.button>
                @endif
            </div>

            <div class="fanous-table-wrap">
                <table class="fanous-finance-table">
                    <thead>
                        <tr>
                            <th>تاریخ</th>
                            <th>نوع</th>
                            <th>دسته‌بندی</th>
                            <th>شخص / منبع</th>
                            <th>روش</th>
                            <th>رسید</th>
                            <th>مبلغ</th>
                            <th>ثبت‌کننده</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($libraryFinanceRecords as $record)
                            <tr>
                                <td>{{ $record->transaction_date ? Locale::number($record->transaction_date->format('Y/m/d')) : 'ثبت نشده' }}</td>
                                <td><x-ds.badge :tone="$record->type === 'income' ? 'success' : 'danger'">{{ $record->type === 'income' ? 'درآمد' : 'مصرف' }}</x-ds.badge></td>
                                <td>{{ $record->category?->name ? str_replace('کتابخانه - ', '', $record->category->name) : 'کتابخانه' }}</td>
                                <td>{{ $record->source_or_payee ?: $record->payer_name ?: $record->payee_name ?: 'کتابخانه فانوس' }}</td>
                                <td>{{ $libraryPaymentMethods[$record->payment_method] ?? $record->payment_method }}</td>
                                <td class="ltr-text">{{ $record->receipt_number ?: $record->transaction_number }}</td>
                                <td>{{ Locale::money((int) $record->amount) }}</td>
                                <td>{{ $record->recordedBy?->name ?: 'سیستم' }}</td>
                                <td>
                                    <x-ds.button size="sm" variant="outline" :href="route('library.finance.transactions.receipt', $record)">رسید</x-ds.button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9"><div class="dashboard-empty">هنوز ثبت مالی کتابخانه وجود ندارد.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
        @endif

        <section class="fanous-library-layout">
            <div class="fanous-library-main">
                <article class="dashboard-panel" id="library-members-panel">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">اعضا</span>
                            <h2>اعضای کتابخانه</h2>
                            <p>اعضای ثبت‌شده، وضعیت پرداخت و امانت‌های هر عضو را مدیریت کنید.</p>
                        </div>
                        @if ($canWriteLibrary)
                            <x-ds.button size="sm" type="button" data-library-panel-trigger="new-library-member" aria-controls="new-library-member" aria-expanded="false">افزودن عضو</x-ds.button>
                        @endif
                    </div>

                    <form method="GET" action="{{ route('library.index') }}" class="fanous-library-filters">
                        <input class="form-control" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="جستجوی نام، شماره تماس یا کد عضویت">
                        <select class="form-control" name="status">
                            <option value="">همه وضعیت‌ها</option>
                            @foreach ($memberStatusMeta as $value => $meta)
                                <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $meta['label'] }}</option>
                            @endforeach
                        </select>
                        <div class="fanous-filter-actions">
                            <x-ds.button type="submit">جستجو</x-ds.button>
                            <x-ds.button variant="outline" :href="route('library.members.export', request()->only(['q', 'status']))">CSV</x-ds.button>
                            <x-ds.button variant="outline" :href="route('library.index')">پاک کردن</x-ds.button>
                        </div>
                    </form>

                    <div class="fanous-library-member-grid">
                        @forelse ($members as $member)
                            @php
                                $card = $member->membershipCards->first();
                                $statusMeta = $memberStatusMeta[$member->status] ?? ['label' => $member->status, 'tone' => 'primary'];
                                $paymentTone = $member->payment_status === 'paid' ? 'success' : 'warning';
                            @endphp
                            <article class="fanous-library-member-card">
                                <div class="fanous-library-member-head">
                                    @if ($member->profile_photo_path)
                                        <img class="fanous-library-avatar" src="{{ asset('storage/'.$member->profile_photo_path) }}" alt="{{ $member->full_name }}">
                                    @else
                                        <span class="fanous-library-avatar">{{ mb_substr($member->full_name, 0, 1) }}</span>
                                    @endif
                                    <div>
                                        <strong>{{ $member->full_name }}</strong>
                                        <span class="ltr-text">{{ $member->member_code ?: 'N/A' }}</span>
                                    </div>
                                    <x-ds.badge :tone="$statusMeta['tone']">{{ $statusMeta['label'] }}</x-ds.badge>
                                </div>

                                <div class="fanous-library-member-meta">
                                    <div><span>شماره تماس</span><strong class="ltr-text">{{ $member->phone }}</strong></div>
                                    <div><span>فیس ماهانه</span><strong>{{ Locale::money((int) $member->membership_fee) }}</strong></div>
                                    <div><span>باقی فیس</span><strong>{{ Locale::money((int) $member->monthlyFeeBalance()) }}</strong></div>
                                    <div><span>پرداخت</span><strong><x-ds.badge :tone="$paymentTone">{{ $member->payment_status === 'paid' ? 'پرداخت شده' : 'پرداخت نشده' }}</x-ds.badge></strong></div>
                                    <div><span>تاریخ ثبت</span><strong>{{ $member->joined_at ? Locale::number($member->joined_at->format('Y/m/d')) : 'ثبت نشده' }}</strong></div>
                                    <div><span>اعتبار کارت</span><strong>{{ $member->membership_expires_at ? Locale::number($member->membership_expires_at->format('Y/m/d')) : ($card?->expires_at ? Locale::number($card->expires_at->format('Y/m/d')) : 'ندارد') }}</strong></div>
                                </div>

                                <div class="fanous-library-member-actions">
                                    <x-ds.button size="sm" :href="route('library.members.show', $member)">مشاهده</x-ds.button>
                                    @if ($canWriteLibrary)
                                        <x-ds.button variant="outline" size="sm" :href="route('library.members.edit', $member)">ویرایش</x-ds.button>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="dashboard-empty">هیچ عضو کتابخانه پیدا نشد.</div>
                        @endforelse
                    </div>

                    @if ($members->hasPages())
                        <div class="fanous-pagination">
                            {{ $members->links() }}
                        </div>
                    @endif
                </article>

                <article class="dashboard-panel" id="recent-library-loans">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">امانت‌ها</span>
                            <h2>امانت‌های اخیر</h2>
                            <p>کتاب‌های امانت گرفته‌شده، تاریخ برگشت و وضعیت هر ثبت.</p>
                        </div>
                        @if ($canWriteLibrary)
                            <x-ds.button size="sm" type="button" data-library-panel-trigger="new-library-loan" aria-controls="new-library-loan" aria-expanded="false">ثبت امانت جدید</x-ds.button>
                        @endif
                    </div>

                    <div class="fanous-library-loan-list">
                        @forelse ($loans as $loan)
                            @php
                                $isLateLoan = in_array($loan->status, ['borrowed', 'late'], true) && $loan->due_at && $loan->due_at->isPast();
                                $statusMeta = $loanStatusMeta[$isLateLoan ? 'late' : $loan->status] ?? ['label' => $loan->status, 'tone' => 'primary'];
                            @endphp
                            <article class="fanous-library-loan-card">
                                <span class="fanous-record-icon {{ $loan->status === 'returned' ? 'is-income' : ($isLateLoan ? 'is-expense' : 'is-warning') }}">ا</span>
                                <div class="fanous-record-main">
                                    <div>
                                        <strong>{{ $loan->member?->full_name ?: 'عضو نامشخص' }}</strong>
                                        <span>{{ $loan->book?->title ?: 'کتاب نامشخص' }}</span>
                                    </div>
                                    <div class="fanous-record-meta">
                                        <span>امانت: {{ $loan->borrowed_at ? Locale::number($loan->borrowed_at->format('Y/m/d')) : 'ثبت نشده' }}</span>
                                        <span>برگشت: {{ $loan->due_at ? Locale::number($loan->due_at->format('Y/m/d')) : 'ندارد' }}</span>
                                        <span class="ltr-text">Copy {{ $loan->copy?->copy_code ?: 'N/A' }}</span>
                                    </div>

                                    @if ($canWriteLibrary && $loan->status !== 'returned')
                                        <form method="POST" action="{{ route('library.loans.return', $loan) }}" class="fanous-library-return-form">
                                            @csrf
                                            @method('PUT')
                                            <input class="form-control" name="returned_at" type="date" value="{{ now()->format('Y-m-d') }}" required>
                                            <input class="form-control" name="fine_amount" type="number" min="0" value="0" placeholder="جریمه">
                                            <select class="form-control" name="return_status">
                                                <option value="available">سالم</option>
                                                <option value="damaged">خراب</option>
                                                <option value="lost">گم‌شده</option>
                                            </select>
                                            <input class="form-control" name="condition_in" placeholder="وضعیت برگشت">
                                            <x-ds.button type="submit" size="sm">برگشت</x-ds.button>
                                        </form>
                                    @endif
                                </div>
                                <div class="fanous-record-side">
                                    <x-ds.badge :tone="$statusMeta['tone']">{{ $statusMeta['label'] }}</x-ds.badge>
                                    @if ($loan->member)
                                        <x-ds.button variant="outline" size="sm" :href="route('library.members.show', $loan->member)">پروفایل</x-ds.button>
                                    @endif
                                    @if ($canWriteLibrary)
                                        <x-ds.button variant="outline" size="sm" :href="route('library.loans.edit', $loan)">ویرایش</x-ds.button>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="dashboard-empty">
                                <strong>هنوز امانت ثبت نشده است</strong>
                                @if ($canWriteLibrary)
                                    <x-ds.button size="sm" type="button" data-library-panel-trigger="new-library-loan" aria-controls="new-library-loan" aria-expanded="false">ثبت امانت جدید</x-ds.button>
                                @endif
                            </div>
                        @endforelse
                    </div>
                </article>
            </div>

            <aside class="fanous-library-sidebar">
                <article class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">موجودی</span>
                            <h2>موجودی کتاب‌ها</h2>
                            <p>خلاصه نسخه‌های موجود، امانت‌رفته و آسیب‌دیده.</p>
                        </div>
                    </div>

                    <div class="fanous-collection-list">
                        <div><span class="fanous-record-icon is-income">ک</span><p><strong>کل کتاب‌ها</strong><small>عنوان‌های ثبت‌شده</small></p><b>{{ Locale::number($bookTitleCount) }}</b></div>
                        <div><span class="fanous-record-icon is-income">م</span><p><strong>نسخه‌های موجود</strong><small>آماده امانت</small></p><b>{{ Locale::number($availableCopyCount) }}</b></div>
                        <div><span class="fanous-record-icon is-warning">ا</span><p><strong>نسخه‌های امانت‌رفته</strong><small>خارج از قفسه</small></p><b>{{ Locale::number($borrowedCopyCount) }}</b></div>
                        <div><span class="fanous-record-icon is-expense">خ</span><p><strong>خراب / گم‌شده</strong><small>نیازمند پیگیری</small></p><b>{{ Locale::number($damagedLostCount) }}</b></div>
                    </div>
                </article>

                <article class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">کتاب‌ها</span>
                            <h2>فهرست کوتاه موجودی</h2>
                        </div>
                    </div>

                    <div class="fanous-library-book-list">
                        @forelse ($books->take(8) as $book)
                            @php $statusMeta = $bookStatusMeta[$book->status] ?? ['label' => $book->status, 'tone' => 'primary']; @endphp
                            <div class="fanous-library-book-row">
                                <span class="fanous-record-icon">ک</span>
                                <div>
                                    <strong>{{ $book->title }}</strong>
                                    <small>{{ $book->author ?: 'نویسنده نامشخص' }} · قفسه: <span class="ltr-text">{{ $book->shelf_code ?: 'N/A' }}</span></small>
                                    <small>{{ Locale::number($book->available_copies) }}/{{ Locale::number($book->total_copies) }} موجود · {{ Locale::number($book->copies_count ?? 0) }} نسخه فزیکی</small>
                                </div>
                                <div class="fanous-row-actions">
                                    <x-ds.badge :tone="$statusMeta['tone']">{{ $statusMeta['label'] }}</x-ds.badge>
                                    @if ($canWriteLibrary)
                                        <x-ds.button variant="outline" size="sm" :href="route('library.books.edit', $book)">ویرایش</x-ds.button>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="dashboard-empty">هنوز کتاب ثبت نشده است.</div>
                        @endforelse
                    </div>
                </article>

                <article class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">گزارش‌ها</span>
                            <h2>آخرین گزارش‌ها</h2>
                            <p>پیگیری امانت‌های دیرشده، کارت‌های منقضی و فیس‌های نزدیک.</p>
                        </div>
                    </div>

                    <div class="fanous-follow-list">
                        @forelse ($overdueLoans->take(4) as $loan)
                            <div class="fanous-follow-row">
                                <strong>{{ $loan->member?->full_name ?: 'عضو نامشخص' }}</strong>
                                <span>{{ $loan->book?->title ?: 'کتاب نامشخص' }} · تاریخ برگشت: {{ $loan->due_at ? Locale::number($loan->due_at->format('Y/m/d')) : 'ندارد' }}</span>
                            </div>
                        @empty
                            <div class="dashboard-empty">فعلاً امانت دیرشده وجود ندارد.</div>
                        @endforelse

                        @foreach ($expiringMembers->take(3) as $member)
                            @php
                                $feeFine = max((int) $member->monthly_fee_fine_amount, $member->calculatedMonthlyFine());
                                $feeBalance = (int) $member->membership_fee + $feeFine;
                            @endphp
                            <div class="fanous-follow-row">
                                <strong>{{ $member->full_name }}</strong>
                                <span>سررسید: {{ $member->next_payment_due_at ? Locale::number($member->next_payment_due_at->format('Y/m/d')) : 'ندارد' }} · باقی: {{ Locale::money($feeBalance) }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="fanous-library-report-actions">
                        <x-ds.button variant="outline" :href="route('library.inventory.report')">مشاهده گزارش‌ها</x-ds.button>
                    </div>
                </article>
            </aside>
        </section>
    </div>

    @if ($canWriteLibrary)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const panels = Array.from(document.querySelectorAll('[data-library-panel]'));
                const triggers = Array.from(document.querySelectorAll('[data-library-panel-trigger]'));
                const emptyState = document.querySelector('[data-library-panel-empty]');
                const formArea = document.getElementById('library-action-forms');
                const defaultPanel = @json($hasMemberFilters ? null : null);

                function setPanel(panelId, shouldScroll) {
                    const activePanel = panels.find((panel) => panel.id === panelId);

                    panels.forEach((panel) => {
                        panel.classList.toggle('is-active', panel === activePanel);
                    });

                    triggers.forEach((trigger) => {
                        const isActive = trigger.getAttribute('data-library-panel-trigger') === panelId;
                        trigger.classList.toggle('is-active', isActive);
                        trigger.setAttribute('aria-expanded', isActive ? 'true' : 'false');
                    });

                    if (emptyState) {
                        emptyState.classList.toggle('is-hidden', Boolean(activePanel));
                    }

                    if (activePanel && shouldScroll) {
                        formArea?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }

                triggers.forEach((trigger) => {
                    trigger.addEventListener('click', function (event) {
                        event.preventDefault();
                        setPanel(trigger.getAttribute('data-library-panel-trigger'), true);
                    });
                });

                document.querySelectorAll('[data-library-panel-close]').forEach((button) => {
                    button.addEventListener('click', function () {
                        panels.forEach((panel) => panel.classList.remove('is-active'));
                        triggers.forEach((trigger) => {
                            trigger.classList.remove('is-active');
                            trigger.setAttribute('aria-expanded', 'false');
                        });
                        emptyState?.classList.remove('is-hidden');
                    });
                });

                if (window.location.hash && document.querySelector(window.location.hash + '[data-library-panel]')) {
                    setPanel(window.location.hash.slice(1), false);
                } else if (defaultPanel) {
                    setPanel(defaultPanel, false);
                }

                document.querySelectorAll('[data-card-required-form]').forEach((form) => {
                    const cardSubmit = form.querySelector('[data-card-submit]');
                    const disabledUntilCard = form.querySelectorAll('[data-disabled-until-card]');
                    const message = form.dataset.cardRequiredMessage || 'فیلدهای ضروری را تکمیل کنید و کارت را صادر کنید.';

                    const requiredControls = () => Array.from(form.querySelectorAll('input, select, textarea'))
                        .filter((control) => control.required && !control.disabled && control.type !== 'hidden');

                    const isComplete = () => requiredControls().every((control) => {
                        if (control.type === 'checkbox' || control.type === 'radio') {
                            return Boolean(form.querySelector(`[name="${CSS.escape(control.name)}"]:checked`));
                        }

                        return control.value.trim() !== '' && control.checkValidity();
                    });

                    const syncState = () => {
                        const complete = isComplete();

                        if (cardSubmit) {
                            cardSubmit.disabled = !complete;
                            cardSubmit.setAttribute('aria-disabled', String(!complete));
                        }

                        disabledUntilCard.forEach((button) => {
                            button.disabled = true;
                            button.setAttribute('aria-disabled', 'true');
                            button.title = message;
                        });
                    };

                    form.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter' && event.target instanceof HTMLElement && event.target.tagName !== 'TEXTAREA') {
                            event.preventDefault();
                        }
                    });

                    form.addEventListener('submit', (event) => {
                        if (event.submitter !== cardSubmit || !isComplete()) {
                            event.preventDefault();
                            form.reportValidity();
                        }
                    });

                    form.addEventListener('input', syncState);
                    form.addEventListener('change', syncState);
                    syncState();
                });
            });
        </script>
    @endif
@endsection
