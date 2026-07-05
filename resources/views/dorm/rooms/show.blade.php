@extends('admin.layout')

@section('title', 'اتاق '.$room->room_number.' - ادمین فانوس')

@section('content')
    @php
        $freeBeds = max(0, $room->capacity - $room->occupied_beds);
        $usedPercent = $room->capacity > 0 ? min(100, round(($room->occupied_beds / $room->capacity) * 100)) : 0;
        $studentsByBed = $room->activeStudents->keyBy(fn ($student) => (string) $student->bed_number);
        $roomStatusNames = [
            'active' => 'فعال',
            'maintenance' => 'در ترمیم',
            'closed' => 'بسته',
        ];
        $badgeClass = match ($room->status) {
            'active' => 'badge-outline-success',
            'maintenance' => 'badge-outline-warning',
            'closed' => 'badge-outline-danger',
            default => 'badge-outline-secondary',
        };
    @endphp

    <div class="student-profile-hero room-hero">
        <div class="student-profile-identity">
            <div class="room-hero-mark">{{ $room->room_number }}</div>
            <div>
                <span class="student-command-kicker">{{ $room->floor ?: 'منزل ثبت نشده' }}</span>
                <h1>اتاق {{ $room->room_number }}</h1>
                <p>شاگردان را تخصیص کنید، استفاده بسترها را ببینید و باشندگان را به‌سادگی انتقال دهید.</p>
                <div class="student-profile-actions">
                    <a href="{{ route('dorm.rooms.index') }}" class="btn btn-outline-secondary">برگشت</a>
                    <a href="{{ route('dorm.rooms.edit', $room) }}" class="btn btn-primary">ویرایش اتاق</a>
                </div>
            </div>
        </div>
        <div class="student-profile-snapshot">
            <span><strong>{{ $room->capacity }}</strong>ظرفیت</span>
            <span><strong>{{ $room->occupied_beds }}</strong>پر</span>
            <span><strong>{{ $freeBeds }}</strong>بستر خالی</span>
            <span><strong>{{ $usedPercent }}%</strong>استفاده‌شده</span>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="room-detail-layout">
        <div class="room-detail-main">
            <section class="student-workspace-panel">
                <div class="student-panel-head">
                    <div>
                        <span class="student-panel-label">نقشه بستر</span>
                        <h2>چیدمان اتاق</h2>
                        <p>اتاق {{ $roomStatusNames[$room->status] ?? $room->status }} با {{ $freeBeds }} بستر خالی.</p>
                    </div>
                    <span class="badge {{ $badgeClass }}">{{ $roomStatusNames[$room->status] ?? $room->status }}</span>
                </div>

                <div class="room-usage-line mb-4">
                    <span style="width: {{ $usedPercent }}%"></span>
                </div>

                <div class="room-bed-grid">
                    @for ($bed = 1; $bed <= $room->capacity; $bed++)
                        @php $bedStudent = $studentsByBed->get((string) $bed); @endphp
                        <div class="room-bed-tile {{ $bedStudent ? 'is-occupied' : '' }}">
                            <span>بستر {{ $bed }}</span>
                            <strong>{{ $bedStudent?->full_name ?: 'خالی' }}</strong>
                            <p>{{ $bedStudent ? 'واتساپ: '.($bedStudent->whatsapp ?: $bedStudent->phone) : 'آماده تخصیص' }}</p>
                        </div>
                    @endfor
                </div>
            </section>

            <section class="student-workspace-panel">
                <div class="student-panel-head">
                    <div>
                        <span class="student-panel-label">باشندگان</span>
                        <h2>شاگردان این اتاق</h2>
                        <p>هر کارت گزینه انتقال و حذف را کنار معلومات شاگرد دارد.</p>
                    </div>
                </div>

                <div class="room-resident-list">
                    @forelse ($room->activeStudents as $student)
                        <article class="room-resident-card">
                            <div class="student-table-person">
                                <div class="user-table-avatar">{{ mb_substr($student->full_name, 0, 1) }}</div>
                                <div>
                                    <strong>{{ $student->full_name }}</strong>
                                    <p>نام پدر: {{ $student->father_name ?: 'ثبت نشده' }}</p>
                                </div>
                            </div>

                            <div class="room-resident-meta">
                                <span><strong>بستر</strong>{{ $student->bed_number ?: 'ثبت نشده' }}</span>
                                <span><strong>واتساپ</strong>{{ $student->whatsapp ?: $student->phone ?: 'ثبت نشده' }}</span>
                                <span><strong>آی‌دی</strong>{{ $student->tazkira_number ?: 'ثبت نشده' }}</span>
                            </div>

                            <form method="POST" action="{{ route('dorm.rooms.students.move', [$room, $student]) }}" class="room-card-form">
                                @csrf
                                @method('PUT')
                                <select class="form-control form-control-sm" name="target_room_id" required>
                                    <option value="">اتاق مقصد</option>
                                    @foreach ($roomsForMove as $targetRoom)
                                        <option value="{{ $targetRoom->id }}">
                                            اتاق {{ $targetRoom->room_number }} - خالی {{ max(0, $targetRoom->capacity - $targetRoom->occupied_beds) }}
                                        </option>
                                    @endforeach
                                </select>
                                <input class="form-control form-control-sm" name="bed_number" type="number" min="1" required placeholder="بستر">
                                <button class="btn btn-outline-primary btn-sm" type="submit">انتقال</button>
                            </form>

                            <form method="POST" action="{{ route('dorm.rooms.students.remove', [$room, $student]) }}">
                                @csrf
                                @method('DELETE')
                                <label class="sr-only" for="left_at_{{ $student->id }}">تاریخ خروج</label>
                                <input id="left_at_{{ $student->id }}" class="form-control form-control-sm mb-2 @error('left_at') is-invalid @enderror" name="left_at" type="date" value="{{ old('left_at', now()->toDateString()) }}" required>
                                @error('left_at') <span class="text-danger small d-block mb-2">{{ $message }}</span> @enderror
                                <button class="btn btn-outline-danger btn-sm" type="submit">حذف از اتاق</button>
                            </form>
                        </article>
                    @empty
                        <div class="student-directory-empty">
                            <strong>تا هنوز باشنده‌ای نیست</strong>
                            <p>از بخش کناری یک شاگرد فعال را تخصیص کنید.</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </div>

        <aside class="student-workspace-panel room-assignment-panel">
            <div class="student-panel-head">
                <div>
                    <span class="student-panel-label">تخصیص</span>
                    <h2>افزودن شاگرد</h2>
                    <p>فقط شاگردان فعال بدون اتاق در این‌جا نمایش داده می‌شوند.</p>
                </div>
            </div>

            @if ($freeBeds > 0 && $room->status === 'active')
                <form method="POST" action="{{ route('dorm.rooms.allocations.store', $room) }}">
                    @csrf
                    <div class="form-group">
                        <label for="dorm_student_id">شاگرد</label>
                        <select id="dorm_student_id" class="form-control @error('dorm_student_id') is-invalid @enderror" name="dorm_student_id" required>
                            <option value="">شاگرد را انتخاب کنید</option>
                            @foreach ($unassignedStudents as $student)
                                <option value="{{ $student->id }}" @selected(old('dorm_student_id') == $student->id)>
                                    {{ $student->full_name }} - {{ $student->whatsapp ?: $student->phone }}
                                </option>
                            @endforeach
                        </select>
                        @error('dorm_student_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <div class="form-group">
                        <label for="bed_number">نمبر بستر</label>
                        <input id="bed_number" class="form-control @error('bed_number') is-invalid @enderror" name="bed_number" type="number" min="1" max="{{ $room->capacity }}" value="{{ old('bed_number') }}" required placeholder="مثلاً 1">
                        @error('bed_number') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    <button class="btn btn-primary btn-block" type="submit">افزودن به اتاق</button>
                </form>
            @else
                <div class="user-access-note">
                    <div class="preview-thumbnail">
                        <div class="preview-icon bg-danger"><span>!</span></div>
                    </div>
                    <p class="text-muted mb-0">این اتاق فعال نیست یا بستر خالی ندارد.</p>
                </div>
            @endif

            <div class="student-side-divider"></div>
            <h3 class="student-side-title">یادداشت اتاق</h3>
            <p class="student-muted-copy mb-0">{{ $room->notes ?: 'برای این اتاق یادداشتی ثبت نشده است.' }}</p>
        </aside>
    </div>
@endsection
