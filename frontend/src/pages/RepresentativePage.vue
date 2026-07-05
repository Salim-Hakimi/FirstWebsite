<script setup>
import ModulePage from '../components/layout/ModulePage.vue';
import RepresentativeCollectionsTable from '../components/representative/RepresentativeCollectionsTable.vue';

const actions = [
    { label: 'ثبت جمع‌آوری', href: '/representative', primary: true },
    { label: 'گزارش', href: '/representative/report' },
];
</script>

<template>
    <ModulePage
        eyebrow="نماینده"
        title="ثبت‌های نماینده"
        description="لیست دریافتی‌ها و گزارش‌های نماینده در Vue."
        :actions="actions"
    >
        <RepresentativeCollectionsTable title="ثبت‌های نماینده" />
    </ModulePage>
</template>
