<section class="lf-saas-page">
    <div class="lf-saas-card">
        <div class="lf-saas-header">
            <div class="lf-saas-logo">
                <span>F</span>
            </div>
            <h1>Sign in to Dashboard</h1>
            <p>Welcome back! Please sign in to continue</p>
        </div>

        @if (session('status'))
            <div class="lf-saas-message">{{ session('status') }}</div>
        @endif

        @auth
            <div class="lf-saas-message">You are already signed in.</div>
            <div class="lf-saas-auth-actions">
                <a class="lf-saas-submit" href="{{ route('dashboard') }}">Dashboard</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="lf-saas-secondary" type="submit">Log out</button>
                </form>
            </div>
        @else
            <form class="lf-saas-form" method="POST" action="{{ route('login.store') }}">
                @csrf

                <div class="lf-saas-group @error('email') error @enderror">
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" placeholder=" " autofocus>
                    <label for="email">Email address</label>
                    <span class="lf-saas-border"></span>
                    @error('email')
                        <span class="lf-saas-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="lf-saas-group @error('password') error @enderror">
                    <input id="password" name="password" type="password" required autocomplete="current-password" placeholder=" ">
                    <label for="password">Password</label>
                    <button type="button" class="lf-saas-password-toggle" data-password-toggle aria-label="Show password">
                        <svg class="eye-open" width="17" height="17" viewBox="0 0 24 24" fill="none">
                            <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z" stroke="currentColor" stroke-width="1.8"/>
                            <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
                        </svg>
                        <svg class="eye-closed" width="17" height="17" viewBox="0 0 24 24" fill="none">
                            <path d="M4 4l16 16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M7 7.7C4.2 9.2 2.5 12 2.5 12s3.5 6 9.5 6c1.6 0 3-.4 4.2-1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M10 6.2A10 10 0 0112 6c6 0 9.5 6 9.5 6a15.4 15.4 0 01-2.3 2.8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                    </button>
                    <span class="lf-saas-border"></span>
                    @error('password')
                        <span class="lf-saas-error">{{ $message }}</span>
                    @enderror
                </div>

                <label class="lf-saas-remember">
                    <input type="checkbox" name="remember" value="1">
                    <span class="lf-saas-checkmark">
                        <svg width="10" height="8" viewBox="0 0 10 8" fill="none">
                            <path d="M1 4l2.5 2.5L9 1" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    Remember me
                </label>

                <button type="submit" class="lf-saas-submit">Sign in</button>
            </form>
        @endauth
    </div>
</section>

<script>
    document.addEventListener('click', function (event) {
        const toggle = event.target.closest('[data-password-toggle]');

        if (! toggle) {
            return;
        }

        const input = toggle.closest('.lf-saas-group').querySelector('input');
        const isPassword = input.type === 'password';

        input.type = isPassword ? 'text' : 'password';
        toggle.classList.toggle('show-password', isPassword);
    });
</script>
