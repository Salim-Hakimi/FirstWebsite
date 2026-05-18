@extends('admin.layout')

@section('title', 'پیگیری فیس کتابخانه - فانوس')
@section('content_wrapper_class', 'fanous-dashboard-wrapper')

@section('content')
    @php
        use App\Support\Locale;

        $statusOptions = [
            'due_soon' => 'نزدیک به سررسید',
            'overdue' => 'گذشته از سررسید',
            'all' => 'همه پرداخت‌نشده‌ها',
        ];
    @endphp

    <div class="fanous-library-page" dir="rtl">
        <section class="fanous-page-header">
            <div>
                <span class="dashboard-section-kicker">پیگیری فیس</span>
                <h1>یادآوری فیس ماهانه کتابخانه</h1>
                <p>اعضایی را که فیس ماهانه‌شان نزدیک سررسید است یا از موعد پرداخت گذشته، از همین بخش پیگیری کنید.</p>
            </div>
            <div class="fanous-page-actions">
                <x-ds.button :href="route('library.index')">بازگشت به کتابخانه</x-ds.button>
                <x-ds.button variant="outline" :href="route('library.members.export', request()->query())">خروجی CSV</x-ds.button>
            </div>
        </section>

        <section class="dashboard-stat-grid" aria-label="خلاصه پیگیری فیس">
            <article class="dashboard-stat">
                <div>
                    <span>نیازمند یادآوری</span>
                    <strong>{{ Locale::number($dueSoonCount) }}</strong>
                    <small>اعضایی که تا سه روز آینده باید پیگیری شوند</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="calendar" /></span>
            </article>
            <article class="dashboard-stat">
                <div>
                    <span>گذشته از سررسید</span>
                    <strong>{{ Locale::number($overdueCount) }}</strong>
                    <small>فیس‌هایی که موعد پرداخت‌شان گذشته است</small>
                </div>
                <span class="dashboard-stat-icon is-danger"><x-ds.icon name="bell" /></span>
            </article>
            <article class="dashboard-stat">
                <div>
                    <span>جریمه روزانه</span>
                    <strong>{{ Locale::money(20) }}</strong>
                    <small>مقدار پیش‌فرض جریمه دیرکرد</small>
                </div>
                <span class="dashboard-stat-icon is-blue"><x-ds.icon name="cash" /></span>
            </article>
        </section>

        <section class="dashboard-panel">
            <div class="dashboard-panel-header">
                <div>
                    <span class="dashboard-section-kicker">جستجو و فیلتر</span>
                    <h2>صف یادآوری اعضا</h2>
                    <p>پیام آماده را از واتساپ بفرستید و بعد از ارسال، ثبت را به عنوان پیگیری‌شده علامت بزنید.</p>
                </div>
            </div>

            <form method="GET" action="{{ route('library.fee-reminders.index') }}" class="fanous-finance-filter-grid">
                <input class="form-control" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="جستجوی نام، شماره تماس، نام پدر یا کد عضویت">
                <select class="form-control" name="status">
                    @foreach ($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="fanous-filter-actions">
                    <x-ds.button type="submit">جستجو</x-ds.button>
                    <x-ds.button variant="outline" :href="route('library.fee-reminders.index')">پاک کردن</x-ds.button>
                </div>
            </form>

            <div class="fanous-library-member-grid mt-4">
                @forelse ($members as $member)
                    @php
                        $fine = max((int) $member->monthly_fee_fine_amount, $member->calculatedMonthlyFine());
                        $balance = (int) $member->membership_fee + $fine;
                        $isOverdue = $member->next_payment_due_at && $member->next_payment_due_at->isPast();
                        $dueDate = $member->next_payment_due_at?->format('Y/m/d');
                        $dueDateText = $dueDate ? Locale::number($dueDate) : 'ثبت نشده';
                        $message = $isOverdue
                            ? "سلام {$member->full_name} عزیز، تاریخ پرداخت فیس ماهانه کتابخانه شما گذشته است. مبلغ قابل پرداخت فعلی ".Locale::money($balance)." است که شامل ".Locale::money($fine)." جریمه می‌باشد."
                            : "سلام {$member->full_name} عزیز، فیس ماهانه کتابخانه شما تا تاریخ {$dueDateText} باید پرداخت شود. در صورت تأخیر، روزانه ".Locale::money((int) $member->monthly_fee_daily_fine)." جریمه می‌شود.";
                        $whatsappDigits = preg_replace('/\D+/', '', $member->phone ?? '');
                        if (str_starts_with($whatsappDigits, '0')) {
                            $whatsappDigits = '93'.substr($whatsappDigits, 1);
                        }
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
                                <span class="ltr-text">{{ $member->member_code ?: 'بدون کد' }}</span>
                            </div>
                            <x-ds.badge :tone="$isOverdue ? 'danger' : 'warning'">{{ $isOverdue ? 'گذشته از سررسید' : 'نیازمند یادآوری' }}</x-ds.badge>
                        </div>

                        <div class="fanous-library-member-meta">
                            <div><span>تماس</span><strong class="ltr-text">{{ $member->phone ?: 'ثبت نشده' }}</strong></div>
                            <div><span>سررسید بعدی</span><strong>{{ $dueDateText }}</strong></div>
                            <div><span>فیس ماهانه</span><strong>{{ Locale::money((int) $member->membership_fee) }}</strong></div>
                            <div><span>جریمه</span><strong>{{ Locale::money($fine) }}</strong></div>
                            <div><span>قابل پرداخت</span><strong>{{ Locale::money($balance) }}</strong></div>
                            <div><span>آخرین یادآوری</span><strong>{{ $member->last_fee_reminder_at ? Locale::number($member->last_fee_reminder_at->format('Y/m/d')) : 'ارسال نشده' }}</strong></div>
                        </div>

                        <div class="fanous-library-notice">
                            <span>پیام</span>
                            <p>{{ $message }}</p>
                        </div>

                        <div class="fanous-library-member-actions">
                            @if ($whatsappDigits)
                                <x-ds.button size="sm" href="https://wa.me/{{ $whatsappDigits }}?text={{ rawurlencode($message) }}" target="_blank" rel="noopener">واتساپ</x-ds.button>
                            @endif
                            <x-ds.button variant="outline" size="sm" :href="route('library.members.show', $member)">پروفایل</x-ds.button>
                            @if ($canWriteLibrary)
                                <form method="POST" action="{{ route('library.members.fee-reminder.store', $member) }}">
                                    @csrf
                                    <x-ds.button variant="outline" size="sm" type="submit">ثبت ارسال</x-ds.button>
                                </form>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="dashboard-empty">برای این فیلتر هیچ عضوی نیازمند یادآوری فیس نیست.</div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
