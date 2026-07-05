<!DOCTYPE html>
<html lang="fa" dir="rtl" data-theme="{{ auth()->user()?->theme === 'light' ? 'light' : 'dark' }}">
    @php
        use App\Support\FrontendNavigation;

        $currentUser = auth()->user();
        $frontendContext = [
            'user' => [
                'name' => $currentUser->name,
                'email' => $currentUser->email,
                'role' => $currentUser->role,
                'theme' => $currentUser->theme,
                'profile_photo_url' => $currentUser->profile_photo_path ? asset('storage/'.$currentUser->profile_photo_path) : null,
            ],
            'navigation' => FrontendNavigation::forUser($currentUser),
            'csrfToken' => csrf_token(),
            'logoutUrl' => route('logout'),
            'legacyDashboardUrl' => $currentUser->canAccessAdmin() ? route('admin.dashboard') : route('dashboard'),
        ];
    @endphp
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>پنل Vue فانوس</title>
        <script>
            (function () {
                const theme = localStorage.getItem('fanous.theme') || '{{ $currentUser->theme === 'light' ? 'light' : 'dark' }}';
                localStorage.setItem('fanous.lang', 'fa');
                document.documentElement.lang = 'fa';
                document.documentElement.dir = 'rtl';
                document.documentElement.dataset.theme = theme === 'light' ? 'light' : 'dark';
            })();
        </script>
        <link rel="stylesheet" href="{{ asset('corona/assets/css/style.css') }}">
        <link rel="stylesheet" href="{{ asset('css/corona-admin-overrides.css') }}">
        @vite('resources/js/app.js')
    </head>
    <body>
        <script type="application/json" id="fanous-vue-context">
            {!! json_encode($frontendContext, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
        </script>
        <div
            id="fanous-vue-root"
            data-vue-app="spa"
            data-vue-base="/app"
            data-vue-context-id="fanous-vue-context"
        ></div>
        <script src="{{ asset('js/fanous-page-modal.js') }}"></script>
    </body>
</html>
