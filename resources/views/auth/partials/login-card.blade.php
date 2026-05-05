<section class="fanous-admin-login-shell">
    <div class="fanous-login-topbar">
        <a class="fanous-login-control" href="{{ route('home') }}" aria-label="Home">
            <span class="fanous-login-control-icon fanous-login-home"></span>
            <span>Home</span>
        </a>
        <a class="fanous-login-control" href="{{ route('locale.switch', \App\Support\Locale::current() === 'fa' ? 'en' : 'fa') }}" aria-label="Switch language">
            <span class="fanous-login-control-icon fanous-login-lang">{{ \App\Support\Locale::current() === 'fa' ? 'EN' : 'FA' }}</span>
            <span>Language</span>
        </a>
    </div>

    <div class="fanous-admin-login-grid">
        <div class="fanous-login-visual" aria-hidden="true">
            <div class="fanous-login-brand">
                <div class="fanous-login-mark">F</div>
                <div>
                    <strong>Fanous</strong>
                    <span>Dormitory and library management</span>
                </div>
            </div>

            <div class="fanous-login-dashboard-preview">
                <span class="fanous-preview-bar"></span>
                <div class="fanous-preview-row">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <div class="fanous-preview-cards">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <div class="fanous-preview-table">
                    <span></span>
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>

            <div class="fanous-login-points">
                <span>Students</span>
                <span>Rooms</span>
                <span>Finance</span>
                <span>Library</span>
            </div>
        </div>

        <div class="fanous-login-card">
            <div class="fanous-login-card-head">
                <div class="fanous-login-mark compact">F</div>
                <p>Secure access</p>
                <h1>Login to Fanous</h1>
                <span>Use your staff account to continue.</span>
            </div>

            @if (session('status'))
                <div class="fanous-login-message">{{ session('status') }}</div>
            @endif

            @auth
                <div class="fanous-login-message">You are already signed in.</div>
                <div class="fanous-login-actions">
                    <a class="fanous-login-submit" href="{{ route('dashboard') }}">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="fanous-login-secondary" type="submit">Log out</button>
                    </form>
                </div>
            @else
                <form class="fanous-login-form" method="POST" action="{{ route('login.store') }}">
                    @csrf

                    <div class="fanous-login-field">
                        <label for="email">Email address</label>
                        <div class="fanous-login-input">
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" placeholder="name@example.com" autofocus>
                        </div>
                        @error('email')
                            <small>{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="fanous-login-field">
                        <label for="password">Password</label>
                        <div class="fanous-login-input">
                            <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="Enter your password">
                            <button type="button" class="fanous-login-eye" data-password-toggle aria-label="Show password">
                                <svg class="eye-open" width="18" height="18" viewBox="0 0 24 24" fill="none">
                                    <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6z" stroke="currentColor" stroke-width="1.8"/>
                                    <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
                                </svg>
                                <svg class="eye-closed" width="18" height="18" viewBox="0 0 24 24" fill="none">
                                    <path d="M4 4l16 16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M7 7.7C4.2 9.2 2.5 12 2.5 12s3.5 6 9.5 6c1.6 0 3-.4 4.2-1" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    <path d="M10 6.2A10 10 0 0112 6c6 0 9.5 6 9.5 6a15.4 15.4 0 01-2.3 2.8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>
                        @error('password')
                            <small>{{ $message }}</small>
                        @enderror
                    </div>

                    <label class="fanous-login-remember">
                        <input type="checkbox" name="remember" value="1">
                        <span>Remember me</span>
                    </label>

                    <button type="submit" class="fanous-login-submit">Sign in</button>
                </form>
            @endauth
        </div>
    </div>
</section>

<script>
    document.addEventListener('click', function (event) {
        const toggle = event.target.closest('[data-password-toggle]');

        if (! toggle) {
            return;
        }

        const input = toggle.closest('.fanous-login-input').querySelector('input');
        const isPassword = input.type === 'password';

        input.type = isPassword ? 'text' : 'password';
        toggle.classList.toggle('show-password', isPassword);
    });
</script>
