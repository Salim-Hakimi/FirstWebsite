@extends('admin.layout')

@section('title', $user->exists ? 'Edit User - Fanous Admin' : 'Create User - Fanous Admin')

@section('content')
    @php
        $roleNames = [
            \App\Models\User::ROLE_OWNER => 'Owner',
            \App\Models\User::ROLE_MANAGER => 'Manager',
            \App\Models\User::ROLE_ADMIN => 'Admin',
            \App\Models\User::ROLE_GUARD => 'Guard',
            \App\Models\User::ROLE_STUDENT_REPRESENTATIVE => 'Student Representative',
            \App\Models\User::ROLE_PURCHASER => 'Purchaser',
            \App\Models\User::ROLE_LIBRARIAN => 'Librarian',
            \App\Models\User::ROLE_COOK => 'Cook',
            \App\Models\User::ROLE_DORM_STUDENT => 'Dorm Student',
            \App\Models\User::ROLE_LIBRARY_MEMBER => 'Library Member',
            \App\Models\User::ROLE_APPLICANT => 'Applicant',
        ];

        $statusNames = [
            \App\Models\User::STATUS_ACTIVE => 'Active',
            \App\Models\User::STATUS_PENDING => 'Pending',
            \App\Models\User::STATUS_SUSPENDED => 'Suspended',
        ];
    @endphp

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card corona-gradient-card">
                <div class="card-body py-0 px-0 px-sm-3">
                    <div class="row align-items-center">
                        <div class="col-12 col-lg-8 py-4 px-4">
                            <h3 class="mb-1">{{ $user->exists ? 'Edit User Access' : 'Create User Access' }}</h3>
                            <p class="mb-0 text-white-50">Set account identity, role permissions, and login status for the Fanous dashboard.</p>
                        </div>
                        <div class="col-12 col-lg-4 text-lg-right px-4 pb-4 pb-lg-0">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-light btn-rounded">Back to users</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($user->exists)
            @method('PUT')
        @endif

        <div class="row">
            <div class="col-lg-8 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Account details</h4>
                        <p class="card-description">Core login and contact information</p>

                        <div class="student-photo-uploader mb-4">
                            @if ($user->profile_photo_path)
                                <img src="{{ asset('storage/'.$user->profile_photo_path) }}" alt="{{ $user->name ?: 'User photo' }}">
                            @else
                                <span>{{ strtoupper(substr($user->name ?: 'U', 0, 1)) }}</span>
                            @endif
                            <div class="flex-grow-1">
                                <label for="profile_photo">Profile photo</label>
                                <input id="profile_photo" class="form-control @error('profile_photo') is-invalid @enderror" name="profile_photo" type="file" accept="image/*">
                                <span class="text-muted small">This photo appears in the sidebar, user list, and account profile.</span>
                                @error('profile_photo') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                                @if ($user->profile_photo_path)
                                    <label class="d-inline-flex align-items-center text-muted small mt-2">
                                        <input class="mr-2" name="remove_profile_photo" type="checkbox" value="1">
                                        Remove current photo
                                    </label>
                                @endif
                            </div>
                        </div>

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
                                <input id="phone" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $user->phone) }}" required>
                                @error('phone') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="password">{{ $user->exists ? 'New password' : 'Password' }}</label>
                                <input id="password" class="form-control @error('password') is-invalid @enderror" name="password" type="password" @required(! $user->exists)>
                                @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
                                @if ($user->exists)
                                    <span class="text-muted small">Leave empty to keep the current password.</span>
                                @endif
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="password_confirmation">Confirm password</label>
                                <input id="password_confirmation" class="form-control" name="password_confirmation" type="password" @required(! $user->exists)>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Access control</h4>
                        <p class="card-description">Choose role and status carefully</p>

                        <div class="form-group">
                            <label for="role">Role</label>
                            <select id="role" class="form-control @error('role') is-invalid @enderror" name="role" required>
                                @foreach ($roleOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $roleNames[$value] ?? $label }}</option>
                                @endforeach
                            </select>
                            @error('role') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="status">Access status</label>
                            <select id="status" class="form-control @error('status') is-invalid @enderror" name="status" required>
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $user->status) === $value)>{{ $statusNames[$value] ?? $label }}</option>
                                @endforeach
                            </select>
                            @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="user-access-note">
                            <div class="preview-thumbnail">
                                <div class="preview-icon bg-primary"><span>!</span></div>
                            </div>
                            <p class="text-muted mb-0">Role determines which database modules this user can open and modify.</p>
                        </div>

                        <div class="d-flex flex-wrap mt-4">
                            <button class="btn btn-primary mr-2" type="submit">{{ $user->exists ? 'Save changes' : 'Create user' }}</button>
                            <a class="btn btn-dark" href="{{ route('admin.users.index') }}">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
