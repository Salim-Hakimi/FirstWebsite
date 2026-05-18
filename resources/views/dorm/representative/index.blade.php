@extends('admin.layout')

@section('title', 'نماینده محصلین - ادمین فانوس')
@section('content_wrapper_class', 'fanous-dashboard-wrapper')

@section('content')
    @php
        use App\Support\Locale;

        $balance = $totalIncome - $totalExpenses;
        $typeNames = [
            'monthly_fee' => 'پول ماهانه',
            'electricity' => 'پول برق',
            'fine' => 'جریمه',
            'water' => 'پول آب',
            'expense' => 'مصرف نماینده',
        ];
        $incomeTypeNames = collect($typeNames)->except('expense')->all();
        $recordCount = $collections->count();
    @endphp

    <div class="fanous-representative-page" dir="rtl">
        <section class="fanous-page-header">
            <div>
                <span class="dashboard-section-kicker">مدیریت لیلیه</span>
                <h1>نماینده محصلین</h1>
                <p>نماینده‌ها، جمع‌آوری ماهانه، بدهی‌ها و گزارش‌های مربوط به محصلین را مدیریت کنید.</p>
            </div>

            <div class="fanous-page-actions">
                <x-ds.button variant="outline" href="#representative-filters">فیلتر گزارش‌ها</x-ds.button>
                @if ($canRecord)
                    <x-ds.button variant="outline" href="#representative-account">ثبت نماینده</x-ds.button>
                @endif
                <x-ds.button :href="route('representative.report', $filters)">گزارش گرفتن</x-ds.button>
            </div>
        </section>

        <section class="dashboard-stat-grid" aria-label="خلاصه حساب نماینده">
            <article class="dashboard-stat">
                <div>
                    <span>کل درآمد افغانی</span>
                    <strong>{{ Locale::money($totalIncome) }}</strong>
                    <small>مجموع دریافت‌های ثبت‌شده نماینده</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="cash" /></span>
            </article>

            <article class="dashboard-stat">
                <div>
                    <span>کل مصرف افغانی</span>
                    <strong>{{ Locale::money($totalExpenses) }}</strong>
                    <small>مصارف ثبت‌شده از حساب نماینده</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="cash-minus" /></span>
            </article>

            <article class="dashboard-stat">
                <div>
                    <span>باقی‌مانده افغانی</span>
                    <strong>{{ Locale::money($balance) }}</strong>
                    <small>توازن فعلی حساب نماینده</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="chart" /></span>
            </article>

            <article class="dashboard-stat">
                <div>
                    <span>محصلین فعال</span>
                    <strong>{{ Locale::number($students->count()) }}</strong>
                    <small>{{ Locale::number($recordCount) }} ثبت اخیر در این گزارش</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="users" /></span>
            </article>
        </section>

        <section class="fanous-representative-layout">
            <div class="fanous-representative-main">
                <article class="dashboard-panel" id="representative-filters">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">جستجو و فیلتر</span>
                            <h2>گزارش حساب نماینده</h2>
                            <p>ثبت‌ها را بر اساس شاگرد، نوع پرداخت، تاریخ یا دوره مشخص فیلتر کنید.</p>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('representative.index') }}" class="fanous-representative-filters">
                        <label>
                            <span>جستجو</span>
                            <input class="form-control" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="نام شاگرد، نام پدر، تماس، ID یا اتاق">
                        </label>

                        <label>
                            <span>نوع پرداخت</span>
                            <select class="form-control" name="type">
                                <option value="">همه نوع‌ها</option>
                                @foreach ($typeNames as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label>
                            <span>تاریخ شروع</span>
                            <input class="form-control" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}">
                        </label>

                        <label>
                            <span>تاریخ ختم</span>
                            <input class="form-control" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}">
                        </label>

                        <label>
                            <span>دوره / ماه</span>
                            <input class="form-control" name="period" value="{{ $filters['period'] ?? '' }}" placeholder="مثلاً حمل ۱۴۰۵">
                        </label>

                        <div class="fanous-filter-actions">
                            <x-ds.button type="submit">جستجو</x-ds.button>
                            <x-ds.button variant="outline" :href="route('representative.index')">پاک کردن</x-ds.button>
                            <x-ds.button variant="outline" :href="route('representative.report', $filters)">گزارش</x-ds.button>
                        </div>
                    </form>
                </article>

                <article class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">آخرین ثبت‌ها</span>
                            <h2>آخرین ثبت‌های نماینده</h2>
                            <p>دریافت‌ها و مصارف تازه حساب نماینده در این بخش دیده می‌شود.</p>
                        </div>
                    </div>

                    <div class="fanous-representative-record-list">
                        @forelse ($collections as $collection)
                            @php
                                $isExpense = $collection->type === 'expense';
                                $tone = $isExpense ? 'danger' : 'success';
                            @endphp

                            <article class="fanous-representative-record">
                                <span class="fanous-record-icon {{ $isExpense ? 'is-expense' : 'is-income' }}">
                                    <x-ds.icon :name="$isExpense ? 'cash-minus' : 'cash'" />
                                </span>

                                <div class="fanous-record-main">
                                    <div>
                                        <strong>{{ $collection->student?->full_name ?: 'مصرف عمومی نماینده' }}</strong>
                                        <span>{{ $typeNames[$collection->type] ?? $collection->type }}</span>
                                    </div>

                                    <div class="fanous-record-meta">
                                        <span>تاریخ: {{ $collection->collected_at ? Locale::number($collection->collected_at->format('Y/m/d')) : 'ثبت نشده' }}</span>
                                        <span>دوره: {{ $collection->period ?: 'ندارد' }}</span>
                                    </div>
                                </div>

                                <div class="fanous-record-side">
                                    <x-ds.badge :tone="$tone">{{ $isExpense ? 'مصرف' : 'پرداخت‌شده' }}</x-ds.badge>
                                    <strong>{{ Locale::money($collection->amount) }}</strong>
                                    @if ($collection->student)
                                        <x-ds.button variant="outline" size="sm" :href="route('dorm.students.show', $collection->student)">پروفایل</x-ds.button>
                                    @endif
                                </div>
                            </article>
                        @empty
                            <div class="dashboard-empty">
                                <strong>هنوز ثبت نماینده وجود ندارد</strong>
                                <span>بعد از ثبت دریافت یا مصرف، گزارش‌ها در همین بخش نمایش داده می‌شود.</span>
                                @if ($canRecord)
                                    <x-ds.button size="sm" href="#representative-account">ثبت نماینده جدید</x-ds.button>
                                @endif
                            </div>
                        @endforelse
                    </div>
                </article>
            </div>

            <aside class="fanous-representative-sidebar">
                <article class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">خلاصه دریافت‌ها</span>
                            <h2>جزئیات حساب</h2>
                            <p>خلاصه سریع از دریافت‌ها، جریمه‌ها و مصرف‌های نماینده.</p>
                        </div>
                    </div>

                    <div class="fanous-collection-list">
                        <div>
                            <span class="fanous-record-icon is-income"><x-ds.icon name="cash" /></span>
                            <p><strong>پول ماهانه</strong><small>دریافت ماهانه محصلین</small></p>
                            <b>{{ Locale::money($totalMonthly) }}</b>
                        </div>
                        <div>
                            <span class="fanous-record-icon is-income"><x-ds.icon name="cash" /></span>
                            <p><strong>پول برق</strong><small>مصارف برق جمع‌آوری‌شده</small></p>
                            <b>{{ Locale::money($totalElectricity) }}</b>
                        </div>
                        <div>
                            <span class="fanous-record-icon is-income"><x-ds.icon name="cash" /></span>
                            <p><strong>پول آب</strong><small>هزینه آب ثبت‌شده</small></p>
                            <b>{{ Locale::money($totalWater) }}</b>
                        </div>
                        <div>
                            <span class="fanous-record-icon is-warning"><x-ds.icon name="bell" /></span>
                            <p><strong>جریمه‌ها</strong><small>جریمه یا فیس اضافی</small></p>
                            <b>{{ Locale::money($totalFines) }}</b>
                        </div>
                        <div>
                            <span class="fanous-record-icon is-expense"><x-ds.icon name="cash-minus" /></span>
                            <p><strong>مصرف</strong><small>مصارف نماینده</small></p>
                            <b>{{ Locale::money($totalExpenses) }}</b>
                        </div>
                        <div class="is-balance">
                            <span class="fanous-record-icon"><x-ds.icon name="chart" /></span>
                            <p><strong>باقی‌مانده</strong><small>درآمد منفی مصرف</small></p>
                            <b>{{ Locale::money($balance) }}</b>
                        </div>
                    </div>
                </article>

                <article class="dashboard-panel" id="representative-account">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">عملیات نماینده</span>
                            <h2>ثبت حساب نماینده</h2>
                            <p>ثبت مستقیم فقط برای نقش نماینده محصلین فعال است.</p>
                        </div>
                    </div>

                    @if ($canRecord)
                        <details class="fanous-representative-form-panel" open>
                            <summary>ثبت دریافت از محصل</summary>
                            <form method="POST" action="{{ route('representative.collections.store') }}" class="fanous-representative-form">
                                @csrf

                                <label>
                                    <span>شاگرد / محصل</span>
                                    <select class="form-control" name="dorm_student_id" required>
                                        <option value="">انتخاب شاگرد</option>
                                        @foreach ($students as $student)
                                            <option value="{{ $student->id }}">
                                                {{ $student->full_name }} · اتاق {{ $student->room?->room_number ?: ($student->room_number ?: 'ثبت نشده') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </label>

                                <label>
                                    <span>نوع پرداخت</span>
                                    <select class="form-control" name="type" required>
                                        @foreach ($incomeTypeNames as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </label>

                                <label>
                                    <span>مبلغ</span>
                                    <input class="form-control" name="amount" type="number" min="1" required>
                                </label>

                                <label>
                                    <span>تاریخ</span>
                                    <input class="form-control" name="collected_at" type="date" value="{{ now()->format('Y-m-d') }}" required>
                                </label>

                                <label>
                                    <span>دوره / ماه</span>
                                    <input class="form-control" name="period" placeholder="حمل ۱۴۰۵">
                                </label>

                                <label class="fanous-form-wide">
                                    <span>یادداشت</span>
                                    <textarea class="form-control" name="notes" rows="3" placeholder="توضیحات کوتاه"></textarea>
                                </label>

                                <x-ds.button type="submit">ذخیره دریافت</x-ds.button>
                            </form>
                        </details>

                        <details class="fanous-representative-form-panel">
                            <summary>ثبت مصرف نماینده</summary>
                            <form method="POST" action="{{ route('representative.collections.store') }}" class="fanous-representative-form">
                                @csrf
                                <input name="type" type="hidden" value="expense">

                                <label>
                                    <span>مبلغ</span>
                                    <input class="form-control" name="amount" type="number" min="1" required>
                                </label>

                                <label>
                                    <span>تاریخ</span>
                                    <input class="form-control" name="collected_at" type="date" value="{{ now()->format('Y-m-d') }}" required>
                                </label>

                                <label>
                                    <span>دوره / ماه</span>
                                    <input class="form-control" name="period" placeholder="حمل ۱۴۰۵">
                                </label>

                                <label class="fanous-form-wide">
                                    <span>توضیحات مصرف</span>
                                    <textarea class="form-control" name="notes" rows="3" placeholder="چاپ، وسایل مشترک، ترمیم کوچک و موارد مشابه"></textarea>
                                </label>

                                <x-ds.button variant="danger" type="submit">ذخیره مصرف</x-ds.button>
                            </form>
                        </details>
                    @else
                        <div class="fanous-representative-note">
                            <span class="fanous-record-icon"><x-ds.icon name="bell" /></span>
                            <p>شما این بخش را در نقش مدیریت می‌بینید. ثبت مستقیم دریافت و مصرف مخصوص حساب نماینده محصلین است.</p>
                        </div>
                        <x-ds.button :href="route('representative.report')">باز کردن گزارش نماینده</x-ds.button>
                    @endif
                </article>
            </aside>
        </section>
    </div>
@endsection
