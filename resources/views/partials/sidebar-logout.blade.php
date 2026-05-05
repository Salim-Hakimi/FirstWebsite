@auth
    <form class="sidebar-logout" method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="{{ $buttonClass ?? 'gui-btn' }}" type="submit">خروج</button>
    </form>
@endauth
