@extends('layouts.site')

@section('title', 'فانوس')

@section('content')
    <section class="hero">
        <div class="container hero-inner">
            <div>
                <p class="eyebrow">سیستم مدیریت فانوس</p>
                <h1>مدیریت لیلیه و کتابخانه در یک سیستم</h1>
                <p class="hero-copy">
                    فانوس برای ثبت محصلین، اتاق‌ها، حساب‌های مالی، نماینده محصلین، خریدها و کتابخانه ساخته شده است.
                </p>
                <div class="hero-actions">
                    @auth
                        <a class="primary-button" href="{{ route('dashboard') }}">باز کردن داشبورد</a>
                    @else
                        <a class="primary-button" href="{{ route('login') }}">ورود به سیستم</a>
                        @unless ($hasUsers ?? true)
                            <a class="secondary-button" href="{{ route('staff.setup') }}">ساخت حساب اول</a>
                        @endunless
                    @endauth
                    <a class="secondary-button" href="{{ route('contact') }}">تماس</a>
                </div>
            </div>

            <div class="program-card">
                <div class="program-visual">
                    <div class="program-panel">
                        <div>
                            <span class="section-label">فانوس</span>
                            <h2 class="program-title">کنترل منظم اطلاعات روزانه</h2>
                        </div>
                        <div class="program-tags">
                            <span>محصلین</span>
                            <span>مالی</span>
                            <span>کتابخانه</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="stats">
        <div class="container stat-row">
            <article class="stat-card">
                <strong>دسترسی</strong>
                <span>دسترسی بر اساس نقش کاربر</span>
            </article>
            <article class="stat-card">
                <strong>زنده</strong>
                <span>اطلاعات از دیتابیس خوانده می‌شود</span>
            </article>
            <article class="stat-card">
                <strong>کارت‌ها</strong>
                <span>صدور کارت عضویت و لیلیه</span>
            </article>
            <article class="stat-card">
                <strong>گزارش‌ها</strong>
                <span>گزارش‌های مالی و عملیاتی</span>
            </article>
        </div>
    </section>
@endsection
