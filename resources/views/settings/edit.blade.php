@extends('admin.layout')

@section('title', 'Account Settings - Fanous Admin')

@section('content')
    @php
        $roleName = ucwords(str_replace('_', ' ', $user->role));
        $statusName = ucwords($user->status);
        $avatar = strtoupper(substr($user->name ?: 'F', 0, 1));
    @endphp

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card corona-gradient-card">
                <div class="card-body py-0 px-0 px-sm-3">
                    <div class="row align-items-center">
                        <div class="col-12 col-lg-8 py-4 px-4">
                            <h3 class="mb-1">Account Settings</h3>
                            <p class="mb-0 text-white-50">Update your profile information, contact details, display preference, and password.</p>
                        </div>
                        <div class="col-12 col-lg-4 text-lg-right px-4 pb-4 pb-lg-0">
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-rounded">Back to dashboard</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="row">
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        @if ($user->profile_photo_path)
                            <img class="fanous-avatar settings-avatar-large fanous-avatar-img mr-3" src="{{ asset('storage/'.$user->profile_photo_path) }}" alt="{{ $user->name }}">
                        @else
                            <span class="fanous-avatar settings-avatar-large mr-3">{{ $avatar }}</span>
                        @endif
                        <div>
                            <h5 class="mb-1">{{ $user->name }}</h5>
                            <p class="text-muted mb-0">{{ $roleName }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><h5 class="mb-1">{{ $statusName }}</h5><p class="text-muted mb-0">Access status</p></div></div>
        </div>
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><h5 class="mb-1">{{ $user->theme === 'dark' ? 'Dark' : 'Light' }}</h5><p class="text-muted mb-0">Display mode</p></div></div>
        </div>
        <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
            <div class="card"><div class="card-body"><h5 class="mb-1">{{ $user->email }}</h5><p class="text-muted mb-0">Login email</p></div></div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Profile information</h4>
                    <p class="card-description">This information appears in the dashboard and staff records.</p>

                    <form method="POST" action="{{ route('settings.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="name">Full name</label>
                                <input id="name" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="email">Email address</label>
                                <input id="email" class="form-control @error('email') is-invalid @enderror" name="email" type="email" value="{{ old('email', $user->email) }}" required>
                                @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="phone">Phone number</label>
                                <input id="phone" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Optional phone number">
                                @error('phone') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="profile_photo">Profile photo</label>
                                <input id="profile_photo" class="form-control @error('profile_photo') is-invalid @enderror" name="profile_photo" type="file" accept="image/*">
                                @error('profile_photo') <span class="text-danger small">{{ $message }}</span> @enderror
                                @if ($user->profile_photo_path)
                                    <label class="d-inline-flex align-items-center text-muted small mt-2">
                                        <input class="mr-2" name="remove_profile_photo" type="checkbox" value="1">
                                        Remove current photo
                                    </label>
                                @endif
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="theme">Display mode</label>
                                <select id="theme" class="form-control @error('theme') is-invalid @enderror" name="theme" required>
                                    <option value="dark" @selected(old('theme', $user->theme) === 'dark')>Dark</option>
                                    <option value="light" @selected(old('theme', $user->theme) === 'light')>Light</option>
                                </select>
                                @error('theme') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-12">
                                <button class="btn btn-primary mr-2" type="submit">Save profile</button>
                                <a class="btn btn-dark" href="{{ route('dashboard') }}">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Security</h4>
                    <p class="card-description">Use your current password before setting a new one.</p>

                    <form method="POST" action="{{ route('settings.password.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="current_password">Current password</label>
                            <input id="current_password" class="form-control @error('current_password') is-invalid @enderror" type="password" name="current_password" required>
                            @error('current_password') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">New password</label>
                            <input id="password" class="form-control @error('password') is-invalid @enderror" type="password" name="password" required>
                            @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirm new password</label>
                            <input id="password_confirmation" class="form-control" type="password" name="password_confirmation" required>
                        </div>

                        <button class="btn btn-primary" type="submit">Change password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
