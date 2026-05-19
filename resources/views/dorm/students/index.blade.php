@extends('admin.layout')

@section('title', 'شاگردان لیلیه - ادمین فانوس')
@section('content_wrapper_class', 'fanous-dashboard-wrapper')

@section('content')
    @php
        use App\Support\Locale;

        $statusNames = [
            'active' => 'فعال',
            'waiting' => 'در انتظار',
            'on_hold' => 'ناقص',
            'rejected' => 'رد شده',
            'suspended' => 'مسدود',
            'graduated' => 'فارغ شده',
            'left' => 'خارج شده',
        ];

        $statusTones = [
            'active' => 'success',
            'waiting' => 'warning',
            'on_hold' => 'primary',
            'rejected' => 'danger',
            'suspended' => 'danger',
            'graduated' => 'primary',
            'left' => 'danger',
        ];

        $activeCount = $students->where('status', 'active')->count();
        $waitingCount = $students->where('status', 'waiting')->count();
        $onHoldCount = $students->where('status', 'on_hold')->count();
        $missingDocumentsCount = $students->filter(fn ($student) => count($student->document_names ?? []) === 0)->count();
        $recentRegistrationCount = $students->filter(fn ($student) => $student->created_at?->greaterThanOrEqualTo(now()->subDays(30)))->count();

        $rooms = $students
            ->map(fn ($student) => $student->room?->room_number ?: $student->room_number)
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $roomFilter = request('room');
        $dateFilter = request('date');
        $visibleStudents = $students
            ->when($roomFilter, fn ($items) => $items->filter(fn ($student) => (string) ($student->room?->room_number ?: $student->room_number) === (string) $roomFilter))
            ->when($dateFilter, fn ($items) => $items->filter(fn ($student) => $student->created_at?->toDateString() === $dateFilter || $student->application_date?->toDateString() === $dateFilter))
            ->values();
    @endphp

    <div class="fanous-students-page" dir="rtl">
        <section class="fanous-page-header">
            <div>
                <span class="dashboard-section-kicker">مدیریت لیلیه</span>
                <h1>شاگردان لیلیه</h1>
                <p>حساب‌ها، اتاق‌ها، پرداخت‌ها و وضعیت شاگردان لیلیه را مدیریت کنید.</p>
            </div>

            <div class="fanous-page-actions">
                <x-ds.button variant="outline" href="#student-filters">فیلتر شاگردان</x-ds.button>
                @if (auth()->user()->canAccessAdmin())
                    <x-ds.button :href="route('dorm.students.create')">
                        <span aria-hidden="true">+</span>
                        ثبت شاگرد جدید
                    </x-ds.button>
                @endif
            </div>
        </section>

        <section class="dashboard-stat-grid" aria-label="خلاصه شاگردان">
            <article class="dashboard-stat">
                <div>
                    <span>شاگردان فعال</span>
                    <strong>{{ Locale::number($activeCount) }}</strong>
                    <small>شاگردان دارای وضعیت فعال</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="users" /></span>
            </article>

            <article class="dashboard-stat">
                <div>
                    <span>منتظر بررسی</span>
                    <strong>{{ Locale::number($waitingCount) }}</strong>
                    <small>{{ Locale::number($onHoldCount) }} پرونده ناقص یا متوقف</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="calendar" /></span>
            </article>

            <article class="dashboard-stat">
                <div>
                    <span>اسناد ناقص</span>
                    <strong>{{ Locale::number($missingDocumentsCount) }}</strong>
                    <small>پرونده‌هایی که سند ثبت‌شده ندارند</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="logs" /></span>
            </article>

            <article class="dashboard-stat">
                <div>
                    <span>ثبت‌نام‌های اخیر</span>
                    <strong>{{ Locale::number($recentRegistrationCount) }}</strong>
                    <small>ثبت‌های قابل نمایش در ماه اخیر</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="plus" /></span>
            </article>
        </section>

        @if (auth()->user()->canAccessAdmin())
            <section class="dashboard-panel">
                <div class="dashboard-panel-header">
                    <div>
                        <span class="dashboard-section-kicker">لیست انتظار</span>
                        <h2>درخواست‌های نیازمند بررسی</h2>
                        <p>فقط شاگردانی که وضعیت‌شان در انتظار یا ناقص است در این بخش نمایش داده می‌شوند.</p>
                    </div>
                    <x-ds.button variant="outline" size="sm" :href="route('dorm.students.index', ['status' => 'waiting'])">نمایش صف</x-ds.button>
                </div>

                <div class="fanous-waiting-list">
                    @forelse ($waitingApplicants->take(6) as $applicant)
                        @php
                            $tone = $statusTones[$applicant->status] ?? 'primary';
                        @endphp
                        <article class="fanous-waiting-card">
                            <div class="fanous-waiting-main">
                                @if ($applicant->profile_photo_path)
                                    <img class="fanous-student-avatar" src="{{ asset('storage/'.$applicant->profile_photo_path) }}" alt="{{ $applicant->full_name }}">
                                @else
                                    <span class="fanous-student-avatar">{{ mb_substr($applicant->full_name, 0, 1) }}</span>
                                @endif
                                <div>
                                    <strong>{{ $applicant->full_name }}</strong>
                                    <span class="ltr-text">{{ $applicant->phone }}</span>
                                    <small>تاریخ درخواست: {{ $applicant->application_date ? Locale::number($applicant->application_date->format('Y/m/d')) : 'ثبت نشده' }}</small>
                                </div>
                            </div>

                            <x-ds.badge :tone="$tone">{{ $statusNames[$applicant->status] ?? $applicant->status }}</x-ds.badge>

                            <form class="fanous-waiting-actions" method="POST" action="{{ route('dorm.students.admit', $applicant) }}">
                                @csrf
                                @method('PUT')
                                <select class="form-control" name="dorm_room_id" required>
                                    <option value="">اتاق</option>
                                    @foreach ($admissionRooms as $room)
                                        @php $freeBeds = max(0, $room->capacity - $room->occupied_beds); @endphp
                                        <option value="{{ $room->id }}" {{ $freeBeds < 1 ? 'disabled' : '' }}>
                                            اتاق {{ $room->room_number }} · {{ Locale::number($freeBeds) }} خالی
                                        </option>
                                    @endforeach
                                </select>
                                <input class="form-control" name="bed_number" placeholder="تخت">
                                <input class="form-control" name="admission_note" placeholder="یادداشت">
                                <x-ds.button size="sm" type="submit">پذیرفتن</x-ds.button>
                                <x-ds.button variant="outline" size="sm" :href="route('dorm.students.edit', $applicant)">بررسی</x-ds.button>
                            </form>
                        </article>
                    @empty
                        <div class="dashboard-empty">فعلاً هیچ درخواست‌دهنده‌ای در انتظار پذیرش نیست.</div>
                    @endforelse
                </div>
            </section>
        @endif

        <section class="dashboard-panel">
            <div class="dashboard-panel-header">
                <div>
                    <span class="dashboard-section-kicker">ثبت‌های شاگردان</span>
                    <h2>فهرست شاگردان لیلیه</h2>
                    <p>با نام، شماره تماس، آی‌دی، وضعیت، اتاق یا تاریخ ثبت جستجو کنید.</p>
                </div>

                @if (auth()->user()->canAccessAdmin())
                    <x-ds.button size="sm" :href="route('dorm.students.create')">ثبت شاگرد</x-ds.button>
                @endif
            </div>

            <form id="student-filters" method="GET" action="{{ route('dorm.students.index') }}" class="fanous-student-filters">
                <input class="form-control" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="جستجوی نام، شماره تماس یا ID...">

                <select class="form-control" name="status">
                    <option value="">همه وضعیت‌ها</option>
                    @foreach ($statusNames as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <select class="form-control" name="room">
                    <option value="">همه اتاق‌ها</option>
                    @foreach ($rooms as $roomNumber)
                        <option value="{{ $roomNumber }}" @selected((string) request('room') === (string) $roomNumber)>اتاق {{ $roomNumber }}</option>
                    @endforeach
                </select>

                <input class="form-control" name="date" type="date" value="{{ request('date') }}">

                <div class="fanous-filter-actions">
                    <x-ds.button type="submit">جستجو</x-ds.button>
                    <x-ds.button variant="outline" :href="route('dorm.students.index')">پاک کردن</x-ds.button>
                </div>
            </form>

            <div class="fanous-student-grid">
                @forelse ($visibleStudents as $student)
                    @php
                        $dormCard = $student->membershipCards->first();
                        $tone = $statusTones[$student->status] ?? 'primary';
                        $studentRoomNumber = optional($student->room)->room_number;
                        $roomLabel = $student->status === 'active' ? ($studentRoomNumber ?: ($student->room_number ?: 'تعیین نشده')) : 'پذیرش نشده';
                        $documentTotal = count($student->document_names ?? []);
                        $studentCode = 'STD-'.str_pad((string) $student->id, 5, '0', STR_PAD_LEFT);
                        $cardExpiry = $dormCard?->expires_at;
                    @endphp

                    <article class="fanous-student-card">
                        <div class="fanous-student-head">
                            @if ($student->profile_photo_path)
                                <img class="fanous-student-avatar" src="{{ asset('storage/'.$student->profile_photo_path) }}" alt="{{ $student->full_name }}">
                            @else
                                <span class="fanous-student-avatar">{{ mb_substr($student->full_name, 0, 1) }}</span>
                            @endif

                            <div>
                                <strong>{{ $student->full_name }}</strong>
                                <span>نام پدر: {{ $student->father_name }}</span>
                                <small class="ltr-text">{{ $studentCode }}</small>
                            </div>

                            <x-ds.badge :tone="$tone">{{ $statusNames[$student->status] ?? $student->status }}</x-ds.badge>
                        </div>

                        <div class="fanous-student-info">
                            <div><span>تماس</span><strong class="ltr-text">{{ $student->phone }}</strong></div>
                            <div><span>اتاق</span><strong>{{ $roomLabel }}</strong></div>
                            <div><span>تخت</span><strong>{{ $student->bed_number ?: 'ثبت نشده' }}</strong></div>
                            <div><span>تاریخ ثبت</span><strong>{{ $student->created_at ? Locale::number($student->created_at->format('Y/m/d')) : 'ثبت نشده' }}</strong></div>
                            <div><span>اسناد</span><strong>{{ Locale::number($documentTotal) }} فایل</strong></div>
                            <div>
                                <span>{{ $student->status === 'active' ? 'کارت' : 'امتیاز' }}</span>
                                <strong>{{ $student->status === 'active' ? ($cardExpiry ? Locale::number($cardExpiry->format('Y/m/d')) : 'ندارد') : Locale::number($student->eligibility_score ?? 'ثبت نشده') }}</strong>
                            </div>
                        </div>

                        <div class="fanous-student-actions">
                            <x-ds.button size="sm" :href="route('dorm.students.show', $student)">مشاهده</x-ds.button>
                            @if (auth()->user()->canAccessAdmin())
                                <x-ds.button variant="outline" size="sm" :href="route('dorm.students.edit', $student)">ویرایش</x-ds.button>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="dashboard-empty">هیچ شاگردی پیدا نشد.</div>
                @endforelse
            </div>
        </section>
    </div>
@endsection
