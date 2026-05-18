@extends('admin.layout')

@section('title', $room->exists ? 'ویرایش اتاق - ادمین فانوس' : 'ثبت اتاق - ادمین فانوس')

@section('content')
    @php
        $roomStatusNames = [
            'active' => 'فعال',
            'maintenance' => 'در ترمیم',
            'closed' => 'بسته',
        ];
    @endphp

    <div class="student-form-hero">
        <div>
            <span class="student-command-kicker">تنظیم اتاق</span>
            <h1>{{ $room->exists ? 'ویرایش اتاق' : 'ثبت اتاق' }}</h1>
            <p>نمبر اتاق، ظرفیت، منزل و وضعیت دسترسی را پیش از تخصیص شاگردان مشخص کنید.</p>
        </div>
        <div class="student-command-actions">
            <a href="{{ route('dorm.rooms.index') }}" class="btn btn-outline-secondary">برگشت به اتاق‌ها</a>
        </div>
    </div>

    <form method="POST" action="{{ $room->exists ? route('dorm.rooms.update', $room) : route('dorm.rooms.store') }}">
        @csrf
        @if ($room->exists)
            @method('PUT')
        @endif

        <div class="student-form-layout">
            <div class="student-form-main">
                <section class="student-form-section">
                    <div class="student-form-section-head">
                        <span>01</span>
                        <div>
                            <h2>پروفایل اتاق</h2>
                            <p>ظرفیت طبق معیار لیلیه فقط ۴، ۶ یا ۸ بستر پذیرفته می‌شود.</p>
                        </div>
                    </div>

                    <div class="student-form-grid">
                        <div class="form-group">
                            <label for="room_number">نمبر اتاق</label>
                            <input id="room_number" class="form-control @error('room_number') is-invalid @enderror" name="room_number" value="{{ old('room_number', $room->room_number) }}" required>
                            @error('room_number') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="capacity">ظرفیت</label>
                            <select id="capacity" class="form-control @error('capacity') is-invalid @enderror" name="capacity" required>
                                @foreach ([4, 6, 8] as $capacity)
                                    <option value="{{ $capacity }}" @selected((int) old('capacity', $room->capacity) === $capacity)>{{ $capacity }} بستر</option>
                                @endforeach
                            </select>
                            @error('capacity') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="floor">منزل</label>
                            <input id="floor" class="form-control @error('floor') is-invalid @enderror" name="floor" value="{{ old('floor', $room->floor) }}" placeholder="مثلاً: منزل دوم">
                            @error('floor') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="status">وضعیت</label>
                            <select id="status" class="form-control @error('status') is-invalid @enderror" name="status" required>
                                @foreach ($roomStatusNames as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $room->status) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </section>

                <section class="student-form-section">
                    <div class="student-form-section-head">
                        <span>02</span>
                        <div>
                            <h2>یادداشت مدیریت</h2>
                            <p>وضعیت، امکانات یا محدودیت‌های موقتی اتاق را برای تیم لیلیه ثبت کنید.</p>
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label for="notes">یادداشت‌ها</label>
                        <textarea id="notes" class="form-control @error('notes') is-invalid @enderror" name="notes" rows="6" placeholder="وضعیت اتاق، امکانات یا یادداشت‌های مدیریت">{{ old('notes', $room->notes) }}</textarea>
                        @error('notes') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </section>
            </div>

            <aside class="student-form-section is-sticky">
                <h3 class="student-side-title">قواعد ظرفیت</h3>
                <div class="student-status-preview">
                    <strong>اندازه‌های مجاز</strong>
                    <p>فقط اتاق‌های ۴، ۶ و ۸ بستره پذیرفته می‌شود.</p>
                </div>
                <div class="student-status-preview">
                    <strong>اتاق‌های فعال</strong>
                    <p>شاگردان فقط به اتاق‌های فعال تخصیص می‌شوند.</p>
                </div>
                <div class="student-status-preview">
                    <strong>به‌روزرسانی مصون</strong>
                    <p>ظرفیت نمی‌تواند کمتر از تعداد شاگردان فعلی باشد.</p>
                </div>

                <div class="student-save-panel">
                    <button class="btn btn-primary" type="submit">{{ $room->exists ? 'ذخیره اتاق' : 'ثبت اتاق' }}</button>
                    <a class="btn btn-outline-secondary" href="{{ route('dorm.rooms.index') }}">لغو</a>
                </div>
            </aside>
        </div>
    </form>
@endsection
