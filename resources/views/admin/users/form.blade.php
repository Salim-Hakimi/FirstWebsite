@extends('admin.layout')

@section('title', $user->exists ? 'ویرایش کاربر - ادمین فانوس' : 'ساخت کاربر - ادمین فانوس')

@section('content')
    @php
        $roleNames = \App\Models\User::roleOptions();

        $statusNames = [
            \App\Models\User::STATUS_ACTIVE => 'فعال',
            \App\Models\User::STATUS_PENDING => 'در انتظار',
            \App\Models\User::STATUS_SUSPENDED => 'مسدود',
        ];
    @endphp

    <div class="row">
        <div class="col-12 grid-margin stretch-card">
            <div class="card corona-gradient-card">
                <div class="card-body py-0 px-0 px-sm-3">
                    <div class="row align-items-center">
                        <div class="col-12 col-lg-8 py-4 px-4">
                            <h3 class="mb-1">{{ $user->exists ? 'ویرایش دسترسی کاربر' : 'ساخت دسترسی کاربر' }}</h3>
                            <p class="mb-0 text-white-50">هویت حساب، نقش، صلاحیت‌ها و وضعیت ورود کاربر را برای داشبورد فانوس تعیین کنید.</p>
                        </div>
                        <div class="col-12 col-lg-4 text-lg-right px-4 pb-4 pb-lg-0">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-light btn-rounded">برگشت به کاربران</a>
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
                        <h4 class="card-title">جزئیات حساب</h4>
                        <p class="card-description">معلومات اصلی ورود و تماس</p>

                        <div class="student-photo-uploader mb-4">
                            @if ($user->profile_photo_path)
                                <img src="{{ asset('storage/'.$user->profile_photo_path) }}" alt="{{ $user->name ?: 'عکس کاربر' }}">
                            @else
                                <span>{{ mb_substr($user->name ?: 'ک', 0, 1) }}</span>
                            @endif
                            <div class="flex-grow-1">
                                <label for="profile_photo">عکس پروفایل</label>
                                <input id="profile_photo" class="form-control @error('profile_photo') is-invalid @enderror" name="profile_photo" type="file" accept="image/*">
                                <span class="text-muted small">این عکس در سایدبار، لیست کاربران و پروفایل حساب نمایش داده می‌شود.</span>
                                @error('profile_photo') <span class="text-danger small d-block">{{ $message }}</span> @enderror
                                @if ($user->profile_photo_path)
                                    <label class="d-inline-flex align-items-center text-muted small mt-2">
                                        <input class="mr-2" name="remove_profile_photo" type="checkbox" value="1">
                                        حذف عکس فعلی
                                    </label>
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label for="name">نام کامل</label>
                                <input id="name" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name) }}" required>
                                @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="email">ایمیل</label>
                                <input id="email" class="form-control @error('email') is-invalid @enderror" name="email" type="email" value="{{ old('email', $user->email) }}" required>
                                @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="phone">شماره تماس</label>
                                <input id="phone" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $user->phone) }}" required>
                                @error('phone') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="password">{{ $user->exists ? 'رمز عبور جدید' : 'رمز عبور' }}</label>
                                <input id="password" class="form-control @error('password') is-invalid @enderror" name="password" type="password" @required(! $user->exists)>
                                @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
                                @if ($user->exists)
                                    <span class="text-muted small">برای نگه‌داشتن رمز فعلی، این فیلد را خالی بگذارید.</span>
                                @endif
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="password_confirmation">تکرار رمز عبور</label>
                                <input id="password_confirmation" class="form-control" name="password_confirmation" type="password" @required(! $user->exists)>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">کنترول دسترسی</h4>
                        <p class="card-description">نقش و وضعیت را با دقت انتخاب کنید.</p>

                        <div class="form-group">
                            <label for="role">نقش</label>
                            <select id="role" class="form-control @error('role') is-invalid @enderror" name="role" required>
                                @foreach ($roleOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('role', $user->role) === $value)>{{ $roleNames[$value] ?? $label }}</option>
                                @endforeach
                            </select>
                            @error('role') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="form-group">
                            <label for="status">وضعیت دسترسی</label>
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
                            <p class="text-muted mb-0">نقش مشخص می‌کند این کاربر کدام بخش‌های سیستم را باز و ویرایش کرده می‌تواند.</p>
                        </div>

                        <div class="d-flex flex-wrap mt-4">
                            <button class="btn btn-primary mr-2" type="submit">{{ $user->exists ? 'ذخیره تغییرات' : 'ساخت کاربر' }}</button>
                            <a class="btn btn-dark" href="{{ route('admin.users.index') }}">لغو</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
