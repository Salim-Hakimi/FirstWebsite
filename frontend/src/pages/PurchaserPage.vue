<script setup>
import ModulePage from '../components/layout/ModulePage.vue';
import PurchaserRecordsTable from '../components/purchaser/PurchaserRecordsTable.vue';

const actions = [
    { label: 'ثبت خرید / مصرف', href: '/purchaser', primary: true },
    { label: 'گزارش', href: '/purchaser/report' },
];
</script>

<template>
    <ModulePage
        eyebrow="خریداری"
        title="ثبت‌های خریداری"
        description="لیست ثبت‌های خرج‌آور در Vue؛ فورم ثبت فعلاً از صفحه قبلی باز می‌شود."
        :actions="actions"
    >
        <PurchaserRecordsTable title="ثبت‌های خریداری" />
    </ModulePage>
</template>
