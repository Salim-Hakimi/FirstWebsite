@extends('layouts.site')

@section('title', 'Contact - Fanous')

@section('content')
    <section class="form-page">
        <div class="container form-shell">
            <aside class="form-intro">
                <h1>تماس با مدیریت فانوس</h1>
                <p>
                    برای پیگیری حساب‌ها، دسترسی کارمندان، ثبت محصلین یا بخش کتابخانه با مدیریت سیستم تماس بگیرید.
                </p>
                <ul class="form-note-list">
                    <li>مدیریت لیلیه</li>
                    <li>حساب‌های مالی</li>
                    <li>کتابخانه و کارت عضویت</li>
                </ul>
            </aside>

            <div class="form-card">
                <h1>راه‌های ارتباطی</h1>
                <p>این صفحه فعلاً برای نمایش اطلاعات تماس سیستم استفاده می‌شود.</p>

                <div class="request-list">
                    <article class="request-card">
                        <div class="request-card-header">
                            <div>
                                <h2>Fanous Dormitory Management</h2>
                                <p>داشبورد مرکزی برای لیلیه و کتابخانه</p>
                            </div>
                            <span class="status-badge">Active</span>
                        </div>
                        <div class="request-meta">
                            <div>
                                <strong>آدرس سیستم</strong>
                                <span>{{ url('/') }}</span>
                            </div>
                            <div>
                                <strong>ورود</strong>
                                <a href="{{ route('login') }}">صفحه ورود</a>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </div>
    </section>
@endsection
