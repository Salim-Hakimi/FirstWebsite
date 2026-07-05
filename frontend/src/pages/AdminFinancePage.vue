<script setup>
import AdminFinanceTransactionsTable from '../components/finance/AdminFinanceTransactionsTable.vue';
import ModulePage from '../components/layout/ModulePage.vue';

const actions = [
    { label: 'ثبت کمک / درآمد', href: '/admin/finance', primary: true },
    { label: 'گزارش مالی', href: '/admin/finance/report' },
];
</script>

<template>
    <ModulePage
        eyebrow="مالی"
        title="مالی لیلیه"
        description="جستجو و بررسی ثبت‌های مالی در Vue؛ ثبت و ویرایش فعلاً در مسیر Blade انجام می‌شود."
        :actions="actions"
    >
        <AdminFinanceTransactionsTable title="ثبت‌های مالی" />
    </ModulePage>
</template>
