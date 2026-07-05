<?php

namespace App\Support;

use Carbon\CarbonInterface;
use DateTimeInterface;

class Locale
{
    public const DEFAULT = 'fa';

    public const SUPPORTED = ['fa'];

    public static function current(): string
    {
        $locale = app()->getLocale();

        return in_array($locale, self::SUPPORTED, true) ? $locale : self::DEFAULT;
    }

    public static function isRtl(): bool
    {
        return self::current() === 'fa';
    }

    public static function dir(): string
    {
        return self::isRtl() ? 'rtl' : 'ltr';
    }

    public static function number(int|float|string|null $value, int $decimals = 0): string
    {
        if ($value === null || $value === '') {
            return '۰';
        }

        $text = is_numeric($value)
            ? number_format((float) $value, $decimals, '.', ',')
            : (string) $value;

        return self::toPersianDigits($text);
    }

    public static function money(int|float|string|null $value): string
    {
        return self::number($value).' افغانی';
    }

    public static function date(DateTimeInterface|string|null $value): string
    {
        if (! $value) {
            return 'ثبت نشده';
        }

        if ($value instanceof CarbonInterface || $value instanceof DateTimeInterface) {
            $year = (int) $value->format('Y');
            $month = (int) $value->format('m');
            $day = (int) $value->format('d');
        } else {
            $timestamp = strtotime($value);

            if ($timestamp === false) {
                return (string) $value;
            }

            $year = (int) date('Y', $timestamp);
            $month = (int) date('m', $timestamp);
            $day = (int) date('d', $timestamp);
        }

        [$jy, $jm, $jd] = self::gregorianToJalali($year, $month, $day);

        return self::toPersianDigits(sprintf('%04d/%02d/%02d ه.ش', $jy, $jm, $jd));
    }

    public static function percent(int|float|string|null $value): string
    {
        return self::number($value).'٪';
    }

    public static function label(?string $locale = null): string
    {
        return match ($locale ?? self::current()) {
            default => 'فارسی',
        };
    }

    public static function translate(string $text, ?string $locale = null): string
    {
        $locale ??= self::current();

        return $locale === 'en'
            ? (self::translations()[$text] ?? $text)
            : $text;
    }

    public static function translateHtml(string $html, ?string $locale = null): string
    {
        $locale ??= self::current();

        if ($locale !== 'en') {
            return $html;
        }

        $map = $locale === 'en' ? self::translations() : self::reverseTranslations();

        if ($locale === 'fa') {
            $map += [
                'Fanous Dormitory System' => 'سیستم لیلیه فانوس',
                'Welcome Back' => 'خوش آمدید',
                'Sign in to continue' => 'برای ادامه وارد شوید',
                'You are already signed in.' => 'شما قبلاً وارد سیستم شده‌اید.',
                'DASHBOARD' => 'داشبورد',
                'LOG OUT' => 'خروج',
                'LOG IN' => 'ورود',
                'Show password' => 'نمایش رمز عبور',
                'Dorm Students' => 'محصلین لیلیه',
                'Management' => 'مدیریت',
            ];
        }

        return self::translateHtmlSegments($html, $map);
    }

    private static function translateHtmlSegments(string $html, array $map): string
    {
        $parts = preg_split(
            '/(<script\b[^>]*>.*?<\/script>|<style\b[^>]*>.*?<\/style>|<[^>]+>)/is',
            $html,
            -1,
            PREG_SPLIT_DELIM_CAPTURE
        );

        if ($parts === false) {
            return $html;
        }

        $translated = '';

        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }

            if (str_starts_with($part, '<')) {
                $translated .= self::translateTagAttributes($part, $map);

                continue;
            }

            $translated .= self::translateTextNode($part, $map);
        }

        return $translated;
    }

    private static function translateTagAttributes(string $tag, array $map): string
    {
        return (string) preg_replace_callback(
            '/\b(placeholder|aria-label|title|content)="([^"]*)"/u',
            fn (array $match) => $match[1].'="'.e(self::translateExact($match[2], $map)).'"',
            $tag
        );
    }

    private static function translateTextNode(string $text, array $map): string
    {
        if (trim($text) === '') {
            return $text;
        }

        preg_match('/^\s*/u', $text, $leading);
        preg_match('/\s*$/u', $text, $trailing);

        $core = trim($text);
        $translation = self::translateExact($core, $map);

        return ($leading[0] ?? '').$translation.($trailing[0] ?? '');
    }

    private static function translateExact(string $text, array $map): string
    {
        $normalized = self::normalizeText($text);

        if (isset($map[$text])) {
            return $map[$text];
        }

        if (isset($map[$normalized])) {
            return $map[$normalized];
        }

        $patternTranslation = self::translateDynamicPattern($normalized);

        if ($patternTranslation !== null) {
            return $patternTranslation;
        }

        $sentences = preg_split('/(?<=[.؟!])\s+/u', $normalized);

        if (is_array($sentences) && count($sentences) > 1) {
            $translated = [];

            foreach ($sentences as $sentence) {
                if (! isset($map[$sentence])) {
                    return $text;
                }

                $translated[] = $map[$sentence];
            }

            return implode(' ', $translated);
        }

        return $text;
    }

    private static function translateDynamicPattern(string $text): ?string
    {
        $patterns = [
            '/^([0-9۰-۹,\s.]+)\s*افغانی باقی‌مانده$/u' => '$1 AFN balance',
            '/^([0-9۰-۹,\s.]+)\s*افغانی فیس$/u' => '$1 AFN fees',
            '/^([0-9۰-۹,\s.]+)\s*افغانی$/u' => '$1 AFN',
            '/^([0-9۰-۹,\s.]+)\s*نفر$/u' => '$1 people',
            '/^([0-9۰-۹,\s.]+)\s*کتاب$/u' => '$1 books',
            '/^([0-9۰-۹,\s.]+)\s*نسخه$/u' => '$1 copies',
            '/^([0-9۰-۹,\s.]+)\s*مورد$/u' => '$1 items',
            '/^([0-9۰-۹,\s.]+)\s*ثبت اخیر$/u' => '$1 recent records',
        ];

        foreach ($patterns as $pattern => $replacement) {
            if (preg_match($pattern, $text)) {
                return self::toEnglishDigits((string) preg_replace($pattern, $replacement, $text));
            }
        }

        return null;
    }

    private static function toEnglishDigits(string $text): string
    {
        return strtr($text, [
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',
        ]);
    }

    public static function toPersianDigits(string $text): string
    {
        return strtr($text, [
            '0' => '۰',
            '1' => '۱',
            '2' => '۲',
            '3' => '۳',
            '4' => '۴',
            '5' => '۵',
            '6' => '۶',
            '7' => '۷',
            '8' => '۸',
            '9' => '۹',
            ',' => '٬',
            '.' => '٫',
            '%' => '٪',
        ]);
    }

    /**
     * @return array{0:int,1:int,2:int}
     */
    private static function gregorianToJalali(int $gy, int $gm, int $gd): array
    {
        $gDaysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        $jDaysInMonth = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
        $gy -= 1600;
        $gm -= 1;
        $gd -= 1;

        $gDayNo = 365 * $gy + intdiv($gy + 3, 4) - intdiv($gy + 99, 100) + intdiv($gy + 399, 400);

        for ($i = 0; $i < $gm; $i++) {
            $gDayNo += $gDaysInMonth[$i];
        }

        if ($gm > 1 && (($gy + 1600) % 4 === 0 && (($gy + 1600) % 100 !== 0 || ($gy + 1600) % 400 === 0))) {
            $gDayNo++;
        }

        $gDayNo += $gd;
        $jDayNo = $gDayNo - 79;
        $jNp = intdiv($jDayNo, 12053);
        $jDayNo %= 12053;
        $jy = 979 + 33 * $jNp + 4 * intdiv($jDayNo, 1461);
        $jDayNo %= 1461;

        if ($jDayNo >= 366) {
            $jy += intdiv($jDayNo - 1, 365);
            $jDayNo = ($jDayNo - 1) % 365;
        }

        for ($i = 0; $i < 11 && $jDayNo >= $jDaysInMonth[$i]; $i++) {
            $jDayNo -= $jDaysInMonth[$i];
        }

        return [$jy, $i + 1, $jDayNo + 1];
    }

    private static function normalizeText(string $text): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }

    public static function translations(): array
    {
        return [
            '،' => ',',
            '؛' => ';',
            '؟' => '?',
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',
            'ف' => 'F',
            'م' => 'S',
            'فانوس' => 'Fanous',
            'سیستم مدیریت لیلیه و کتاب‌خانه فانوس برای مدیریت محصلین، اتاق‌ها، مالی و کتاب‌خانه.' => 'Fanous dormitory and library management system for students, rooms, finance, and library operations.',
            'سیستم فانوس' => 'Fanous System',
            'سیستم مدیریت لیلیه' => 'Dormitory Management System',
            'مدیریت لیلیه و کتاب‌خانه' => 'Dormitory and Library Management',
            'سیستم تمرینی مدیریت لیلیه و کتاب‌خانه فانوس' => 'Fanous dormitory and library management system',
            'خانه' => 'Home',
            'شفافیت' => 'Transparency',
            'داشبورد' => 'Dashboard',
            'داشبورد کاری' => 'Work Dashboard',
            'داشبورد مدیریت' => 'Admin Dashboard',
            'مدیریت' => 'Management',
            'تنظیمات' => 'Settings',
            'خروج' => 'Log Out',
            'ورود' => 'Sign In',
            'ورود کارکنان' => 'Staff Sign In',
            'برگشت به خانه' => 'Back Home',
            'بازگشت' => 'Back',
            'لغو' => 'Cancel',
            'ذخیره' => 'Save',
            'ویرایش' => 'Edit',
            'حذف' => 'Delete',
            'ثبت' => 'Create',
            'ثبت نشده' => 'Not recorded',
            'نامشخص' => 'Unknown',
            'ندارد' => 'None',
            'همه' => 'All',
            'نمایش' => 'Show',
            'جستجو' => 'Search',
            'پاک کردن' => 'Clear',
            'عملیات' => 'Actions',
            'وضعیت' => 'Status',
            'فعال' => 'Active',
            'در انتظار تایید' => 'Pending approval',
            'مسدود' => 'Suspended',
            'تعلیق' => 'Suspended',
            'خارج شده' => 'Left',
            'فارغ شده' => 'Graduated',
            'در تعمیر' => 'Under maintenance',
            'بسته' => 'Closed',
            'نام' => 'Name',
            'نام کامل' => 'Full Name',
            'نام پدر' => 'Father Name',
            'ایمیل' => 'Email',
            'شماره تماس' => 'Phone Number',
            'تماس' => 'Contact',
            'واتساپ' => 'WhatsApp',
            'رمز عبور' => 'Password',
            'رمز عبور جدید' => 'New Password',
            'تکرار رمز عبور' => 'Confirm Password',
            'نقش' => 'Role',
            'نقش فعال' => 'Active Role',
            'نوع دسترسی' => 'Access Type',
            'حالت سیستم' => 'System State',
            'دسترسی' => 'Access',
            'باز کردن' => 'Open',
            'به‌زودی' => 'Coming Soon',
            'سلام' => 'Hello',
            'بخش قابل دسترس برای حساب شما' => 'available sections for your account',
            'دسترسی بر اساس رول و صلاحیت' => 'Role and permission based access',
            'آماده برای ثبت و گزارش‌گیری' => 'Ready for records and reporting',
            'اطلاعات مالی از ثبت‌های واقعی خوانده می‌شود' => 'Financial data is read from real records',
            'کاربران کاری' => 'Staff Users',
            'کاربران فعال' => 'Active Users',
            'کاربران تازه' => 'Recent Users',
            'کاربران و نقش‌ها' => 'Users and Roles',
            'مدیریت کاربران و نقش‌ها' => 'Manage Users and Roles',
            'ساخت کاربر جدید' => 'Create New User',
            'ویرایش کاربر' => 'Edit User',
            'ساخت کاربر' => 'Create User',
            'ساخت کاربر جدید' => 'Create New User',
            'وضعیت دسترسی' => 'Access Status',
            'حساب فعلی' => 'Current Account',
            'تاریخ ساخت' => 'Created At',
            'هنوز هیچ کاربری ثبت نشده است.' => 'No users have been registered yet.',
            'هنوز کاربری ثبت نشده است.' => 'No users have been registered yet.',
            'بخش‌های فعال' => 'Active Modules',
            'دسترسی سریع مدیریت' => 'Management Quick Access',
            'بخش‌های اصلی سیستم برای مدیریت روزانه.' => 'Core system sections for daily management.',
            'کارهای پیشنهادی' => 'Suggested Tasks',
            'کارهایی که معمولاً مدیر روزانه انجام می‌دهد' => 'Tasks managers usually handle daily',
            'ثبت محصل جدید' => 'Register New Student',
            'ساخت اتاق جدید' => 'Create New Room',
            'تنظیمات حساب' => 'Account Settings',
            'وضعیت سیستم' => 'System Status',
            'مدیریت مستقیم' => 'Direct Management',
            'بخش‌های اصلی' => 'Core Modules',
            'در دسترس مدیر' => 'Available to the manager',
            'صاحب اصلی لیلیه' => 'Dormitory Owner',
            'مدیر لیلیه' => 'Dormitory Manager',
            'ادمین سیستم' => 'System Admin',
            'گارد' => 'Guard',
            'نماینده محصلین' => 'Student Representative',
            'خرج‌آور' => 'Purchaser',
            'کتاب‌دار' => 'Librarian',
            'آشپز' => 'Cook',
            'محصل لیلیه' => 'Dorm Student',
            'عضو کتاب‌خانه' => 'Library Member',
            'متقاضی عضویت' => 'Applicant',
            'اتاق‌ها' => 'Rooms',
            'اتاق‌ها و ظرفیت' => 'Rooms and Capacity',
            'اتاق‌ها و ظرفیت لیلیه' => 'Dorm Rooms and Capacity',
            'محصلین' => 'Students',
            'محصل' => 'Student',
            'مشخصات محصلین' => 'Student Profiles',
            'ثبت و مدیریت محصلین' => 'Register and Manage Students',
            'دفتر محصلین لیلیه' => 'Dorm Students Office',
            'دفتر دیجیتالی محصلین' => 'Digital Student Office',
            'لیست محصلین' => 'Student List',
            'محصلین فعال' => 'Active Students',
            'کل پرونده‌ها' => 'All Records',
            'پرونده' => 'Profile',
            'پرونده محصل' => 'Student Profile',
            'اسناد' => 'Documents',
            'اسناد ثبت‌شده' => 'Registered Documents',
            'فایل' => 'File',
            'اتاق و تخت' => 'Room and Bed',
            'اتاق' => 'Room',
            'تخت' => 'Bed',
            'تحصیل' => 'Education',
            'ضامن' => 'Guarantor',
            'تذکره' => 'ID Number',
            'همه وضعیت‌ها' => 'All Statuses',
            'مدیریت اتاق‌ها' => 'Manage Rooms',
            'افراد فعلاً داخل لیلیه' => 'People currently in the dormitory',
            'مطابق فیلتر فعلی' => 'Matching the current filter',
            'تغییر مستقیم فقط برای مدیریت' => 'Direct changes are management only',
            'رشته / صنف ثبت نشده' => 'Department / grade not recorded',
            'اعتبار کارت' => 'Card Validity',
            'پرداخت شده' => 'Paid',
            'پرداخت نشده' => 'Unpaid',
            'کتاب‌خانه' => 'Library',
            'مدیریت کتاب‌خانه' => 'Manage Library',
            'گزارش کتاب‌خانه' => 'Library Report',
            'عضو کتاب‌خانه' => 'Library Member',
            'پرونده عضو کتاب‌خانه' => 'Library Member Profile',
            'کتاب' => 'Book',
            'کتاب‌ها' => 'Books',
            'عنوان کتاب' => 'Book Titles',
            'نسخه موجود' => 'Available Copies',
            'امانت' => 'Loan',
            'امانت فعال' => 'Active Loans',
            'امانت داده شده' => 'Loaned',
            'برگشت شده' => 'Returned',
            'گم شده' => 'Lost',
            'ناوقت' => 'Overdue',
            'جریمه' => 'Fine',
            'نماینده' => 'Representative',
            'گزارش نماینده محصلین' => 'Student Representative Report',
            'ثبت پول و جریمه' => 'Record Fees and Fines',
            'حساب نماینده محصلین' => 'Student Representative Account',
            'خرج‌آور' => 'Purchaser',
            'حساب خرج‌آور' => 'Purchaser Account',
            'داشبورد خرج‌آور' => 'Purchaser Dashboard',
            'گزارش خرج‌آور' => 'Purchaser Report',
            'گزارش مالی' => 'Financial Report',
            'شفافیت حساب خرج‌آور' => 'Purchaser Account Transparency',
            'ثبت پول و مصرف' => 'Record Money and Expenses',
            'پول‌ها و مصارف خرج‌آور' => 'Purchaser Payments and Expenses',
            'حساب غذا' => 'Food Account',
            'جمع دریافت‌ها' => 'Total Collections',
            'جمع دریافت' => 'Total Collection',
            'دریافت‌ها' => 'Collections',
            'دریافت' => 'Collection',
            'جمع مصرف' => 'Total Expense',
            'مصارف' => 'Expenses',
            'مصرف' => 'Expense',
            'باقی‌مانده' => 'Balance',
            'مبلغ' => 'Amount',
            'افغانی' => 'AFN',
            'افغانی باقی‌مانده' => 'AFN balance',
            'دوره' => 'Period',
            'از تاریخ' => 'From Date',
            'تا تاریخ' => 'To Date',
            'تاریخ' => 'Date',
            'تاریخ ثبت' => 'Record Date',
            'تاریخ دریافت' => 'Collection Date',
            'تاریخ مصرف' => 'Expense Date',
            'نوع ثبت' => 'Record Type',
            'نوع دریافت' => 'Collection Type',
            'نوع حساب' => 'Account Type',
            'ثبت دریافت' => 'Save Collection',
            'ثبت مصرف' => 'Save Expense',
            'مصرف و خرید' => 'Expense and Purchase',
            'شرح خرید' => 'Purchase Details',
            'شرح' => 'Description',
            'یادداشت' => 'Note',
            'فروشنده' => 'Vendor',
            'منبع/رسید' => 'Source/Receipt',
            'شماره رسید' => 'Receipt Number',
            'چاپ رسید' => 'Print Receipt',
            'چاپ گزارش' => 'Print Report',
            'چاپ کارت' => 'Print Card',
            'کاربر جدید ساخته شد و نقش او تنظیم گردید.' => 'The new user was created and their role was assigned.',
            'معلومات کاربر به‌روزرسانی شد.' => 'User information was updated.',
            'شما نمی‌توانید نقش یا وضعیت حساب خودتان را از این بخش تغییر دهید.' => 'You cannot change your own account role or status from this section.',
            'اتاق جدید ساخته شد.' => 'The new room was created.',
            'محصل به اتاق اضافه شد.' => 'The student was added to the room.',
            'محصل به اتاق جدید انتقال شد.' => 'The student was moved to the new room.',
            'محصل از اتاق خارج شد.' => 'The student was removed from the room.',
            'اتاق به‌روزرسانی شد.' => 'The room was updated.',
            'اتاق انتخاب‌شده فعلا فعال نیست.' => 'The selected room is not active right now.',
            'ظرفیت این اتاق تکمیل است.' => 'This room is already full.',
            'این تخت در اتاق انتخاب‌شده قبلا گرفته شده است.' => 'This bed is already taken in the selected room.',
            'این تخت در اتاق انتخاب‌شده قبلاً گرفته شده است.' => 'This bed is already taken in the selected room.',
            'محصل لیلیه با موفقیت ثبت شد.' => 'The dorm student was registered successfully.',
            'مشخصات محصل به‌روزرسانی شد.' => 'Student details were updated.',
            'عضو کتاب‌خانه ثبت شد.' => 'The library member was registered.',
            'مشخصات عضو کتاب‌خانه ویرایش شد.' => 'Library member details were updated.',
            'کتاب جدید ثبت شد.' => 'The new book was registered.',
            'کتاب ویرایش شد.' => 'The book was updated.',
            'این کتاب فعلاً نسخه قابل امانت ندارد.' => 'This book currently has no copy available for loan.',
            'امانت کتاب ثبت شد.' => 'The book loan was registered.',
            'امانت کتاب ویرایش شد.' => 'The book loan was updated.',
            'برگشت کتاب ثبت شد.' => 'The book return was recorded.',
            'ثبت خرج‌آور ذخیره شد. رسید آماده چاپ است.' => 'The purchaser record was saved. The receipt is ready to print.',
            'معلومات حساب به‌روزرسانی شد.' => 'Account information was updated.',
            'رمز عبور تغییر کرد.' => 'The password was changed.',
            'ثبت مالی نماینده ذخیره شد. رسید آماده چاپ است.' => 'The representative financial record was saved. The receipt is ready to print.',
            'گزارش همین فیلتر' => 'Report for This Filter',
            'فیلتر گزارش' => 'Report Filter',
            'جستجو و فیلتر حساب‌ها' => 'Search and Filter Accounts',
            'پرداخت هر محصل' => 'Payment per Student',
            'آخرین ثبت‌ها' => 'Latest Records',
            'جزئیات' => 'Details',
            'جزئیات ثبت‌ها' => 'Record Details',
            'ثبت‌کننده' => 'Recorded By',
            'مصرف عمومی' => 'General Expense',
            'بدون دوره' => 'No Period',
            'بدون شرح' => 'No Description',
            'بدون یادداشت' => 'No Note',
            'بدون فروشنده/منبع' => 'No Vendor/Source',
            'پول ماهانه' => 'Monthly Fee',
            'پول برق' => 'Electricity Fee',
            'پول آب' => 'Water Fee',
            'پول هفته‌وار غذا' => 'Weekly Food Fee',
            'جمع‌آوری پول غذا' => 'Food Fee Collection',
            'مصرف نماینده' => 'Representative Expense',
            'گزارش شفافیت و اعتماد' => 'Transparency and Trust Report',
            'گزارش شفافیت فانوس' => 'Fanous Transparency Report',
            'گزارش واحد لیلیه و کتاب‌خانه' => 'Unified Dormitory and Library Report',
            'این صفحه برای نمایش شفافیت سیستم ساخته شده است: پول‌های جمع‌آوری‌شده، مصارف، باقی‌مانده، وضعیت کتاب‌خانه و خلاصه ماهانه در یک گزارش قابل چاپ دیده می‌شود.' => 'This page is built for system transparency: collected money, expenses, balances, library status, and monthly summaries are visible in one printable report.',
            'اطلاعات شخصی محصلین عمومی نمی‌شود؛ فقط خلاصه حساب‌دهی و جریان کار نمایش داده می‌شود.' => 'Student personal information is not public; only accountability summaries and workflow status are shown.',
            'مثلاً ماه حمل یا هفته اول' => 'For example, Hamal month or the first week',
            'کل درآمد' => 'Total Income',
            'کل مصرف' => 'Total Expense',
            'تفاوت درآمد و مصرف' => 'Income minus expenses',
            'افغانی از غذا، نماینده و کتاب‌خانه' => 'AFN from food, representative, and library',
            'افغانی مصرف ثبت‌شده' => 'AFN recorded expenses',
            'کتاب‌هایی که نزد اعضا است' => 'Books currently with members',
            'حساب غذا و خرج‌آور' => 'Food and Purchaser Account',
            'دریافت‌ها و مصرف‌هایی که توسط خرج‌آور ثبت شده است.' => 'Collections and expenses recorded by the purchaser.',
            'ماهانه، برق، آب، جریمه و مصارف عمومی نماینده.' => 'Monthly fees, electricity, water, fines, and representative general expenses.',
            'شاخص‌های مهم کتاب‌خانه در کنار حساب لیلیه نمایش داده می‌شود.' => 'Important library indicators are shown beside the dormitory account.',
            'افغانی فیس' => 'AFN Fees',
            'امانت ناوقت' => 'Overdue Loans',
            'اعضای فعال' => 'Active Members',
            'نفر' => 'People',
            'نسخه' => 'Copies',
            'امانت برگشت‌شده' => 'Returned Loans',
            'مورد' => 'Items',
            'خلاصه ماهانه' => 'Monthly Summary',
            'برای جلسات مدیریت، هر ماه با درآمد، مصرف و باقی‌مانده دیده می‌شود.' => 'For management meetings, each month is shown with income, expense, and balance.',
            'برای این بازه هنوز خلاصه ماهانه وجود ندارد.' => 'There is no monthly summary for this range yet.',
            'دفترچه شفافیت' => 'Transparency Ledger',
            'آخرین ثبت‌های مالی بدون نمایش معلومات حساس محصلین.' => 'Latest financial records without exposing sensitive student information.',
            'ثبت اخیر' => 'Recent Record',
            'بخش' => 'Section',
            'نوع' => 'Type',
            'مرتبط با' => 'Related To',
            'درآمد' => 'Income',
            'هنوز ثبت مالی برای نمایش وجود ندارد.' => 'There are no financial records to show yet.',
            'روزانه' => 'Daily',
            'هفته‌وار' => 'Weekly',
            'ماه‌وار' => 'Monthly',
            'راه‌اندازی اولیه سیستم' => 'Initial System Setup',
            'ساخت حساب صاحب سیستم' => 'Create Owner Account',
            'معلومات صاحب سیستم' => 'Owner Information',
            'این حساب به پنل مدیریت کامل دسترسی خواهد داشت.' => 'This account will have full admin panel access.',
            'تماس با فانوس' => 'Contact Fanous',
            'راه‌های ارتباطی' => 'Contact Channels',
            'دفتر مدیریت' => 'Management Office',
            'لیلیه' => 'Dormitory',
            'موضوع' => 'Subject',
            'روش ثبت' => 'Registration Method',
            'صفحه ورود' => 'Login Page',
        ];
    }

    private static function reverseTranslations(): array
    {
        $reverse = [];

        foreach (self::translations() as $persian => $english) {
            if (mb_strlen($persian) <= 1 || mb_strlen($english) <= 1) {
                continue;
            }

            $reverse[$english] = $persian;
        }

        return $reverse;
    }
}
