<section class="simple-login-page">
    <div class="simple-login-card">
        <div class="simple-login-head">
            <div class="simple-login-logo">F</div>
            <h1>Login</h1>
            <p>Enter your account details.</p>
        </div>

        @if (session('status'))
            <div class="simple-login-message">{{ session('status') }}</div>
        @endif

        @auth
            <div class="simple-login-message">You are already signed in.</div>
            <div class="simple-login-actions">
                <a class="simple-login-button" href="{{ route('dashboard') }}">Dashboard</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="simple-login-button secondary" type="submit">Log out</button>
                </form>
            </div>
        @else
            <form class="simple-login-form" method="POST" action="{{ route('login.store') }}">
                @csrf

                <label class="simple-login-field" for="email">
                    <span>Email</span>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                    @error('email')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="simple-login-field" for="password">
                    <span>Password</span>
                    <input id="password" name="password" type="password" required autocomplete="current-password">
                    @error('password')
                        <small>{{ $message }}</small>
                    @enderror
                </label>

                <label class="simple-login-remember">
                    <input type="checkbox" name="remember" value="1">
                    <span>Remember me</span>
                </label>

                <button type="submit" class="simple-login-button">Sign in</button>
            </form>
        @endauth
    </div>
</section>
