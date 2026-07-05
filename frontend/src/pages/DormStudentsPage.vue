<script setup>
import DormStudentsTable from '../components/dorm/DormStudentsTable.vue';
import ModulePage from '../components/layout/ModulePage.vue';

const actions = [
    { label: 'ثبت شاگرد جدید', href: '/dorm/students/create', primary: true, modal: true, modalTitle: 'ثبت شاگرد جدید' },
    { label: 'نسخه Blade', href: '/dorm/students' },
];
</script>

<template>
    <ModulePage
        eyebrow="لیلیه"
        title="محصلین لیلیه"
        description="لیست، جستجو، وضعیت کارت و دسترسی سریع به پروفایل محصلین در Vue."
        :actions="actions"
    >
        <DormStudentsTable title="لیست محصلین" />
    </ModulePage>
</template>
