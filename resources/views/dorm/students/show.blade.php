@extends('admin.layout')

@section('title', 'پروفایل شاگرد - ادمین فانوس')

@section('content')
    @php
        $latestCard = $student->membershipCards->first();
        $statusNames = $statusLabels;
        $collectionNames = [
            'monthly_fee' => 'فیس ماهانه',
            'electricity' => 'برق',
            'fine' => 'جریمه',
            'water' => 'آب',
            'expense' => 'مصرف نماینده',
        ];
        $foodNames = [
            'contribution' => 'سهم غذا',
            'weekly_food' => 'غذای هفتگی',
            'monthly_fee' => 'فیس ماهانه',
            'electricity' => 'برق',
            'water' => 'آب',
            'expense' => 'مصرف / خرید',
        ];
        $badgeClasses = [
            'active' => 'badge-outline-success',
            'waiting' => 'badge-outline-warning',
            'on_hold' => 'badge-outline-secondary',
            'rejected' => 'badge-outline-danger',
            'suspended' => 'badge-outline-warning',
            'graduated' => 'badge-outline-primary',
            'left' => 'badge-outline-danger',
        ];
        $statusClass = $badgeClasses[$student->status] ?? 'badge-outline-secondary';
        $roomLabel = $student->status === 'active' ? ($student->room?->room_number ?: ($student->room_number ?: 'ثبت نشده')) : 'در انتظار';
        $registrationTotal = (int) ($student->guarantee_deposit_amount ?? 1000) + (int) ($student->dorm_expense_fee_amount ?? 1000) + (int) ($student->registration_card_fee_amount ?? 50);
        $registrationPaymentStatus = $student->registration_payment_status ?? 'paid';
    @endphp

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <section class="student-profile-hero">
        <div class="student-profile-identity">
            @if ($student->profile_photo_path)
                <img class="student-profile-photo" src="{{ asset('storage/'.$student->profile_photo_path) }}" alt="{{ $student->full_name }}">
            @else
                <div class="student-profile-photo student-profile-photo-empty">{{ mb_substr($student->full_name ?: 'ش', 0, 1) }}</div>
            @endif

            <div>
                <span class="badge {{ $statusClass }}">{{ $statusNames[$student->status] ?? $student->status }}</span>
                <h1>{{ $student->full_name }}</h1>
                <p>{{ $student->education_place }} - {{ $student->department_or_grade ?: 'دیپارتمنت / صنف ثبت نشده' }}</p>
                <div class="student-profile-actions">
                    @if ($canEditStudent)
                        <a class="btn btn-primary btn-sm" href="{{ route('dorm.students.edit', $student) }}">ویرایش پروفایل</a>
                    @endif
                    @if ($latestCard)
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('membership-cards.print', $latestCard) }}">چاپ کارت</a>
                    @endif
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('dorm.students.registration.receipt', $student) }}">رسید ثبت‌نام</a>
                    <a href="{{ route('dorm.students.index') }}" class="btn btn-dark btn-sm">برگشت</a>
                </div>
            </div>
        </div>

        <div class="student-profile-snapshot">
            <span><strong>{{ $roomLabel }}</strong>اتاق</span>
            <span><strong>{{ $student->bed_number ?: 'ثبت نشده' }}</strong>بستر</span>
            <span><strong>{{ $student->eligibility_score ?? 'ثبت نشده' }}</strong>امتیاز واجد بودن</span>
            <span><strong>{{ count($student->document_names ?? []) }}</strong>اسناد</span>
        </div>
    </section>

    <section class="student-insight-grid">
        <article class="student-insight-card is-primary">
            <span>وضعیت لیلیه</span>
            <strong>{{ $statusNames[$student->status] ?? $student->status }}</strong>
            <p>تاریخ شمولیت: {{ $student->joined_at?->format('Y-m-d') ?: 'ثبت نشده' }}</p>
        </article>
        <article class="student-insight-card">
            <span>درخواست</span>
            <strong>{{ $student->application_date?->format('Y-m-d') ?? 'ثبت نشده' }}</strong>
            <p>پذیرفته شده: {{ $student->admitted_at?->format('Y-m-d') ?? 'تا هنوز نه' }}</p>
        </article>
        <article class="student-insight-card">
            <span>پرداخت ثبت‌نام</span>
            <strong>{{ number_format($registrationTotal) }} افغانی</strong>
            <p>{{ ['paid' => 'پرداخت شده', 'partial' => 'قسمی', 'unpaid' => 'پرداخت نشده'][$registrationPaymentStatus] ?? $registrationPaymentStatus }}{{ $student->registration_paid_at ? ' در '.$student->registration_paid_at->format('Y-m-d') : '' }}</p>
        </article>
        <article class="student-insight-card">
            <span>کارت لیلیه</span>
            <strong>{{ $latestCard?->card_number ?: 'ندارد' }}</strong>
            <p>{{ $latestCard ? number_format((float) $latestCard->fee_amount, 0).' افغانی فیس کارت' : 'کارت فعال ثبت نشده' }}</p>
        </article>
    </section>

    <section class="student-profile-layout">
        <div class="student-workspace-panel">
            <div class="student-panel-head">
                <div>
                    <span class="student-panel-label">هویت</span>
                    <h2>معلومات شخصی</h2>
                    <p>پروفایل اصلی، تماس‌ها، ضامن و تصمیم پذیرش.</p>
                </div>
            </div>

            <div class="student-detail-grid">
                <div><span>نام پدر</span><strong>{{ $student->father_name }}</strong></div>
                <div><span>واتساپ</span><strong>{{ $student->whatsapp ?: $student->phone }}</strong></div>
                <div><span>شماره خانواده</span><strong>{{ $student->family_phone ?: 'ثبت نشده' }}</strong></div>
                <div><span>ایمیل</span><strong>{{ $student->email ?: 'ثبت نشده' }}</strong></div>
                <div><span>آی‌دی / تذکره</span><strong>{{ $student->tazkira_number }}</strong></div>
                <div><span>ولایت</span><strong>{{ $student->province ?: 'ثبت نشده' }}</strong></div>
                <div><span>ولسوالی</span><strong>{{ $student->district ?: 'ثبت نشده' }}</strong></div>
                <div><span>محل تحصیل</span><strong>{{ $student->education_place }}</strong></div>
                <div><span>دیپارتمنت / صنف</span><strong>{{ $student->department_or_grade ?: 'ثبت نشده' }}</strong></div>
                <div><span>سال فراغت مکتب</span><strong>{{ $student->school_graduation_year ? \App\Support\Locale::number($student->school_graduation_year) : 'ثبت نشده' }}</strong></div>
                <div><span>ضامن</span><strong>{{ $student->guarantor_name ?: 'ثبت نشده' }}</strong></div>
                <div><span>نسبت ضامن</span><strong>{{ $student->guarantor_relation ?: 'ثبت نشده' }}</strong></div>
                <div><span>شماره ضامن</span><strong>{{ $student->guarantor_phone ?: 'ثبت نشده' }}</strong></div>
                <div><span>تذکره ضامن</span><strong>{{ $student->guarantor_tazkira_number ?: 'ثبت نشده' }}</strong></div>
                <div><span>شغل ضامن</span><strong>{{ $student->guarantor_job ?: 'ثبت نشده' }}</strong></div>
                <div><span>سکونت اصلی ضامن</span><strong>{{ $student->guarantor_permanent_address ?: 'ثبت نشده' }}</strong></div>
                <div><span>سکونت فعلی ضامن</span><strong>{{ $student->guarantor_current_address ?: 'ثبت نشده' }}</strong></div>
                <div><span>تاریخ درخواست</span><strong>{{ $student->application_date?->format('Y-m-d') ?: 'ثبت نشده' }}</strong></div>
                <div><span>تصمیم توسط</span><strong>{{ $student->admissionDecisionBy?->name ?: 'تصمیم نشده' }}</strong></div>
            </div>

            <div class="student-detail-grid mt-3">
                <div><span>ضمانت</span><strong>{{ number_format((int) ($student->guarantee_deposit_amount ?? 1000)) }} افغانی</strong></div>
                <div><span>مصارف لیلیه</span><strong>{{ number_format((int) ($student->dorm_expense_fee_amount ?? 1000)) }} افغانی</strong></div>
                <div><span>فیس کارت لیلیه</span><strong>{{ number_format((int) ($student->registration_card_fee_amount ?? 50)) }} افغانی</strong></div>
                <div><span>مجموع ثبت‌نام</span><strong>{{ number_format($registrationTotal) }} افغانی</strong></div>
                <div><span>وضعیت پرداخت</span><strong>{{ ['paid' => 'پرداخت شده', 'partial' => 'قسمی', 'unpaid' => 'پرداخت نشده'][$registrationPaymentStatus] ?? $registrationPaymentStatus }}</strong></div>
            </div>
            <div class="student-profile-actions mt-3">
                <a class="btn btn-outline-secondary btn-sm" href="{{ route('dorm.students.registration.receipt', $student) }}">چاپ رسید ثبت‌نام</a>
            </div>

            @if ($student->eligibility_notes)
                <div class="user-access-note mt-3">
                    <div class="preview-thumbnail"><div class="preview-icon bg-warning"><span>ی</span></div></div>
                    <p class="text-muted mb-0">{{ $student->eligibility_notes }}</p>
                </div>
            @endif
        </div>

        <aside class="student-workspace-panel">
            <div class="student-panel-head">
                <div>
                    <span class="student-panel-label">فایل‌ها</span>
                    <h2>کارت و اسناد</h2>
                    <p>کارت لیلیه، فایل‌های آپلود شده و یادداشت‌های پروفایل.</p>
                </div>
                @if ($canEditStudent && $student->status === 'active')
                    <form method="POST" action="{{ route('dorm.students.card.issue', $student) }}">
                        @csrf
                        <button class="btn btn-primary btn-sm" type="submit">صدور کارت</button>
                    </form>
                @endif
            </div>

            <div class="student-timeline-list">
                <div class="student-timeline-item">
                    <span class="student-timeline-icon">ک</span>
                    <div>
                        @if ($latestCard)
                            <strong>کارت {{ $latestCard->card_number }}</strong>
                            <p>اعتبار تا {{ $latestCard->expires_at?->format('Y-m-d') }} - {{ number_format((float) $latestCard->fee_amount, 0) }} افغانی - {{ $latestCard->payment_status === 'paid' ? 'پرداخت شده' : 'پرداخت نشده' }}</p>
                        @else
                            <strong>کارت لیلیه ندارد</strong>
                            <p>وقتی شاگرد فعال شد، از همین بخش کارت صادر کنید.</p>
                        @endif
                    </div>
                    @if ($latestCard)
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('membership-cards.print', $latestCard) }}">چاپ</a>
                    @endif
                </div>

                <div class="student-timeline-item">
                    <span class="student-timeline-icon">ی</span>
                    <div>
                        <strong>یادداشت‌ها</strong>
                        <p>{{ $student->notes ?: 'یادداشتی ثبت نشده است.' }}</p>
                    </div>
                </div>

                @forelse ($student->document_names ?? [] as $document)
                    <div class="student-timeline-item">
                        <span class="student-timeline-icon">س</span>
                        <div>
                            <strong>{{ $document['name'] ?? 'سند' }}</strong>
                            <p>{{ $document['label'] ?? 'سند' }} · {{ $document['uploaded_at'] ?? 'تاریخ آپلود نامعلوم' }}</p>
                        </div>
                        @if (! empty($document['path']))
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('dorm.students.documents.show', [$student, $loop->index]) }}" target="_blank" rel="noopener">باز کردن</a>
                        @endif
                    </div>
                @empty
                    <div class="student-directory-empty">تا هنوز سندی آپلود نشده است.</div>
                @endforelse
            </div>
        </aside>
    </section>

    @if ($canRecordPurchaser || $canRecordRepresentative)
        <section class="student-profile-layout">
            @if ($canRecordPurchaser)
                <div class="student-workspace-panel">
                    <div class="student-panel-head"><div><span class="student-panel-label">خریدار</span><h2>ثبت دریافت</h2></div></div>
                    <form method="POST" action="{{ route('purchaser.records.store') }}">
                        @csrf
                        <input name="dorm_student_id" type="hidden" value="{{ $student->id }}">
                        <div class="form-group"><label>نوع</label><select class="form-control" name="type" required>@foreach ($foodNames as $value => $label)@if ($value !== 'expense')<option value="{{ $value }}">{{ $label }}</option>@endif @endforeach</select></div>
                        <div class="form-group"><label>مبلغ</label><input class="form-control" name="amount" type="number" min="1" value="600" required></div>
                        <div class="form-group"><label>تاریخ</label><input class="form-control" name="recorded_at" type="date" value="{{ now()->format('Y-m-d') }}" required></div>
                        <div class="form-group"><label>دوره</label><input class="form-control" name="period" value="هفته {{ now()->weekOfYear }}"></div>
                        <div class="form-group"><label>یادداشت</label><textarea class="form-control" name="description"></textarea></div>
                        <button class="btn btn-primary" type="submit">ذخیره دریافت</button>
                    </form>
                </div>
            @endif

            @if ($canRecordRepresentative)
                <div class="student-workspace-panel">
                    <div class="student-panel-head"><div><span class="student-panel-label">نماینده</span><h2>ثبت دریافت</h2></div></div>
                    <form method="POST" action="{{ route('representative.collections.store') }}">
                        @csrf
                        <input name="dorm_student_id" type="hidden" value="{{ $student->id }}">
                        <div class="form-group"><label>نوع</label><select class="form-control" name="type" required>@foreach ($collectionNames as $value => $label)@if ($value !== 'expense')<option value="{{ $value }}">{{ $label }}</option>@endif @endforeach</select></div>
                        <div class="form-group"><label>مبلغ</label><input class="form-control" name="amount" type="number" min="1" required></div>
                        <div class="form-group"><label>تاریخ</label><input class="form-control" name="collected_at" type="date" value="{{ now()->format('Y-m-d') }}" required></div>
                        <div class="form-group"><label>دوره</label><input class="form-control" name="period" placeholder="مثلاً: ماه حمل"></div>
                        <div class="form-group"><label>یادداشت</label><textarea class="form-control" name="notes"></textarea></div>
                        <button class="btn btn-primary" type="submit">ذخیره دریافت</button>
                    </form>
                </div>
            @endif
        </section>
    @endif

    <section class="student-profile-layout">
        <div class="student-workspace-panel">
            <div class="student-panel-head">
                <div><span class="student-panel-label">نماینده</span><h2>تاریخچه حساب</h2></div>
            </div>
            <div class="student-timeline-list">
                @forelse ($student->collections->sortByDesc('collected_at') as $collection)
                    <div class="student-timeline-item">
                        <span class="student-timeline-icon">ن</span>
                        <div>
                            <strong>{{ $collectionNames[$collection->type] ?? $collection->type }} - {{ number_format($collection->amount) }} افغانی</strong>
                            <p>{{ $collection->collected_at?->format('Y-m-d') }} - {{ $collection->period ?: 'دوره ندارد' }} - {{ $collection->notes ?: 'یادداشت ندارد' }}</p>
                        </div>
                        @if (in_array(auth()->user()->role, \App\Models\User::studentRepresentativeRoles(), true))
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('representative.collections.receipt', $collection) }}">رسید</a>
                        @endif
                    </div>
                @empty
                    <div class="student-directory-empty">تا هنوز ثبت نماینده وجود ندارد.</div>
                @endforelse
            </div>
        </div>

        <div class="student-workspace-panel">
            <div class="student-panel-head">
                <div><span class="student-panel-label">خریدار</span><h2>تاریخچه حساب</h2></div>
            </div>
            <div class="student-timeline-list">
                @forelse ($student->foodFinances->sortByDesc('recorded_at') as $record)
                    <div class="student-timeline-item">
                        <span class="student-timeline-icon">خ</span>
                        <div>
                            <strong>{{ $foodNames[$record->type] ?? $record->type }} - {{ number_format($record->amount) }} افغانی</strong>
                            <p>{{ $record->recorded_at?->format('Y-m-d') }} - {{ $record->period ?: 'دوره ندارد' }} - {{ $record->description ?: 'توضیح ندارد' }}</p>
                        </div>
                        @if (in_array(auth()->user()->role, \App\Models\User::purchaserRoles(), true))
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('purchaser.records.receipt', $record) }}">رسید</a>
                        @endif
                    </div>
                @empty
                    <div class="student-directory-empty">تا هنوز ثبت خریدار وجود ندارد.</div>
                @endforelse
            </div>
        </div>
    </section>
@endsection
