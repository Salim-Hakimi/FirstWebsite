<?php

namespace App\Support;

use App\Models\User;

class FrontendNavigation
{
    /**
     * @return array<int, array{label: string, href: string, active: string, icon: string}>
     */
    public static function forUser(User $user): array
    {
        $items = [
            [
                'label' => 'داشبورد',
                'href' => route('vue.dashboard'),
                'active' => '/app',
                'icon' => 'د',
                'visible' => true,
            ],
            [
                'label' => 'کاربران و نقش‌ها',
                'href' => url('/app/admin/users'),
                'active' => '/app/admin/users',
                'icon' => 'ک',
                'visible' => $user->canManageUsers(),
            ],
            [
                'label' => 'مالی لیلیه',
                'href' => url('/app/admin/finance'),
                'active' => '/app/admin/finance',
                'icon' => 'م',
                'visible' => $user->canAccessAdmin(),
            ],
            [
                'label' => 'اتاق‌ها',
                'href' => url('/app/dorm/rooms'),
                'active' => '/app/dorm/rooms',
                'icon' => 'ا',
                'visible' => $user->canAccessAdmin(),
            ],
            [
                'label' => 'محصلین',
                'href' => url('/app/dorm/students'),
                'active' => '/app/dorm/students',
                'icon' => 'ش',
                'visible' => in_array($user->role, User::dormRecordViewerRoles(), true),
            ],
            [
                'label' => 'نماینده',
                'href' => url('/app/representative'),
                'active' => '/app/representative',
                'icon' => 'ن',
                'visible' => in_array($user->role, User::studentRepresentativeRoles(), true),
            ],
            [
                'label' => 'خریداری',
                'href' => url('/app/purchaser'),
                'active' => '/app/purchaser',
                'icon' => 'خ',
                'visible' => in_array($user->role, User::purchaserRoles(), true),
            ],
            [
                'label' => 'کتابخانه',
                'href' => url('/app/library'),
                'active' => '/app/library',
                'icon' => 'ک',
                'visible' => in_array($user->role, User::libraryViewerRoles(), true),
            ],
            [
                'label' => 'تنظیمات حساب',
                'href' => route('settings.edit'),
                'active' => '/settings',
                'icon' => 'ت',
                'visible' => true,
            ],
        ];

        return array_values(array_map(
            fn (array $item) => array_intersect_key($item, array_flip(['label', 'href', 'active', 'icon'])),
            array_filter($items, fn (array $item) => $item['visible'])
        ));
    }
}
