@extends('layouts.site')

@section('title', 'راه‌اندازی اولیه - فانوس')

@section('content')
    <section class="form-page">
        <div class="container form-shell">
            <div class="form-intro">
                <p class="consultation-label" style="color: var(--green)">راه‌اندازی اولیه سیستم</p>
                <h1>ساخت حساب صاحب سیستم</h1>
                <p>
                    این صفحه فقط زمانی کار می‌کند که هنوز هیچ کاربری در سیستم ساخته نشده باشد. بعد از ساخت اولین حساب، ثبت‌نام عمومی بسته می‌شود و حساب‌های کاری توسط مدیریت ساخته می‌شوند.
                </p>
            </div>

            <div class="form-card">
                <h1>معلومات صاحب سیستم</h1>
                <p>این حساب به پنل مدیریت کامل دسترسی خواهد داشت.</p>

                <form method="POST" action="{{ route('staff.setup.store') }}" class="form-grid">
                    @csrf

                    <div class="field full">
                        <label for="name">نام کامل</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus>
                        @error('name')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field full">
                        <label for="email">ایمیل</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                        @error('email')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field full">
                        <label for="phone">شماره تماس</label>
                        <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required>
                        @error('phone')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password">رمز عبور</label>
                        <input id="password" name="password" type="password" required>
                        @error('password')
                            <span class="error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password_confirmation">تکرار رمز عبور</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required>
                    </div>

                    <div class="form-footer full">
                        <button class="primary-button" type="submit">ساخت حساب صاحب سیستم</button>
                        <a class="login-button" href="{{ route('login') }}">ورود کارکنان</a>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
