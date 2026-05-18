<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\FinanceTransaction;
use App\Models\LibraryMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->canAccessAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('dashboard', [
            'roleLabel' => User::roleOptions()[$user->role] ?? $user->role,
            'cards' => $this->cardsFor($user),
            'libraryDashboard' => $user->role === User::ROLE_LIBRARIAN ? $this->libraryDashboardData() : null,
        ]);
    }

    private function libraryDashboardData(): array
    {
        $activeLoanStatuses = ['borrowed', 'late'];
        $libraryFinance = FinanceTransaction::query()
            ->whereHas('category', fn ($query) => $query->where('name', 'like', 'کتابخانه -%'));

        return [
            'activeMembers' => LibraryMember::where('status', 'active')->count(),
            'bookTitles' => Book::count(),
            'availableCopies' => (int) Book::sum('available_copies'),
            'activeLoans' => BookLoan::whereIn('status', $activeLoanStatuses)->count(),
            'overdueLoans' => BookLoan::query()
                ->with(['member', 'book'])
                ->whereIn('status', $activeLoanStatuses)
                ->whereDate('due_at', '<', today())
                ->orderBy('due_at')
                ->limit(6)
                ->get(),
            'feeFollowUps' => LibraryMember::query()
                ->where('status', 'active')
                ->whereDate('next_payment_due_at', '<=', today()->addDays(3))
                ->orderBy('next_payment_due_at')
                ->limit(6)
                ->get(),
            'recentLoans' => BookLoan::query()
                ->with(['member', 'book', 'copy'])
                ->latest()
                ->limit(6)
                ->get(),
            'todayIncome' => (int) (clone $libraryFinance)->where('type', 'income')->whereDate('transaction_date', today())->sum('amount'),
            'totalIncome' => (int) (clone $libraryFinance)->where('type', 'income')->sum('amount'),
            'totalExpense' => (int) (clone $libraryFinance)->where('type', 'expense')->sum('amount'),
        ];
    }

    private function cardsFor(User $user): array
    {
        return match ($user->role) {
            User::ROLE_ADMIN => [
                ['title' => 'داشبورد مدیریت', 'body' => 'ساخت کاربران کاری، مدیریت نقش‌ها و دیدن گزارش‌های کل سیستم.', 'url' => route('admin.dashboard')],
                ['title' => 'اتاق‌ها و ظرفیت', 'body' => 'ساخت اتاق‌های ۴، ۶ و ۸ نفره و دیدن جای خالی لیلیه.', 'url' => route('dorm.rooms.index')],
                ['title' => 'ثبت و مدیریت محصلین', 'body' => 'ثبت محصل، اسناد، ضامن، اتاق و وضعیت حضور در لیلیه.', 'url' => route('dorm.students.index')],
                ['title' => 'گزارش نماینده محصلین', 'body' => 'دیدن پول ماهانه، برق، آب، جریمه‌ها و ثبت‌های نماینده.', 'url' => route('representative.index')],
                ['title' => 'گزارش خرج‌آور', 'body' => 'دیدن دریافت پول غذا، خریدها، مصارف و باقی‌مانده حساب.', 'url' => route('purchaser.report')],
                ['title' => 'گزارش کتاب‌خانه', 'body' => 'دیدن اعضا، کتاب‌ها، امانت‌ها و برگشت کتاب بدون تغییر مستقیم ثبت‌های کتاب‌دار.', 'url' => route('library.index')],
                ['title' => 'تنظیمات حساب', 'body' => 'ویرایش معلومات پروفایل، ایمیل، شماره تماس و تغییر رمز عبور.', 'url' => route('settings.edit')],
            ],
            User::ROLE_STUDENT_REPRESENTATIVE => [
                ['title' => 'مشخصات محصلین', 'body' => 'دیدن معلومات کامل محصلین برای مدیریت نظم داخلی.', 'url' => route('dorm.students.index')],
                ['title' => 'ثبت پول و جریمه', 'body' => 'ثبت پول ماهانه، برق، آب و جریمه‌های محصلین.', 'url' => route('representative.index')],
            ],
            User::ROLE_PURCHASER => [
                ['title' => 'مشخصات محصلین', 'body' => 'دیدن معلومات محصلین برای جمع‌آوری پول غذا.', 'url' => route('dorm.students.index')],
                ['title' => 'حساب غذا', 'body' => 'ثبت دریافت پول غذا، خریدها و مصارف روزانه یا هفته‌وار.', 'url' => route('purchaser.index')],
            ],
            User::ROLE_LIBRARIAN => [
                ['title' => 'مدیریت کتاب‌خانه', 'body' => 'ثبت عضو، کتاب، امانت، برگشت کتاب و جریمه‌های مربوط کتاب‌خانه.', 'url' => route('library.index')],
            ],
            User::ROLE_GUARD => [
                ['title' => 'مشخصات محصلین', 'body' => 'دیدن معلومات ضروری محصلین برای کنترول ورود و خروج.', 'url' => route('dorm.students.index')],
            ],
            default => [
                ['title' => 'حساب فعال', 'body' => 'برای این نقش هنوز بخش کاری جدا ساخته نشده است.', 'url' => null],
            ],
        };
    }
}
