<section class="simple-login-page">
    <div class="simple-login-card">
        <div class="simple-login-head">
            <div class="simple-login-logo">
                <img src="{{ asset('logo/logo.jpg') }}" alt="Fanous Logo">
            </div>
            <h1>ورود</h1>
            <p>معلومات حساب خود را وارد کنید.</p>
        </div>

        @if (session('status'))
            <div class="simple-login-message">{{ session('status') }}</div>
        @endif

        @auth
            <div class="simple-login-message">شما قبلاً وارد سیستم شده‌اید.</div>
            <div class="simple-login-actions">
                <a class="simple-login-button" href="{{ route('dashboard') }}">داشبورد</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="simple-login-button secondary" type="submit">خروج</button>
                </form>
            </div>
        @else
            <form class="simple-login-form" method="POST" action="{{ route('login.store') }}">
                @csrf

                <label class="simple-login-field" for="email">
                    <span>ایمیل</span>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                    @error('email')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="simple-login-field" for="password">
                    <span>رمز عبور</span>
                    <input id="password" name="password" type="password" required autocomplete="current-password">
                    @error('password')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="simple-login-remember">
                    <input type="checkbox" name="remember" value="1">
                    <span>مرا به خاطر بسپار</span>
                </label>

                <button type="submit" class="simple-login-button">ورود</button>
            </form>
        @endauth

        <div class="developer-credit developer-credit-login">
            <span>طراحی و توسعه توسط</span>
            <img src="{{ asset('logo/company-logo-small.png') }}" alt="Company Logo">
        </div>
    </div>
</section>
