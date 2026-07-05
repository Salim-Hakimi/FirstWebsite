<script setup>
import DashboardSummary from '../components/dashboard/DashboardSummary.vue';

const quickLinks = [
    { label: 'محصلین لیلیه', href: '/app/dorm/students', body: 'لیست، جستجو، پروفایل و کارت محصلین' },
    { label: 'کتابخانه', href: '/app/library', body: 'اعضا، کتاب‌ها، امانت‌ها و مالی کتابخانه' },
    { label: 'مالی لیلیه', href: '/app/admin/finance', body: 'درآمد، مصرف، گزارش و رسیدها' },
    { label: 'اتاق‌ها', href: '/app/dorm/rooms', body: 'ظرفیت، اشغال و مدیریت اتاق‌ها' },
];
</script>

<template>
    <div class="fanous-vue-dashboard">
        <section class="fanous-vue-hero">
            <span>مرحله اول مهاجرت</span>
            <h1>داشبورد Vue فانوس</h1>
            <p>
                این صفحه اولین قدم برای تبدیل تدریجی Blade به Vue است. صفحات قدیمی هنوز فعال‌اند،
                اما ساختار اصلی برای انتقال صفحه‌به‌صفحه آماده شد.
            </p>
        </section>

        <DashboardSummary title="خلاصه زنده سیستم" />

        <section class="fanous-vue-quick-grid">
            <a v-for="link in quickLinks" :key="link.href" :href="link.href">
                <strong>{{ link.label }}</strong>
                <small>{{ link.body }}</small>
            </a>
        </section>
    </div>
</template>
