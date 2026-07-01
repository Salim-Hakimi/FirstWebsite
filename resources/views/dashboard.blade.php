@extends('admin.layout')

@section('title', 'داشبورد - فانوس')
@section('content_wrapper_class', 'fanous-dashboard-wrapper')

@section('content')
    @php
        use App\Models\User;
        use App\Support\Locale;

        $isLibrarianDashboard = auth()->user()?->role === User::ROLE_LIBRARIAN && $libraryDashboard;
    @endphp

    @if ($isLibrarianDashboard)
        <div class="fanous-dashboard fanous-librarian-dashboard" dir="rtl">
            <section class="fanous-page-header">
                <div>
                    <span class="dashboard-section-kicker">داشبورد کتابخانه</span>
                    <h1>کتابدار</h1>
                    <p>خلاصه کارهای امروز کتابخانه، امانت‌ها، اعضا، پیگیری فیس و وضعیت مالی کتابخانه را از این‌جا مدیریت کنید.</p>
                </div>
                <div class="fanous-page-actions">
                    <x-ds.button :href="route('library.index')">ورود به کتابخانه</x-ds.button>
                    <x-ds.button variant="outline" :href="route('settings.edit')">تنظیمات حساب</x-ds.button>
                </div>
            </section>

            <section class="dashboard-stat-grid" aria-label="خلاصه کتابخانه">
                <article class="dashboard-stat">
                    <div>
                        <span>اعضای فعال</span>
                        <strong>{{ Locale::number($libraryDashboard['activeMembers']) }}</strong>
                        <small>اعضایی که حساب فعال دارند</small>
                    </div>
                    <span class="dashboard-stat-icon"><x-ds.icon name="users" /></span>
                </article>

                <article class="dashboard-stat">
                    <div>
                        <span>کتاب‌ها</span>
                        <strong>{{ Locale::number($libraryDashboard['bookTitles']) }}</strong>
                        <small>{{ Locale::number($libraryDashboard['availableCopies']) }} نسخه آماده امانت</small>
                    </div>
                    <span class="dashboard-stat-icon"><x-ds.icon name="books" /></span>
                </article>

                <article class="dashboard-stat">
                    <div>
                        <span>امانت‌های فعال</span>
                        <strong>{{ Locale::number($libraryDashboard['activeLoans']) }}</strong>
                        <small>کتاب‌هایی که هنوز برگشت نشده‌اند</small>
                    </div>
                    <span class="dashboard-stat-icon is-blue"><x-ds.icon name="book" /></span>
                </article>

                <article class="dashboard-stat">
                    <div>
                        <span>درآمد امروز</span>
                        <strong>{{ Locale::money($libraryDashboard['todayIncome']) }}</strong>
                        <small>فیس، کارت و دریافت‌های کتابخانه</small>
                    </div>
                    <span class="dashboard-stat-icon is-purple"><x-ds.icon name="cash" /></span>
                </article>
            </section>

            <section class="dashboard-panel">
                <div data-vue-app="dashboard-summary" data-title="خلاصه زنده کتابخانه" data-endpoint="{{ route('api.dashboard.summary') }}"></div>
            </section>

            <section class="dashboard-panel">
                <div class="dashboard-panel-header">
                    <div>
                        <span class="dashboard-section-kicker">دسترسی سریع</span>
                        <h2>کارهای روزانه کتابدار</h2>
                        <p>برای ثبت عضو، افزودن کتاب، ثبت امانت، برگشت کتاب یا ثبت مالی کتابخانه مستقیم وارد بخش مربوط شوید.</p>
                    </div>
                </div>

                <div class="dashboard-quick-grid">
                    <a class="dashboard-action" href="{{ route('library.index') }}#new-library-member">
                        <span class="dashboard-action-icon"><x-ds.icon name="user" /></span>
                        <strong>ثبت عضو جدید</strong>
                        <small>پروفایل، فیس و کارت کتابخانه</small>
                    </a>
                    <a class="dashboard-action" href="{{ route('library.index') }}#new-library-book">
                        <span class="dashboard-action-icon"><x-ds.icon name="book" /></span>
                        <strong>افزودن کتاب</strong>
                        <small>عنوان، نویسنده و نسخه‌ها</small>
                    </a>
                    <a class="dashboard-action" href="{{ route('library.index') }}#new-library-loan">
                        <span class="dashboard-action-icon"><x-ds.icon name="books" /></span>
                        <strong>ثبت امانت</strong>
                        <small>عضو، کتاب و تاریخ برگشت</small>
                    </a>
                    <a class="dashboard-action" href="{{ route('library.index') }}#return-library-copy">
                        <span class="dashboard-action-icon"><x-ds.icon name="edit" /></span>
                        <strong>برگشت کتاب</strong>
                        <small>اسکن یا ثبت کد نسخه</small>
                    </a>
                    <a class="dashboard-action" href="{{ route('library.index') }}#library-finance-record">
                        <span class="dashboard-action-icon"><x-ds.icon name="cash" /></span>
                        <strong>ثبت مالی کتابخانه</strong>
                        <small>درآمد یا مصرف کتابخانه</small>
                    </a>
                    <a class="dashboard-action" href="{{ route('library.inventory.report') }}">
                        <span class="dashboard-action-icon"><x-ds.icon name="report" /></span>
                        <strong>گزارش موجودی</strong>
                        <small>نسخه‌ها و وضعیت کتاب‌ها</small>
                    </a>
                </div>
            </section>

            <section class="dashboard-main-grid">
                <article class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">پیگیری فوری</span>
                            <h2>امانت‌های دیرشده</h2>
                            <p>کتاب‌هایی که از تاریخ برگشت‌شان گذشته و نیاز به پیگیری دارند.</p>
                        </div>
                    </div>

                    <div class="dashboard-activity-list">
                        @forelse ($libraryDashboard['overdueLoans'] as $loan)
                            <div class="dashboard-activity">
                                <span class="dashboard-activity-icon"><x-ds.icon name="bell" /></span>
                                <div>
                                    <strong>{{ $loan->member?->full_name ?: 'عضو نامشخص' }}</strong>
                                    <small>{{ $loan->book?->title ?: 'کتاب نامشخص' }} · برگشت: {{ $loan->due_at ? Locale::number($loan->due_at->format('Y/m/d')) : 'ندارد' }}</small>
                                </div>
                                @if ($loan->member)
                                    <x-ds.button variant="outline" size="sm" :href="route('library.members.show', $loan->member)">پروفایل</x-ds.button>
                                @endif
                            </div>
                        @empty
                            <div class="dashboard-empty">فعلاً امانت دیرشده وجود ندارد.</div>
                        @endforelse
                    </div>
                </article>

                <article class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">فیس و کارت</span>
                            <h2>اعضای نیازمند پیگیری</h2>
                            <p>اعضایی که سررسید فیس یا اعتبار کارت‌شان نزدیک است.</p>
                        </div>
                    </div>

                    <div class="dashboard-activity-list">
                        @forelse ($libraryDashboard['feeFollowUps'] as $member)
                            <div class="dashboard-activity">
                                <span class="dashboard-activity-icon"><x-ds.icon name="calendar" /></span>
                                <div>
                                    <strong>{{ $member->full_name }}</strong>
                                    <small>سررسید: {{ $member->next_payment_due_at ? Locale::number($member->next_payment_due_at->format('Y/m/d')) : 'ندارد' }} · باقی: {{ Locale::money((int) $member->monthlyFeeBalance()) }}</small>
                                </div>
                                <x-ds.button variant="outline" size="sm" :href="route('library.members.show', $member)">پیگیری</x-ds.button>
                            </div>
                        @empty
                            <div class="dashboard-empty">فعلاً عضو نیازمند پیگیری وجود ندارد.</div>
                        @endforelse
                    </div>
                </article>
            </section>

            <section class="dashboard-lower-grid">
                <article class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">آخرین امانت‌ها</span>
                            <h2>جریان کار کتابخانه</h2>
                        </div>
                        <x-ds.button variant="outline" size="sm" :href="route('library.index')">مشاهده همه</x-ds.button>
                    </div>

                    <div class="dashboard-activity-list">
                        @forelse ($libraryDashboard['recentLoans'] as $loan)
                            <div class="dashboard-activity">
                                <span class="dashboard-activity-icon"><x-ds.icon name="book" /></span>
                                <div>
                                    <strong>{{ $loan->member?->full_name ?: 'عضو نامشخص' }}</strong>
                                    <small>{{ $loan->book?->title ?: 'کتاب نامشخص' }} · وضعیت: {{ $loan->status === 'returned' ? 'برگشت‌شده' : 'در امانت' }}</small>
                                </div>
                            </div>
                        @empty
                            <div class="dashboard-empty">هنوز امانتی ثبت نشده است.</div>
                        @endforelse
                    </div>
                </article>

                <article class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">مالی کتابخانه</span>
                            <h2>خلاصه حساب</h2>
                            <p>ثبت‌های مالی کتابخانه از فیس، کارت و ثبت‌های دستی کتابدار خوانده می‌شود.</p>
                        </div>
                    </div>

                    <div class="dashboard-payment-list">
                        <div class="dashboard-payment-item">
                            <span>کل درآمد</span>
                            <strong>{{ Locale::money($libraryDashboard['totalIncome']) }}</strong>
                        </div>
                        <div class="dashboard-payment-item">
                            <span>کل مصرف</span>
                            <strong>{{ Locale::money($libraryDashboard['totalExpense']) }}</strong>
                        </div>
                    </div>
                    <div class="dashboard-payment-total">
                        <span>توازن کتابخانه</span>
                        <strong>{{ Locale::money($libraryDashboard['totalIncome'] - $libraryDashboard['totalExpense']) }}</strong>
                    </div>
                </article>
            </section>
        </div>
    @else
        <section class="student-command-shell">
            <div class="student-command-copy">
                <span class="student-command-kicker">محیط کاری فانوس</span>
                <h1>{{ $roleLabel }}</h1>
                <p>یکی از بخش‌های قابل دسترس برای نقش حساب خود را انتخاب کنید.</p>
            </div>
            <div class="student-command-actions">
                <a class="btn btn-outline-secondary" href="{{ route('settings.edit') }}">تنظیمات حساب</a>
            </div>
        </section>

        <section class="student-workspace-panel">
            <div data-vue-app="dashboard-summary" data-title="خلاصه زنده دسترسی‌ها" data-endpoint="{{ route('api.dashboard.summary') }}"></div>
        </section>

        <section class="student-workspace-panel">
            <div class="student-panel-head">
                <div>
                    <span class="student-panel-label">دسترسی</span>
                    <h2>بخش‌های کاری شما</h2>
                    <p>فقط بخش‌هایی که برای نقش شما مجاز است اینجا نمایش داده می‌شود.</p>
                </div>
            </div>

            <div class="admin-shortcut-grid">
                @forelse ($cards as $card)
                    @if (! empty($card['url']))
                        <a href="{{ $card['url'] }}">
                            <span>{{ mb_substr($card['title'], 0, 1) }}</span>
                            <strong>{{ $card['title'] }}</strong>
                            <em>{{ $card['body'] }}</em>
                        </a>
                    @else
                        <div class="student-directory-empty">
                            <strong>{{ $card['title'] }}</strong>
                            <p>{{ $card['body'] }}</p>
                        </div>
                    @endif
                @empty
                    <div class="student-directory-empty">
                        <strong>هیچ بخشی در دسترس نیست</strong>
                        <p>برای بررسی دسترسی نقش خود با ادمین تماس بگیرید.</p>
                    </div>
                @endforelse
            </div>
        </section>
    @endif
@endsection
