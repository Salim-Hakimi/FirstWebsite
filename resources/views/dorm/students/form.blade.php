@extends('admin.layout')

@section('title', $student->exists ? 'ویرایش شاگرد - ادمین فانوس' : 'ثبت شاگرد - ادمین فانوس')

@section('content')
    @php
        $studentStatusNames = $statusLabels;
        $latestDormCard = $dormCard ?? null;
        $selectedStatus = old('status', $student->status);
    @endphp

    <section class="student-form-hero">
        <div>
            <span class="student-command-kicker">{{ $student->exists ? 'ثبت شاگرد' : 'پذیرش جدید' }}</span>
            <h1>{{ $student->exists ? 'ویرایش معلومات شاگرد' : 'ثبت شاگرد جدید' }}</h1>
            <p>معلومات هویتی، اولویت پذیرش، اتاق، ضامن، اسناد و وضعیت کارت لیلیه را در یک فورم ثبت کنید.</p>
        </div>

        <div class="student-command-actions">
            <a href="{{ route('dorm.students.index') }}" class="btn btn-outline-light">برگشت به شاگردان</a>
            @if ($student->exists)
                <a href="{{ route('dorm.students.show', $student) }}" class="btn btn-primary">دیدن پروفایل</a>
            @endif
        </div>
    </section>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">لطفاً فیلدهای مشخص شده را بررسی کرده دوباره ذخیره کنید.</div>
    @endif

    <form id="student-form" method="POST" action="{{ $student->exists ? route('dorm.students.update', $student) : route('dorm.students.store') }}" enctype="multipart/form-data" @unless($student->exists) data-card-required-form data-card-required-message="برای ثبت شاگرد و محاسبه مالی، فیلدهای ضروری را تکمیل کرده و از دکمه ذخیره و چاپ کارت استفاده کنید." @endunless>
        @csrf
        @if ($student->exists)
            @method('PUT')
        @endif

        <div class="student-form-layout">
            <main class="student-form-main">
                <section class="student-form-section">
                    <div class="student-form-section-head">
                        <span class="student-form-step">01</span>
                        <div>
                            <h2>هویت و تماس</h2>
                            <p>معلومات اصلی که در پروفایل، کارت‌های جستجو و کارت چاپی لیلیه استفاده می‌شود.</p>
                        </div>
                    </div>

                    <div class="student-photo-uploader student-form-photo">
                        @if ($student->profile_photo_path)
                            <img src="{{ asset('storage/'.$student->profile_photo_path) }}" alt="{{ $student->full_name ?: 'عکس شاگرد' }}">
                        @else
                            <span>{{ mb_substr($student->full_name ?: 'ش', 0, 1) }}</span>
                        @endif
                        <div class="flex-grow-1">
                            <label>عکس پروفایل</label>
                            <input class="form-control @error('profile_photo') is-invalid @enderror" name="profile_photo" type="file" accept="image/*">
                            <small class="text-muted">از عکس واضح استفاده کنید. این عکس در پروفایل و کارت چاپی دیده می‌شود.</small>
                            @error('profile_photo') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                            @if ($student->profile_photo_path)
                                <label class="student-checkbox-line mt-2">
                                    <input name="remove_profile_photo" type="checkbox" value="1">
                                    <span>حذف عکس فعلی</span>
                                </label>
                            @endif
                        </div>
                    </div>

                    <div class="student-form-grid">
                        <div class="form-group">
                            <label>نام مکمل</label>
                            <input class="form-control @error('full_name') is-invalid @enderror" name="full_name" value="{{ old('full_name', $student->full_name) }}" required>
                            @error('full_name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>نام پدر</label>
                            <input class="form-control @error('father_name') is-invalid @enderror" name="father_name" value="{{ old('father_name', $student->father_name) }}" required>
                            @error('father_name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>آی‌دی / نمبر تذکره</label>
                            <input class="form-control @error('tazkira_number') is-invalid @enderror" name="tazkira_number" value="{{ old('tazkira_number', $student->tazkira_number) }}" required>
                            @error('tazkira_number') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>ولایت</label>
                            <input class="form-control @error('province') is-invalid @enderror" name="province" value="{{ old('province', $student->province) }}">
                            @error('province') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>شماره تماس</label>
                            <input class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $student->phone) }}" required>
                            @error('phone') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>WhatsApp</label>
                            <input class="form-control @error('whatsapp') is-invalid @enderror" name="whatsapp" value="{{ old('whatsapp', $student->whatsapp) }}">
                            @error('whatsapp') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>ایمیل</label>
                            <input class="form-control @error('email') is-invalid @enderror" name="email" type="email" value="{{ old('email', $student->email) }}">
                            @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>تاریخ شمولیت</label>
                            <input class="form-control @error('joined_at') is-invalid @enderror" name="joined_at" type="date" value="{{ old('joined_at', $student->joined_at?->format('Y-m-d')) }}">
                            @error('joined_at') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>محل تحصیل</label>
                            <input class="form-control @error('education_place') is-invalid @enderror" name="education_place" value="{{ old('education_place', $student->education_place) }}" required>
                            @error('education_place') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>دیپارتمنت / صنف</label>
                            <input class="form-control @error('department_or_grade') is-invalid @enderror" name="department_or_grade" value="{{ old('department_or_grade', $student->department_or_grade) }}">
                            @error('department_or_grade') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </section>

                <section class="student-form-section">
                    <div class="student-form-section-head">
                        <span class="student-form-step">02</span>
                        <div>
                            <h2>ارزیابی پذیرش</h2>
                            <p>از این فیلدها برای اولویت لیست انتظار، بررسی درخواست و تصمیم پذیرش استفاده کنید.</p>
                        </div>
                    </div>

                    <div class="student-form-grid three">
                        <div class="form-group">
                            <label>تاریخ درخواست</label>
                            <input class="form-control @error('application_date') is-invalid @enderror" name="application_date" type="date" value="{{ old('application_date', $student->application_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}">
                            @error('application_date') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>فیصدی تحصیل</label>
                            <input class="form-control @error('education_score') is-invalid @enderror" name="education_score" type="number" min="0" max="100" step="0.01" value="{{ old('education_score', $student->education_score) }}" placeholder="0 - 100">
                            @error('education_score') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>امتیاز واجد بودن</label>
                            <input class="form-control @error('eligibility_score') is-invalid @enderror" name="eligibility_score" type="number" min="0" max="100" value="{{ old('eligibility_score', $student->eligibility_score) }}" placeholder="0 - 100">
                            @error('eligibility_score') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group full">
                            <label>یادداشت واجد بودن</label>
                            <textarea class="form-control @error('eligibility_notes') is-invalid @enderror" name="eligibility_notes" rows="3" placeholder="دلیل اولویت، فاصله، نیاز مالی، یادداشت کمیته یا بررسی اسناد">{{ old('eligibility_notes', $student->eligibility_notes) }}</textarea>
                            @error('eligibility_notes') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </section>

                <section class="student-form-section">
                    <div class="student-form-section-head">
                        <span class="student-form-step">03</span>
                        <div>
                            <h2>ضامن و اسناد</h2>
                            <p>فایل‌های حمایوی را ضمیمه کنید و معلومات ضامن را کنار پروفایل شاگرد نگه دارید.</p>
                        </div>
                    </div>

                    <div class="student-form-grid">
                        <div class="form-group">
                            <label>نام ضامن</label>
                            <input class="form-control @error('guarantor_name') is-invalid @enderror" name="guarantor_name" value="{{ old('guarantor_name', $student->guarantor_name) }}">
                            @error('guarantor_name') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>نسبت ضامن با شاگرد</label>
                            <input class="form-control @error('guarantor_relation') is-invalid @enderror" name="guarantor_relation" value="{{ old('guarantor_relation', $student->guarantor_relation) }}" placeholder="مثلاً: کاکا، ماما، پدر، برادر">
                            @error('guarantor_relation') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>شماره ضامن</label>
                            <input class="form-control @error('guarantor_phone') is-invalid @enderror" name="guarantor_phone" value="{{ old('guarantor_phone', $student->guarantor_phone) }}">
                            @error('guarantor_phone') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>شماره تذکره ضامن</label>
                            <input class="form-control @error('guarantor_tazkira_number') is-invalid @enderror" name="guarantor_tazkira_number" value="{{ old('guarantor_tazkira_number', $student->guarantor_tazkira_number) }}">
                            @error('guarantor_tazkira_number') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>شغل ضامن</label>
                            <input class="form-control @error('guarantor_job') is-invalid @enderror" name="guarantor_job" value="{{ old('guarantor_job', $student->guarantor_job) }}">
                            @error('guarantor_job') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>محل سکونت اصلی ضامن</label>
                            <input class="form-control @error('guarantor_permanent_address') is-invalid @enderror" name="guarantor_permanent_address" value="{{ old('guarantor_permanent_address', $student->guarantor_permanent_address) }}">
                            @error('guarantor_permanent_address') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>محل سکونت فعلی ضامن</label>
                            <input class="form-control @error('guarantor_current_address') is-invalid @enderror" name="guarantor_current_address" value="{{ old('guarantor_current_address', $student->guarantor_current_address) }}">
                            @error('guarantor_current_address') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="student-document-upload-grid">
                        <div class="form-group">
                            <label>آپلود تذکره شاگرد</label>
                            <input class="form-control @error('student_tazkira_document') is-invalid @enderror" name="student_tazkira_document" type="file" accept=".jpg,.jpeg,.png,.pdf">
                            @error('student_tazkira_document') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group full">
                            <label>آپلود اسناد شاگرد</label>
                            <input class="form-control @error('student_documents') is-invalid @enderror" name="student_documents[]" type="file" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                            @error('student_documents') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                            @error('student_documents.*') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>آپلود تذکره ضامن</label>
                            <input class="form-control @error('guarantor_tazkira_document') is-invalid @enderror" name="guarantor_tazkira_document" type="file" accept=".jpg,.jpeg,.png,.pdf">
                            @error('guarantor_tazkira_document') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group full">
                            <label>آپلود اسناد ضامن</label>
                            <input class="form-control @error('guarantor_documents') is-invalid @enderror" name="guarantor_documents[]" type="file" multiple accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                            <small class="text-muted">مجاز: عکس، PDF و Word. حداکثر ۵MB برای هر فایل.</small>
                            @error('guarantor_documents') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                            @error('guarantor_documents.*') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    @if ($student->document_names)
                        <div class="student-document-list">
                            @foreach ($student->document_names as $index => $document)
                                <article class="student-document-item">
                                    <span class="student-timeline-icon">س</span>
                                    <div>
                                        <strong>{{ $document['name'] ?? 'سند بی‌نام' }}</strong>
                                        <p>{{ $document['label'] ?? 'سند' }} · {{ $document['uploaded_at'] ?? 'تاریخ آپلود نامعلوم' }}</p>
                                        <label class="student-checkbox-line">
                                            <input name="remove_documents[]" type="checkbox" value="{{ $index }}">
                                            <span>حذف این فایل هنگام ذخیره</span>
                                        </label>
                                    </div>
                                    @if (! empty($document['path']))
                                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('dorm.students.documents.show', [$student, $index]) }}" target="_blank" rel="noopener">باز کردن</a>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    @endif

                    <div class="form-group mb-0">
                        <label>یادداشت‌ها</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" name="notes" rows="5">{{ old('notes', $student->notes) }}</textarea>
                        @error('notes') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </section>
            </main>

            <aside class="student-form-side">
                <section class="student-form-section is-sticky">
                    <div class="student-form-section-head compact">
                        <span class="student-form-step">04</span>
                        <div>
                            <h2>اتاق و وضعیت</h2>
                            <p>تخصیص اتاق فقط برای شاگردان فعال تطبیق می‌شود.</p>
                        </div>
                    </div>

                    <div class="student-status-preview">
                        <span class="badge badge-outline-primary">{{ $studentStatusNames[$selectedStatus] ?? $selectedStatus }}</span>
                        <strong>{{ old('full_name', $student->full_name) ?: 'نام شاگرد' }}</strong>
                        <p>{{ old('education_place', $student->education_place) ?: 'محل تحصیل' }}</p>
                    </div>

                    <div class="form-group">
                        <label>اتاق</label>
                        <select class="form-control @error('dorm_room_id') is-invalid @enderror" name="dorm_room_id">
                            <option value="">تا هنوز تعیین نشده</option>
                            @foreach ($rooms as $room)
                                @php
                                    $availableBeds = max(0, $room->capacity - $room->occupied_beds);
                                    $selectedRoomId = old('dorm_room_id', $student->dorm_room_id);
                                    $isCurrentRoom = (int) $selectedRoomId === (int) $room->id;
                                @endphp
                                <option value="{{ $room->id }}" @selected($isCurrentRoom) @disabled(! $isCurrentRoom && $availableBeds < 1)>
                                    اتاق {{ $room->room_number }} - ظرفیت {{ $room->capacity }} - خالی {{ $isCurrentRoom ? $availableBeds + 1 : $availableBeds }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">درخواست‌های لیست انتظار تا زمان پذیرش بدون بستر می‌مانند.</small>
                        @error('dorm_room_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="student-form-grid compact">
                        <div class="form-group">
                            <label>اتاق دستی</label>
                            <input class="form-control @error('room_number') is-invalid @enderror" name="room_number" value="{{ old('room_number', $student->room_number) }}" placeholder="اختیاری">
                            @error('room_number') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>بستر</label>
                            <input class="form-control @error('bed_number') is-invalid @enderror" name="bed_number" value="{{ old('bed_number', $student->bed_number) }}">
                            @error('bed_number') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label>وضعیت</label>
                        <select class="form-control @error('status') is-invalid @enderror" name="status" required>
                            @foreach ($studentStatusNames as $value => $label)
                                <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">وقتی جای خالی نیست یا درخواست نیاز به بررسی دارد، از لیست انتظار استفاده کنید.</small>
                        @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="student-side-divider"></div>
                    <h3 class="student-side-title">پرداخت ثبت‌نام</h3>
                    <div class="student-status-preview">
                        <strong>مجموع ضروری: {{ number_format((int) old('guarantee_deposit_amount', $student->guarantee_deposit_amount ?? 1000) + (int) old('dorm_expense_fee_amount', $student->dorm_expense_fee_amount ?? 1000) + (int) old('registration_card_fee_amount', $student->registration_card_fee_amount ?? 50)) }} افغانی</strong>
                        <p>ضمانت، مصارف ابتدایی لیلیه و فیس کارت هنگام ثبت‌نام توسط ادمین دریافت می‌شود.</p>
                    </div>
                    <div class="student-form-grid compact">
                        <div class="form-group">
                            <label>ضمانت</label>
                            <input class="form-control @error('guarantee_deposit_amount') is-invalid @enderror" name="guarantee_deposit_amount" type="number" min="0" value="{{ old('guarantee_deposit_amount', $student->guarantee_deposit_amount ?? 1000) }}">
                            @error('guarantee_deposit_amount') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>مصارف لیلیه</label>
                            <input class="form-control @error('dorm_expense_fee_amount') is-invalid @enderror" name="dorm_expense_fee_amount" type="number" min="0" value="{{ old('dorm_expense_fee_amount', $student->dorm_expense_fee_amount ?? 1000) }}">
                            @error('dorm_expense_fee_amount') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="student-form-grid compact">
                        <div class="form-group">
                            <label>وضعیت پرداخت</label>
                            <select class="form-control @error('registration_payment_status') is-invalid @enderror" name="registration_payment_status">
                                @php $registrationStatus = old('registration_payment_status', $student->registration_payment_status ?? 'paid'); @endphp
                                <option value="paid" @selected($registrationStatus === 'paid')>پرداخت شده</option>
                                <option value="partial" @selected($registrationStatus === 'partial')>قسمی</option>
                                <option value="unpaid" @selected($registrationStatus === 'unpaid')>پرداخت نشده</option>
                            </select>
                            @error('registration_payment_status') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>تاریخ پرداخت</label>
                            <input class="form-control @error('registration_paid_at') is-invalid @enderror" name="registration_paid_at" type="date" value="{{ old('registration_paid_at', $student->registration_paid_at?->format('Y-m-d') ?? now()->toDateString()) }}">
                            @error('registration_paid_at') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label>فیس کارت لیلیه</label>
                        <input class="form-control @error('registration_card_fee_amount') is-invalid @enderror" name="registration_card_fee_amount" type="number" min="0" value="{{ old('registration_card_fee_amount', $student->registration_card_fee_amount ?? 50) }}">
                        <small class="text-muted">فیس پیش‌فرض کارت ۵۰ افغانی است و روی کارت چاپی دیده می‌شود.</small>
                        @error('registration_card_fee_amount') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    @unless ($student->exists)
                        <div class="student-side-divider"></div>
                        <h3 class="student-side-title">کارت لیلیه</h3>
                        <div class="form-group">
                            <label>فیس کارت</label>
                            <input class="form-control @error('card_fee') is-invalid @enderror" name="card_fee" type="number" min="0" step="0.01" value="{{ old('card_fee', $student->registration_card_fee_amount ?? 50) }}">
                            <small class="text-muted">این فیس ۵۰ افغانی روی کارت چاپی ذخیره می‌شود.</small>
                            @error('card_fee') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="form-group">
                            <label>وضعیت پرداخت</label>
                            <select class="form-control @error('card_payment_status') is-invalid @enderror" name="card_payment_status">
                                <option value="paid" @selected(old('card_payment_status', 'paid') === 'paid')>پرداخت شده</option>
                                <option value="unpaid" @selected(old('card_payment_status') === 'unpaid')>پرداخت نشده</option>
                            </select>
                            @error('card_payment_status') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    @else
                        <div class="student-side-divider"></div>
                        <div class="student-card-summary">
                            <span class="student-timeline-icon">ک</span>
                            <div>
                                <strong>{{ $latestDormCard ? 'کارت فعلی' : 'کارت لیلیه ندارد' }}</strong>
                                <p>{{ $latestDormCard?->expires_at?->format('Y-m-d') ?? 'بعد از پذیرش کارت صادر کنید.' }} - {{ $latestDormCard?->payment_status === 'paid' ? 'پرداخت شده' : 'پرداخت نشده' }}</p>
                            </div>
                        </div>
                    @endunless

                    <div class="student-save-panel">
                        <button class="btn btn-primary" type="submit" @unless($student->exists) data-disabled-until-card disabled @endunless>ذخیره معلومات</button>
                        @if ($student->exists)
                            @if ($student->status === 'active')
                                <button class="btn btn-outline-primary" type="submit" form="issue-card-form">{{ $latestDormCard ? 'تمدید کارت' : 'صدور کارت' }}</button>
                                @if ($latestDormCard)
                                    <a class="btn btn-outline-secondary" href="{{ route('membership-cards.print', $latestDormCard) }}">چاپ کارت</a>
                                @endif
                            @else
                                <small class="text-muted">کارت بعد از پذیرش صادر می‌شود.</small>
                            @endif
                        @else
                            <button class="btn btn-outline-primary" type="submit" name="issue_card" value="1" data-card-submit disabled>ذخیره و چاپ کارت</button>
                        @endif
                        <a class="btn btn-dark" href="{{ route('dorm.students.index') }}">لغو</a>
                    </div>
                </section>
            </aside>
        </div>
    </form>

    @if ($student->exists)
        <form id="issue-card-form" method="POST" action="{{ route('dorm.students.card.issue', $student) }}">
            @csrf
        </form>
    @endif

    @unless ($student->exists)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const form = document.querySelector('[data-card-required-form]');
                if (! form) {
                    return;
                }

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
        </script>
    @endunless
@endsection
