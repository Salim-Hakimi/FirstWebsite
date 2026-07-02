<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FinanceController as AdminFinanceController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AdminFinanceTransactionsController as ApiAdminFinanceTransactionsController;
use App\Http\Controllers\Api\AdminUsersController as ApiAdminUsersController;
use App\Http\Controllers\Api\DashboardSummaryController as ApiDashboardSummaryController;
use App\Http\Controllers\Api\DormRoomsController as ApiDormRoomsController;
use App\Http\Controllers\Api\DormStudentsController as ApiDormStudentsController;
use App\Http\Controllers\Api\LibraryBooksController as ApiLibraryBooksController;
use App\Http\Controllers\Api\LibraryLoansController as ApiLibraryLoansController;
use App\Http\Controllers\Api\LibraryMembersController as ApiLibraryMembersController;
use App\Http\Controllers\Api\PurchaserRecordsController as ApiPurchaserRecordsController;
use App\Http\Controllers\Api\RepresentativeCollectionsController as ApiRepresentativeCollectionsController;
use App\Http\Controllers\Api\SessionController as ApiSessionController;
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
use Illuminate\Support\Facades\Storage;

Route::get('/storage/{path}', function (string $path) {
    abort_if(str_contains($path, '..') || ! Storage::disk('public')->exists($path), 404);

    return Storage::disk('public')->response($path);
})->where('path', '.*')->name('storage.public');

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::get('/contact', function () {
    return view('contact');
})->name('contact');

Route::get('/transparency', TransparencyController::class)->name('transparency');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'createLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'storeLogin'])->middleware('throttle:login')->name('login.store');
    Route::get('/staff/setup', [AuthController::class, 'createRegister'])->name('staff.setup');
    Route::post('/staff/setup', [AuthController::class, 'storeRegister'])->middleware('throttle:3,1')->name('staff.setup.store');
});

Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/session', ApiSessionController::class)->name('session');
        Route::get('/dashboard/summary', ApiDashboardSummaryController::class)->name('dashboard.summary');
        Route::middleware('role:'.implode(',', User::dormRecordViewerRoles()))
            ->get('/dorm/students', ApiDormStudentsController::class)
            ->name('dorm.students');
        Route::middleware('role:'.implode(',', User::managementRoles()))
            ->get('/dorm/rooms', ApiDormRoomsController::class)
            ->name('dorm.rooms');
        Route::middleware('role:'.implode(',', User::libraryViewerRoles()))
            ->get('/library/members', ApiLibraryMembersController::class)
            ->name('library.members');
        Route::middleware('role:'.implode(',', User::libraryViewerRoles()))
            ->get('/library/books', ApiLibraryBooksController::class)
            ->name('library.books');
        Route::middleware('role:'.implode(',', User::libraryViewerRoles()))
            ->get('/library/loans', ApiLibraryLoansController::class)
            ->name('library.loans');
        Route::middleware('role:'.implode(',', User::managementRoles()))
            ->get('/admin/finance/transactions', ApiAdminFinanceTransactionsController::class)
            ->name('admin.finance.transactions');
        Route::middleware('role:'.implode(',', User::managementRoles()))
            ->get('/admin/users', ApiAdminUsersController::class)
            ->name('admin.users');
        Route::middleware('role:'.implode(',', User::purchaserRoles()))
            ->get('/purchaser/records', ApiPurchaserRecordsController::class)
            ->name('purchaser.records');
        Route::middleware('role:'.implode(',', User::studentRepresentativeRoles()))
            ->get('/representative/collections', ApiRepresentativeCollectionsController::class)
            ->name('representative.collections');
    });

    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings/profile', [SettingsController::class, 'updateProfile'])->middleware('throttle:10,1')->name('settings.profile.update');
    Route::put('/settings/password', [SettingsController::class, 'updatePassword'])->middleware('throttle:5,1')->name('settings.password.update');
    Route::get('/membership-cards/{card}/print', [MembershipCardController::class, 'show'])->name('membership-cards.print');

    Route::middleware('role:'.implode(',', User::dormRecordViewerRoles()))->prefix('dorm')->name('dorm.')->group(function () {
        Route::get('/students', [DormStudentController::class, 'index'])->name('students.index');
        Route::get('/students/{student}/details', [DormStudentController::class, 'show'])->name('students.show');
        Route::get('/students/{student}/documents/{index}', [DormStudentController::class, 'document'])->whereNumber('index')->name('students.documents.show');
    });

    Route::middleware('role:'.implode(',', User::managementRoles()))->prefix('dorm')->name('dorm.')->group(function () {
        Route::get('/rooms', [DormRoomController::class, 'index'])->name('rooms.index');
        Route::get('/rooms/create', [DormRoomController::class, 'create'])->name('rooms.create');
        Route::post('/rooms', [DormRoomController::class, 'store'])->middleware('throttle:20,1')->name('rooms.store');
        Route::get('/rooms/{room}', [DormRoomController::class, 'show'])->name('rooms.show');
        Route::post('/rooms/{room}/allocations', [DormRoomController::class, 'storeAllocation'])->middleware('throttle:20,1')->name('rooms.allocations.store');
        Route::put('/rooms/{room}/students/{student}/move', [DormRoomController::class, 'moveStudent'])->middleware('throttle:20,1')->name('rooms.students.move');
        Route::delete('/rooms/{room}/students/{student}', [DormRoomController::class, 'removeStudent'])->name('rooms.students.remove');
        Route::get('/rooms/{room}/edit', [DormRoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/rooms/{room}', [DormRoomController::class, 'update'])->middleware('throttle:20,1')->name('rooms.update');

        Route::get('/students/create', [DormStudentController::class, 'create'])->name('students.create');
        Route::post('/students', [DormStudentController::class, 'store'])->middleware('throttle:10,1')->name('students.store');
        Route::get('/students/{student}/edit', [DormStudentController::class, 'edit'])->name('students.edit');
        Route::put('/students/{student}', [DormStudentController::class, 'update'])->middleware('throttle:10,1')->name('students.update');
        Route::put('/students/{student}/admit', [DormStudentController::class, 'admit'])->name('students.admit');
        Route::post('/students/{student}/card', [DormStudentController::class, 'issueCard'])->middleware('throttle:20,1')->name('students.card.issue');
        Route::get('/students/{student}/registration-receipt', [DormStudentController::class, 'registrationReceipt'])->name('students.registration.receipt');

    });

    Route::middleware('role:'.implode(',', User::studentRepresentativeRoles()))->group(function () {
        Route::get('/representative', [StudentRepresentativeController::class, 'index'])->name('representative.index');
        Route::get('/representative/report', [StudentRepresentativeController::class, 'report'])->name('representative.report');
        Route::get('/representative/collections/{collection}/receipt', [StudentRepresentativeController::class, 'receipt'])->name('representative.collections.receipt');
    });

    Route::middleware('role:'.User::ROLE_STUDENT_REPRESENTATIVE)->group(function () {
        Route::post('/representative/collections', [StudentRepresentativeController::class, 'store'])->middleware('throttle:20,1')->name('representative.collections.store');
    });

    Route::middleware('role:'.implode(',', User::purchaserRoles()))->group(function () {
        Route::get('/purchaser', [PurchaserController::class, 'index'])->name('purchaser.index');
        Route::get('/purchaser/report', [PurchaserController::class, 'report'])->name('purchaser.report');
        Route::get('/purchaser/records/{record}/receipt', [PurchaserController::class, 'receipt'])->name('purchaser.records.receipt');
    });

    Route::middleware('role:'.User::ROLE_PURCHASER)->group(function () {
        Route::post('/purchaser/records', [PurchaserController::class, 'store'])->middleware('throttle:20,1')->name('purchaser.records.store');
    });

    Route::middleware('role:'.implode(',', User::libraryViewerRoles()))->group(function () {
        Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
        Route::get('/library/inventory', [LibraryController::class, 'inventoryReport'])->name('library.inventory.report');
        Route::get('/library/inventory/export', [LibraryController::class, 'inventoryExport'])->name('library.inventory.export');
        Route::get('/library/fee-reminders', [LibraryController::class, 'feeReminders'])->name('library.fee-reminders.index');
        Route::get('/library/members-export', [LibraryController::class, 'membersExport'])->name('library.members.export');
        Route::get('/library/finance', [LibraryController::class, 'index'])->name('library.finance.index');
        Route::get('/library/finance/export', [LibraryController::class, 'financeExport'])->name('library.finance.export');
        Route::get('/library/finance/transactions/{transaction}/receipt', [LibraryController::class, 'financeReceipt'])->name('library.finance.transactions.receipt');
        Route::get('/library/members/{member}', [LibraryController::class, 'showMember'])->name('library.members.show');
    });

    Route::middleware('role:'.User::ROLE_LIBRARIAN)->prefix('library')->name('library.')->group(function () {
        Route::post('/members', [LibraryController::class, 'storeMember'])->middleware('throttle:10,1')->name('members.store');
        Route::get('/members/{member}/edit', [LibraryController::class, 'editMember'])->name('members.edit');
        Route::put('/members/{member}', [LibraryController::class, 'updateMember'])->middleware('throttle:10,1')->name('members.update');
        Route::post('/members/{member}/card', [LibraryController::class, 'issueMemberCard'])->middleware('throttle:20,1')->name('members.card.issue');
        Route::post('/members/{member}/monthly-payment', [LibraryController::class, 'recordMonthlyPayment'])->middleware('throttle:20,1')->name('members.monthly-payment.store');
        Route::get('/members/{member}/monthly-payment/receipt', [LibraryController::class, 'monthlyPaymentReceipt'])->name('members.monthly-payment.receipt');
        Route::post('/members/{member}/fee-reminder', [LibraryController::class, 'markFeeReminderSent'])->name('members.fee-reminder.store');
        Route::post('/finance', [LibraryController::class, 'storeFinanceRecord'])->middleware('throttle:20,1')->name('finance.store');
        Route::post('/books', [LibraryController::class, 'storeBook'])->middleware('throttle:20,1')->name('books.store');
        Route::get('/books/{book}/edit', [LibraryController::class, 'editBook'])->name('books.edit');
        Route::get('/books/{book}/copy-labels', [LibraryController::class, 'copyLabels'])->name('books.copy-labels');
        Route::put('/books/{book}', [LibraryController::class, 'updateBook'])->middleware('throttle:20,1')->name('books.update');
        Route::put('/book-copies/{copy}', [LibraryController::class, 'updateBookCopy'])->name('book-copies.update');
        Route::post('/loans', [LibraryController::class, 'storeLoan'])->middleware('throttle:20,1')->name('loans.store');
        Route::post('/loans/return-by-copy', [LibraryController::class, 'returnLoanByCopy'])->middleware('throttle:20,1')->name('loans.return-by-copy');
        Route::get('/loans/{loan}/edit', [LibraryController::class, 'editLoan'])->name('loans.edit');
        Route::put('/loans/{loan}', [LibraryController::class, 'updateLoan'])->middleware('throttle:20,1')->name('loans.update');
        Route::put('/loans/{loan}/return', [LibraryController::class, 'returnLoan'])->middleware('throttle:20,1')->name('loans.return');
    });

    Route::middleware('role:'.implode(',', User::managementRoles()))->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::get('/finance', [AdminFinanceController::class, 'index'])->name('finance.index');
        Route::get('/finance/report', [AdminFinanceController::class, 'report'])->name('finance.report');
        Route::post('/finance/transactions', [AdminFinanceController::class, 'storeTransaction'])->middleware('throttle:20,1')->name('finance.transactions.store');
        Route::get('/finance/transactions/{transaction}/edit', [AdminFinanceController::class, 'editTransaction'])->name('finance.transactions.edit');
        Route::put('/finance/transactions/{transaction}', [AdminFinanceController::class, 'updateTransaction'])->middleware('throttle:20,1')->name('finance.transactions.update');
        Route::delete('/finance/transactions/{transaction}', [AdminFinanceController::class, 'destroyTransaction'])->middleware('throttle:10,1')->name('finance.transactions.destroy');
        Route::get('/finance/transactions/{transaction}/receipt', [AdminFinanceController::class, 'receipt'])->name('finance.transactions.receipt');
        Route::post('/finance/donors', [AdminFinanceController::class, 'storeDonor'])->middleware('throttle:20,1')->name('finance.donors.store');
        Route::post('/finance/projects', [AdminFinanceController::class, 'storeProject'])->middleware('throttle:20,1')->name('finance.projects.store');
        Route::get('/finance/export', [AdminFinanceController::class, 'export'])->name('finance.export');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->middleware('throttle:10,1')->name('users.store');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->middleware('throttle:10,1')->name('users.update');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->middleware('throttle:10,1')->name('users.destroy');
    });

});
