@extends('admin.layout')

@section('title', 'گزارش نماینده - ادمین فانوس')
@section('content_wrapper_class', 'fanous-dashboard-wrapper')

@section('content')
    @php
        use App\Support\Locale;

        $typeNames = [
            'monthly_fee' => 'پول ماهانه',
            'electricity' => 'پول برق',
            'fine' => 'جریمه',
            'water' => 'پول آب',
            'expense' => 'مصرف نماینده',
        ];
        $groupNames = [
            'daily' => 'روزانه',
            'weekly' => 'هفته‌وار',
            'monthly' => 'ماهانه',
        ];
    @endphp

    <div class="fanous-representative-page" dir="rtl">
        <section class="fanous-page-header">
            <div>
                <span class="dashboard-section-kicker">گزارش نماینده</span>
                <h1>گزارش حساب نماینده محصلین</h1>
                <p>دریافت‌ها، مصارف، باقی‌مانده، دوره‌ها و ثبت‌کننده‌ها را به شکل منظم بررسی کنید.</p>
            </div>
            <div class="fanous-page-actions">
                <x-ds.button variant="outline" :href="route('representative.index')">بازگشت</x-ds.button>
                <x-ds.button onclick="window.print()">چاپ گزارش</x-ds.button>
            </div>
        </section>

        <section class="dashboard-stat-grid">
            <article class="dashboard-stat">
                <div>
                    <span>کل دریافتی</span>
                    <strong>{{ Locale::money($totalIncome) }}</strong>
                    <small>مجموع پول ثبت‌شده از محصلین</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="cash" /></span>
            </article>
            <article class="dashboard-stat">
                <div>
                    <span>کل مصرف</span>
                    <strong>{{ Locale::money($totalExpenses) }}</strong>
                    <small>مصارف ثبت‌شده توسط نماینده</small>
                </div>
                <span class="dashboard-stat-icon is-danger"><x-ds.icon name="cash-minus" /></span>
            </article>
            <article class="dashboard-stat">
                <div>
                    <span>باقی‌مانده</span>
                    <strong>{{ Locale::money($balance) }}</strong>
                    <small>دریافت منهای مصرف</small>
                </div>
                <span class="dashboard-stat-icon is-blue"><x-ds.icon name="chart" /></span>
            </article>
        </section>

        <section class="fanous-representative-layout">
            <aside class="fanous-representative-sidebar">
                <article class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">فیلتر</span>
                            <h2>فیلتر گزارش</h2>
                            <p>گزارش را بر اساس تاریخ، نوع ثبت، دوره یا گروه‌بندی محدود کنید.</p>
                        </div>
                    </div>

                    <form method="GET" action="{{ route('representative.report') }}" class="fanous-representative-form">
                        <label>
                            <span>از تاریخ</span>
                            <input class="form-control" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}">
                        </label>
                        <label>
                            <span>تا تاریخ</span>
                            <input class="form-control" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}">
                        </label>
                        <label>
                            <span>نوع ثبت</span>
                            <select class="form-control" name="type">
                                <option value="">همه نوع‌ها</option>
                                @foreach ($typeNames as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['type'] ?? '') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span>گروه‌بندی</span>
                            <select class="form-control" name="group">
                                @foreach ($groupNames as $value => $label)
                                    <option value="{{ $value }}" @selected($group === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="fanous-form-wide">
                            <span>دوره / ماه</span>
                            <input class="form-control" name="period" value="{{ $filters['period'] ?? '' }}" placeholder="مثلاً حمل ۱۴۰۵">
                        </label>
                        <x-ds.button type="submit">نمایش گزارش</x-ds.button>
                        <x-ds.button variant="outline" :href="route('representative.report')">پاک کردن</x-ds.button>
                    </form>
                </article>

                <article class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">نوع‌ها</span>
                            <h2>جمع‌بندی بر اساس نوع</h2>
                        </div>
                    </div>
                    <div class="fanous-collection-list">
                        @foreach ($typeNames as $value => $label)
                            <div>
                                <span class="fanous-record-icon {{ $value === 'expense' ? 'is-expense' : 'is-income' }}">
                                    <x-ds.icon :name="$value === 'expense' ? 'cash-minus' : 'cash'" />
                                </span>
                                <p><strong>{{ $label }}</strong><small>{{ Locale::number((int) $records->where('type', $value)->count()) }} ثبت</small></p>
                                <b>{{ Locale::money((int) ($totalsByType[$value] ?? 0)) }}</b>
                            </div>
                        @endforeach
                    </div>
                </article>
            </aside>

            <div class="fanous-representative-main">
                <article class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">{{ $groupNames[$group] ?? 'روزانه' }}</span>
                            <h2>خلاصه گروه‌بندی‌شده</h2>
                        </div>
                    </div>
                    <div class="fanous-table-wrap">
                        <table class="fanous-finance-table">
                            <thead>
                                <tr>
                                    <th>دوره</th>
                                    <th>دریافت</th>
                                    <th>مصرف</th>
                                    <th>باقی‌مانده</th>
                                    <th>تعداد ثبت</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($summaryRows as $row)
                                    <tr>
                                        <td>{{ Locale::number($row['label']) }}</td>
                                        <td>{{ Locale::money($row['income']) }}</td>
                                        <td>{{ Locale::money($row['expense']) }}</td>
                                        <td>{{ Locale::money($row['balance']) }}</td>
                                        <td>{{ Locale::number($row['count']) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5"><div class="dashboard-empty">در این محدوده هیچ ثبت وجود ندارد.</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">جزئیات</span>
                            <h2>جزئیات ثبت‌های نماینده</h2>
                        </div>
                    </div>
                    <div class="fanous-table-wrap">
                        <table class="fanous-finance-table">
                            <thead>
                                <tr>
                                    <th>تاریخ</th>
                                    <th>محصل / منبع</th>
                                    <th>نوع</th>
                                    <th>دوره</th>
                                    <th>مبلغ</th>
                                    <th>ثبت‌کننده</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($records as $record)
                                    <tr>
                                        <td>{{ $record->collected_at ? Locale::number($record->collected_at->format('Y/m/d')) : 'ثبت نشده' }}</td>
                                        <td>
                                            <strong>{{ $record->student?->full_name ?: 'مصرف عمومی نماینده' }}</strong>
                                            <small>{{ $record->notes ?: 'یادداشت ندارد' }}</small>
                                        </td>
                                        <td>{{ $typeNames[$record->type] ?? $record->type }}</td>
                                        <td>{{ $record->period ?: 'ندارد' }}</td>
                                        <td>{{ Locale::money($record->amount) }}</td>
                                        <td>{{ $record->recordedBy?->name ?: 'نامعلوم' }}</td>
                                        <td>
                                            @if ($record->student)
                                                <x-ds.button variant="outline" size="sm" :href="route('dorm.students.show', $record->student)">پروفایل</x-ds.button>
                                            @else
                                                <span class="fanous-locked-label">عمومی</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7"><div class="dashboard-empty">در این محدوده هیچ ثبت وجود ندارد.</div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            </div>
        </section>
    </div>
@endsection
