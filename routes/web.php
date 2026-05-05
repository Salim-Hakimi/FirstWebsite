<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DormRoomController;
use App\Http\Controllers\DormStudentController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\MembershipCardController;
use App\Http\Controllers\PurchaserController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StudentRepresentativeController;
use App\Http\Controllers\TransparencyController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::get('/language/{locale}', function (string $locale) {
    abort_unless(in_array($locale, \App\Support\Locale::SUPPORTED, true), 404);

    session(['locale' => $locale]);
    app()->setLocale($locale);

    return back();
})->name('locale.switch');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');



Route::get('/transparency', TransparencyController::class)->name('transparency');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'createLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'storeLogin'])->name('login.store');
    Route::get('/staff/setup', [AuthController::class, 'createRegister'])->name('staff.setup');
    Route::post('/staff/setup', [AuthController::class, 'storeRegister'])->name('staff.setup.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password.update');
    Route::get('/membership-cards/{card}/print', [MembershipCardController::class, 'show'])->name('membership-cards.print');

    Route::middleware('role:'.implode(',', User::dormRecordViewerRoles()))->prefix('dorm')->name('dorm.')->group(function () {
        Route::get('/students', [DormStudentController::class, 'index'])->name('students.index');
        Route::get('/students/{student}/details', [DormStudentController::class, 'show'])->name('students.show');
    });

    Route::middleware('role:'.implode(',', User::managementRoles()))->prefix('dorm')->name('dorm.')->group(function () {
        Route::get('/rooms', [DormRoomController::class, 'index'])->name('rooms.index');
        Route::get('/rooms/create', [DormRoomController::class, 'create'])->name('rooms.create');
        Route::post('/rooms', [DormRoomController::class, 'store'])->name('rooms.store');
        Route::get('/rooms/{room}', [DormRoomController::class, 'show'])->name('rooms.show');
        Route::post('/rooms/{room}/allocations', [DormRoomController::class, 'storeAllocation'])->name('rooms.allocations.store');
        Route::put('/rooms/{room}/students/{student}/move', [DormRoomController::class, 'moveStudent'])->name('rooms.students.move');
        Route::delete('/rooms/{room}/students/{student}', [DormRoomController::class, 'removeStudent'])->name('rooms.students.remove');
        Route::get('/rooms/{room}/edit', [DormRoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/rooms/{room}', [DormRoomController::class, 'update'])->name('rooms.update');

        Route::get('/students/create', [DormStudentController::class, 'create'])->name('students.create');
        Route::post('/students', [DormStudentController::class, 'store'])->name('students.store');
        Route::get('/students/{student}/edit', [DormStudentController::class, 'edit'])->name('students.edit');
        Route::put('/students/{student}', [DormStudentController::class, 'update'])->name('students.update');
        Route::put('/students/{student}/admit', [DormStudentController::class, 'admit'])->name('students.admit');
        Route::post('/students/{student}/card', [DormStudentController::class, 'issueCard'])->name('students.card.issue');

    });

    Route::middleware('role:'.implode(',', User::studentRepresentativeRoles()))->group(function () {
        Route::get('/representative', [StudentRepresentativeController::class, 'index'])->name('representative.index');
        Route::get('/representative/report', [StudentRepresentativeController::class, 'report'])->name('representative.report');
        Route::get('/representative/collections/{collection}/receipt', [StudentRepresentativeController::class, 'receipt'])->name('representative.collections.receipt');
    });

    Route::middleware('role:'.User::ROLE_STUDENT_REPRESENTATIVE)->group(function () {
        Route::post('/representative/collections', [StudentRepresentativeController::class, 'store'])->name('representative.collections.store');
    });

    Route::middleware('role:'.implode(',', User::purchaserRoles()))->group(function () {
        Route::get('/purchaser', [PurchaserController::class, 'index'])->name('purchaser.index');
        Route::get('/purchaser/report', [PurchaserController::class, 'report'])->name('purchaser.report');
        Route::get('/purchaser/records/{record}/receipt', [PurchaserController::class, 'receipt'])->name('purchaser.records.receipt');
    });

    Route::middleware('role:'.User::ROLE_PURCHASER)->group(function () {
        Route::post('/purchaser/records', [PurchaserController::class, 'store'])->name('purchaser.records.store');
    });

    Route::middleware('role:'.implode(',', User::libraryViewerRoles()))->group(function () {
        Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
        Route::get('/library/members/{member}', [LibraryController::class, 'showMember'])->name('library.members.show');
    });

    Route::middleware('role:'.User::ROLE_LIBRARIAN)->prefix('library')->name('library.')->group(function () {
        Route::post('/members', [LibraryController::class, 'storeMember'])->name('members.store');
        Route::get('/members/{member}/edit', [LibraryController::class, 'editMember'])->name('members.edit');
        Route::put('/members/{member}', [LibraryController::class, 'updateMember'])->name('members.update');
        Route::post('/members/{member}/card', [LibraryController::class, 'issueMemberCard'])->name('members.card.issue');
        Route::post('/books', [LibraryController::class, 'storeBook'])->name('books.store');
        Route::get('/books/{book}/edit', [LibraryController::class, 'editBook'])->name('books.edit');
        Route::put('/books/{book}', [LibraryController::class, 'updateBook'])->name('books.update');
        Route::post('/loans', [LibraryController::class, 'storeLoan'])->name('loans.store');
        Route::get('/loans/{loan}/edit', [LibraryController::class, 'editLoan'])->name('loans.edit');
        Route::put('/loans/{loan}', [LibraryController::class, 'updateLoan'])->name('loans.update');
        Route::put('/loans/{loan}/return', [LibraryController::class, 'returnLoan'])->name('loans.return');
    });

    Route::middleware('role:'.implode(',', User::managementRoles()))->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    });

   
});





Route::get('/portfolio/{firstName}',function($firstName){
    return $firstName;    });
