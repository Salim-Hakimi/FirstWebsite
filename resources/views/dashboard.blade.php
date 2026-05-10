@extends('admin.layout')

@section('title', 'Dashboard - Fanous Admin')

@section('content')
    <section class="student-command-shell">
        <div class="student-command-copy">
            <span class="student-command-kicker">Fanous workspace</span>
            <h1>{{ $roleLabel }}</h1>
            <p>Choose one of the panels available for your account role.</p>
        </div>
        <div class="student-command-actions">
            <a class="btn btn-outline-secondary" href="{{ route('settings.edit') }}">Account settings</a>
        </div>
    </section>

    <section class="student-workspace-panel">
        <div class="student-panel-head">
            <div>
                <span class="student-panel-label">Access</span>
                <h2>Your work areas</h2>
                <p>Only the modules allowed for your role are shown here.</p>
            </div>
        </div>

        <div class="admin-shortcut-grid">
            @forelse ($cards as $card)
                @if (! empty($card['url']))
                    <a href="{{ $card['url'] }}">
                        <span>{{ strtoupper(substr($card['title'], 0, 1)) }}</span>
                        <strong>{{ $card['title'] }}</strong>
                        <em>{{ $card['body'] }}</em>
                    </a>
                @else
                    <div class="student-directory-empty">
                        <strong>{{ $card['title'] }}</strong>
                        <p>{{ $card['body'] }}</p>
                    </div>
                @endif
            @empty
                <div class="student-directory-empty">
                    <strong>No panels available</strong>
                    <p>Please contact the administrator to review your role access.</p>
                </div>
            @endforelse
        </div>
    </section>
@endsection
