@extends('admin.layout')

@section('title', 'تنظیمات حساب - ادمین فانوس')
@section('content_wrapper_class', 'fanous-dashboard-wrapper')

@section('content')
    @php
        $roleLabels = \App\Models\User::roleOptions();
        $statusLabels = [
            'pending' => 'در انتظار تایید',
            'active' => 'فعال',
            'suspended' => 'مسدود',
        ];
        $statusTones = [
            'pending' => 'warning',
            'active' => 'success',
            'suspended' => 'danger',
        ];

        $roleName = $roleLabels[$user->role] ?? $user->role;
        $statusName = $statusLabels[$user->status] ?? $user->status;
        $statusTone = $statusTones[$user->status] ?? 'primary';
        $avatar = mb_substr($user->name ?: 'ف', 0, 1);
        $themeName = $user->theme === 'dark' ? 'تاریک' : 'روشن';
    @endphp

    <div class="fanous-settings-page" dir="rtl">
        <section class="fanous-page-header">
            <div>
                <span class="dashboard-section-kicker">حساب کاربری</span>
                <h1>تنظیمات حساب</h1>
                <p>معلومات پروفایل، حساب، حالت نمایش و رمز عبور را ویرایش کنید.</p>
            </div>

            <div class="fanous-page-actions">
                <x-ds.button type="submit" form="settings-profile-form">ذخیره تغییرات</x-ds.button>
                <x-ds.button variant="outline" :href="route('dashboard')">بازگشت به داشبورد</x-ds.button>
            </div>
        </section>

        @if ($errors->any())
            <div class="fanous-settings-alert">
                <span>!</span>
                <p>{{ $errors->first() }}</p>
            </div>
        @endif

        @if (session('status'))
            <div class="fanous-settings-alert is-success">
                <span>✓</span>
                <p>{{ session('status') }}</p>
            </div>
        @endif

        <section class="dashboard-stat-grid" aria-label="خلاصه حساب">
            <article class="dashboard-stat">
                <div>
                    <span>نام کاربر</span>
                    <strong>{{ $user->name }}</strong>
                    <small>نام نمایشی در سیستم</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="user" /></span>
            </article>

            <article class="dashboard-stat">
                <div>
                    <span>نقش کاربر</span>
                    <strong>{{ $roleName }}</strong>
                    <small>سطح دسترسی فعلی حساب</small>
                </div>
                <span class="dashboard-stat-icon"><x-ds.icon name="shield" /></span>
            </article>

            <article class="dashboard-stat">
                <div>
                    <span>ایمیل</span>
                    <strong class="ltr-text">{{ $user->email }}</strong>
                    <small>ایمیل ورود به سیستم</small>
                </div>
                <span class="dashboard-stat-icon is-blue"><x-ds.icon name="logs" /></span>
            </article>

            <article class="dashboard-stat">
                <div>
                    <span>وضعیت حساب</span>
                    <strong>{{ $statusName }}</strong>
                    <small>حالت نمایش: {{ $themeName }}</small>
                </div>
                <span class="dashboard-stat-icon {{ $user->status === 'suspended' ? 'is-danger' : '' }}"><x-ds.icon name="settings" /></span>
            </article>
        </section>

        <section class="fanous-settings-layout">
            <div class="fanous-settings-main">
                <article class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">پروفایل</span>
                            <h2>معلومات پروفایل</h2>
                            <p>این معلومات در داشبورد و ثبت‌های مربوط به کاربر نمایش داده می‌شود.</p>
                        </div>
                    </div>

                    <form id="settings-profile-form" method="POST" action="{{ route('settings.profile.update') }}" enctype="multipart/form-data" class="fanous-settings-form">
                        @csrf
                        @method('PUT')

                        <label>
                            <span>نام کامل</span>
                            <input id="name" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name) }}" required>
                            @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                        </label>

                        <label>
                            <span>ایمیل</span>
                            <input id="email" class="form-control ltr-text @error('email') is-invalid @enderror" name="email" type="email" value="{{ old('email', $user->email) }}" required>
                            @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                        </label>

                        <label>
                            <span>شماره تماس</span>
                            <input id="phone" class="form-control ltr-text @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="شماره تماس اختیاری">
                            @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
                        </label>

                        <label>
                            <span>نقش / نوع حساب</span>
                            <input class="form-control" value="{{ $roleName }}" disabled>
                        </label>

                        <label>
                            <span>حالت نمایش</span>
                            <select id="theme" class="form-control @error('theme') is-invalid @enderror" name="theme" required>
                                <option value="dark" @selected(old('theme', $user->theme) === 'dark')>تاریک</option>
                                <option value="light" @selected(old('theme', $user->theme) === 'light')>روشن</option>
                            </select>
                            @error('theme') <small class="text-danger">{{ $message }}</small> @enderror
                        </label>

                        <label class="fanous-settings-file">
                            <span>عکس پروفایل</span>
                            <input id="profile_photo" class="form-control @error('profile_photo') is-invalid @enderror" name="profile_photo" type="file" accept="image/*">
                            @error('profile_photo') <small class="text-danger">{{ $message }}</small> @enderror
                        </label>

                        @if ($user->profile_photo_path)
                            <label class="fanous-settings-check">
                                <input name="remove_profile_photo" type="checkbox" value="1">
                                <span>حذف عکس فعلی</span>
                            </label>
                        @endif

                        <div class="fanous-form-actions">
                            <x-ds.button type="submit">ذخیره پروفایل</x-ds.button>
                            <x-ds.button variant="outline" :href="route('dashboard')">لغو</x-ds.button>
                        </div>
                    </form>
                </article>

                <article class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">امنیت</span>
                            <h2>تغییر رمز عبور</h2>
                            <p>برای امنیت بیشتر، رمز عبور قوی انتخاب کنید.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('settings.password.update') }}" class="fanous-settings-form fanous-settings-password-form">
                        @csrf
                        @method('PUT')

                        <label>
                            <span>رمز عبور فعلی</span>
                            <input id="current_password" class="form-control @error('current_password') is-invalid @enderror" type="password" name="current_password" required>
                            @error('current_password') <small class="text-danger">{{ $message }}</small> @enderror
                        </label>

                        <label>
                            <span>رمز عبور جدید</span>
                            <input id="password" class="form-control @error('password') is-invalid @enderror" type="password" name="password" required>
                            @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                        </label>

                        <label>
                            <span>تکرار رمز عبور جدید</span>
                            <input id="password_confirmation" class="form-control" type="password" name="password_confirmation" required>
                        </label>

                        <div class="fanous-settings-info">
                            <span>i</span>
                            <p>رمز عبور باید حداقل ۸ حرف داشته باشد و بهتر است شامل حروف، عدد و نشانه باشد.</p>
                        </div>

                        <div class="fanous-form-actions">
                            <x-ds.button type="submit">تغییر رمز عبور</x-ds.button>
                        </div>
                    </form>
                </article>
            </div>

            <aside class="fanous-settings-sidebar">
                <article class="dashboard-panel fanous-account-preview">
                    <div class="fanous-account-avatar-wrap">
                        @if ($user->profile_photo_path)
                            <img class="fanous-account-avatar" src="{{ asset('storage/'.$user->profile_photo_path) }}" alt="{{ $user->name }}">
                        @else
                            <span class="fanous-account-avatar">{{ $avatar }}</span>
                        @endif
                    </div>
                    <h2>{{ $user->name }}</h2>
                    <x-ds.badge :tone="$statusTone">{{ $statusName }}</x-ds.badge>
                    <p class="ltr-text">{{ $user->email }}</p>
                    <div class="fanous-account-preview-list">
                        <div><span>نقش</span><strong>{{ $roleName }}</strong></div>
                        <div><span>شماره تماس</span><strong class="ltr-text">{{ $user->phone ?: 'ثبت نشده' }}</strong></div>
                        <div><span>حالت نمایش</span><strong>{{ $themeName }}</strong></div>
                        <div><span>آخرین ورود</span><strong>ثبت نشده</strong></div>
                    </div>
                </article>

                <article class="dashboard-panel">
                    <div class="dashboard-panel-header">
                        <div>
                            <span class="dashboard-section-kicker">راهنمای امنیت</span>
                            <h2>نکات امنیتی</h2>
                        </div>
                    </div>

                    <div class="fanous-security-tips">
                        <div><span>۱</span><p>رمز عبور خود را با دیگران شریک نسازید.</p></div>
                        <div><span>۲</span><p>بعد از ختم کار از سیستم خارج شوید.</p></div>
                        <div><span>۳</span><p>از رمز عبور قوی استفاده کنید.</p></div>
                    </div>
                </article>
            </aside>
        </section>
    </div>
@endsection
