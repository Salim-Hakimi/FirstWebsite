@extends('admin.layout')

@section('title', 'داشبورد ادمین فانوس')
@section('content_wrapper_class', 'fanous-dashboard-wrapper')

@section('content')
    @php
        use App\Support\Locale;

        $followUpTotal = $waitingStudents + $onHoldStudents + $pendingUsers + $overdueLoans->count();
        $today = now();

        $studentStatusLabels = [
            'active' => 'فعال',
            'waiting' => 'در انتظار',
            'on_hold' => 'متوقف',
            'graduated' => 'فارغ شده',
            'left' => 'خارج شده',
        ];

        $roleLabels = $roleLabels ?? [];
        $statusLabels = $statusLabels ?? [];

        $formatDate = fn ($date) => $date ? Locale::number($date->format('Y/m/d')) : 'ثبت نشده';
    @endphp

    <div class="fanous-dashboard" dir="rtl">
        <section class="dashboard-hero-grid">
            <article class="dashboard-date-card">
                <span class="dashboard-date-icon"><x-ds.icon name="calendar" /></span>
                <div>
                    <strong>{{ $today->locale('fa')->translatedFormat('l') }}، {{ Locale::number($today->format('Y/m/d')) }}</strong>
                    <span>نیازمند پیگیری: {{ Locale::number($followUpTotal) }} مورد</span>
                </div>
            </article>

            <article class="dashboard-welcome">
                <div>
                <h1>داشبورد مدیریت لیلیه و کتابخانه فانوس</h1>
                <p>
                    در اینجا خلاصه‌ای از وضعیت عمومی سیستم لیلیه و کتابخانه را مشاهده می‌کنید.
                </p>
                </div>
                <span class="dashboard-welcome-chip">خلاصه امروز</span>
            </article>
        </section>

        <section class="dashboard-stat-grid" aria-label="آمارهای اصلی داشبورد">
            <article class="dashboard-stat">
                <div>
                    <span>شاگردان فعال</span>
                    <strong>{{ Locale::number($activeStudents) }}</strong>
                    <small>{{ Locale::number($waitingStudents) }} در انتظار، {{ Locale::number($onHoldStudents) }} متوقف</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="users" /></span>
            </article>

            <article class="dashboard-stat">
                <div>
                    <span>اتاق‌ها</span>
                    <strong>{{ Locale::number($totalRooms) }}</strong>
                    <small>ظرفیت و وضعیت فعلی لیلیه</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="building" /></span>
            </article>

            <article class="dashboard-stat">
                <div>
                    <span>تخت‌ها</span>
                    <strong>{{ Locale::number($totalBeds) }}</strong>
                    <small>{{ Locale::number($freeBeds) }} خالی، {{ Locale::number($occupiedBeds) }} اشغال‌شده</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="bed" /></span>
            </article>

            <article class="dashboard-stat">
                <div>
                    <span>کتاب‌ها</span>
                    <strong>{{ Locale::number($bookTitles) }}</strong>
                    <small>{{ Locale::number($availableBooks) }} نسخه موجود</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="books" /></span>
            </article>
        </section>

        <section class="dashboard-main-grid">
            <article class="dashboard-panel">
                <div class="dashboard-panel-header">
                    <div>
                        <span class="dashboard-section-kicker">وضعیت اتاق‌ها و تخت‌ها</span>
                        <h2>نمای اشغال لیلیه</h2>
                        <p>ظرفیت کل، تخت‌های خالی و تخت‌های اشغال‌شده به صورت زنده از ثبت‌های اتاق خوانده می‌شود.</p>
                    </div>
                    <x-ds.button variant="outline" size="sm" :href="route('dorm.rooms.index')">مدیریت اتاق‌ها</x-ds.button>
                </div>

                <div class="dashboard-room-layout">
                    <div>
                        <div class="dashboard-donut" style="--value: {{ max(0, min(100, $occupancyRate)) }}">
                            <div>
                                <strong>{{ Locale::percent($occupancyRate) }}</strong>
                                <span>درصد استفاده</span>
                            </div>
                        </div>
                        <div class="dashboard-progress" aria-hidden="true">
                            <span style="width: {{ max(0, min(100, $occupancyRate)) }}%"></span>
                        </div>
                    </div>

                    <div class="dashboard-metric-list">
                        <div class="dashboard-metric-item">
                            <span>کل اتاق‌ها</span>
                            <strong>{{ Locale::number($totalRooms) }}</strong>
                        </div>
                        <div class="dashboard-metric-item">
                            <span>کل تخت‌ها</span>
                            <strong>{{ Locale::number($totalBeds) }}</strong>
                        </div>
                        <div class="dashboard-metric-item">
                            <span>تخت‌های اشغال‌شده</span>
                            <strong>{{ Locale::number($occupiedBeds) }}</strong>
                        </div>
                        <div class="dashboard-metric-item">
                            <span>تخت‌های خالی</span>
                            <strong>{{ Locale::number($freeBeds) }}</strong>
                        </div>
                    </div>
                </div>
            </article>

            <article class="dashboard-panel">
                <div class="dashboard-panel-header">
                    <div>
                        <span class="dashboard-section-kicker">خلاصه پرداخت ثبت‌نام</span>
                        <h2>درآمد ثبت‌نام این ماه</h2>
                        <p>ضمانت فقط امانت شاگرد است و در درآمد لیلیه حساب نمی‌شود؛ فقط فیس مصارف لیلیه و فیس کارت درآمد است.</p>
                    </div>
                    <x-ds.badge tone="success">{{ Locale::number($monthlyRegistrationCount) }} پرداخت</x-ds.badge>
                </div>

                <div class="dashboard-payment-list">
                    <div class="dashboard-payment-item">
                        <span>ضمانت</span>
                        <strong>{{ Locale::money($monthlyGuaranteeDeposits) }}</strong>
                    </div>
                    <div class="dashboard-payment-item">
                        <span>فیس مصارف لیلیه</span>
                        <strong>{{ Locale::money($monthlyDormRegistrationFees) }}</strong>
                    </div>
                    <div class="dashboard-payment-item">
                        <span>فیس کارت</span>
                        <strong>{{ Locale::money($monthlyDormCardFees) }}</strong>
                    </div>
                </div>

                <div class="dashboard-payment-total">
                    <span>درآمد واقعی ثبت‌نام</span>
                    <strong>{{ Locale::money($monthlyRegistrationIncome) }}</strong>
                </div>
            </article>
        </section>

        <section class="dashboard-panel">
            <div class="dashboard-panel-header">
                <div>
                    <span class="dashboard-section-kicker">دسترسی سریع</span>
                    <h2>کارهای پرکاربرد</h2>
                    <p>برای کارهای روزانه بدون جستجو در منو از این میانبرها استفاده کنید.</p>
                </div>
            </div>

            <div class="dashboard-quick-grid">
                <a class="dashboard-action" href="{{ route('dorm.students.create') }}">
                    <span class="dashboard-action-icon"><x-ds.icon name="user" /></span>
                    <span>
                        <strong>ثبت شاگرد جدید</strong>
                        <em>پروفایل، اتاق و پرداخت ثبت‌نام</em>
                    </span>
                </a>

                <a class="dashboard-action" href="{{ route('dorm.rooms.create') }}">
                    <span class="dashboard-action-icon"><x-ds.icon name="building" /></span>
                    <span>
                        <strong>افزودن اتاق</strong>
                        <em>ظرفیت، منزل و وضعیت اتاق</em>
                    </span>
                </a>

                <a class="dashboard-action" href="{{ route('library.index') }}#new-library-book">
                    <span class="dashboard-action-icon"><x-ds.icon name="book" /></span>
                    <span>
                        <strong>افزودن کتاب</strong>
                        <em>عنوان، نویسنده و نسخه‌ها</em>
                    </span>
                </a>

                <a class="dashboard-action" href="{{ route('admin.finance.index') }}">
                    <span class="dashboard-action-icon"><x-ds.icon name="cash" /></span>
                    <span>
                        <strong>ثبت پرداخت</strong>
                        <em>درآمد، مصرف و گزارش مالی</em>
                    </span>
                </a>
            </div>
        </section>

        <section class="dashboard-lower-grid">
            <article class="dashboard-panel">
                <div class="dashboard-panel-header">
                    <div>
                        <span class="dashboard-section-kicker">فعالیت‌های اخیر</span>
                        <h2>آخرین ثبت‌های سیستم</h2>
                        <p>شاگردان تازه ثبت‌شده و حساب‌های کاربری اخیر برای بررسی سریع.</p>
                    </div>
                    <x-ds.button variant="outline" size="sm" :href="route('dorm.students.index')">همه شاگردان</x-ds.button>
                </div>

                <div class="dashboard-activity-list">
                    @forelse ($recentStudents as $student)
                        @php
                            $studentStatus = $studentStatusLabels[$student->status] ?? $student->status;
                        @endphp
                        <div class="dashboard-activity">
                            <span class="dashboard-activity-icon"><x-ds.icon name="user" /></span>
                            <div>
                                <strong>{{ $student->full_name }}</strong>
                                <p>{{ $studentStatus }} · {{ $student->education_place ?: 'محل تحصیل ثبت نشده' }}</p>
                            </div>
                            <x-ds.button variant="outline" size="sm" :href="route('dorm.students.show', $student)">پروفایل</x-ds.button>
                        </div>
                    @empty
                        <div class="dashboard-empty">هنوز شاگردی ثبت نشده است.</div>
                    @endforelse

                    @foreach ($recentUsers->take(2) as $user)
                        <div class="dashboard-activity">
                            <span class="dashboard-activity-icon"><x-ds.icon name="users" /></span>
                            <div>
                                <strong>{{ $user->name }}</strong>
                                <p>{{ $roleLabels[$user->role] ?? $user->role }} · {{ $statusLabels[$user->status] ?? $user->status }}</p>
                            </div>
                            <x-ds.button variant="outline" size="sm" :href="route('admin.users.edit', $user)">ویرایش</x-ds.button>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="dashboard-panel">
                <div class="dashboard-panel-header">
                    <div>
                        <span class="dashboard-section-kicker">لیست انتظار</span>
                        <h2>محصلین در انتظار پذیرش</h2>
                        <p>{{ Locale::number($waitingStudents) }} در انتظار، {{ Locale::number($onHoldStudents) }} پرونده ناقص یا متوقف.</p>
                    </div>
                    <x-ds.button variant="outline" size="sm" :href="route('dorm.students.index', ['status' => 'waiting'])">نمایش صف</x-ds.button>
                </div>

                <div class="dashboard-activity-list">
                    @forelse ($waitingApplicants as $applicant)
                        @php
                            $applicantStatus = $studentStatusLabels[$applicant->status] ?? $applicant->status;
                        @endphp
                        <div class="dashboard-activity">
                            <span class="dashboard-activity-icon"><x-ds.icon name="user" /></span>
                            <div>
                                <strong>{{ $applicant->full_name }}</strong>
                                <p>{{ $applicantStatus }} · تاریخ درخواست {{ $formatDate($applicant->application_date) }}</p>
                            </div>
                            <x-ds.button variant="outline" size="sm" :href="route('dorm.students.edit', $applicant)">بررسی</x-ds.button>
                        </div>
                    @empty
                        <div class="dashboard-empty">فعلاً هیچ محصلی در لیست انتظار نیست.</div>
                    @endforelse
                </div>
            </article>
        </section>

    </div>
@endsection
