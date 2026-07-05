<script setup>
import { computed, ref } from 'vue';
import LibraryBooksTable from '../components/library/LibraryBooksTable.vue';
import LibraryFeeRemindersTable from '../components/library/LibraryFeeRemindersTable.vue';
import LibraryLoansTable from '../components/library/LibraryLoansTable.vue';
import LibraryMembersTable from '../components/library/LibraryMembersTable.vue';
import ModulePage from '../components/layout/ModulePage.vue';

const activeTab = ref('members');
const actions = [
    { label: 'ثبت عضو / کتاب / امانت', href: '/library', primary: true },
    { label: 'گزارش موجودی', href: '/library/inventory' },
];

const tabs = [
    { key: 'members', label: 'اعضا' },
    { key: 'books', label: 'کتاب‌ها' },
    { key: 'loans', label: 'امانت‌ها' },
    { key: 'fees', label: 'یادآوری فیس' },
];

const activeTitle = computed(() => tabs.find((tab) => tab.key === activeTab.value)?.label || '');
</script>

<template>
    <ModulePage
        eyebrow="کتابخانه"
        title="مدیریت کتابخانه"
        description="بخش‌های اصلی کتابخانه در Vue؛ فورم‌های ثبت فعلاً از نسخه Blade امن استفاده می‌کنند."
        :actions="actions"
    >
        <nav class="fanous-vue-tabs" aria-label="بخش‌های کتابخانه">
            <button
                v-for="tab in tabs"
                :key="tab.key"
                type="button"
                :class="{ 'is-active': activeTab === tab.key }"
                @click="activeTab = tab.key"
            >
                {{ tab.label }}
            </button>
        </nav>

        <LibraryMembersTable v-if="activeTab === 'members'" :title="`کتابخانه - ${activeTitle}`" />
        <LibraryBooksTable v-else-if="activeTab === 'books'" :title="`کتابخانه - ${activeTitle}`" />
        <LibraryLoansTable v-else-if="activeTab === 'loans'" :title="`کتابخانه - ${activeTitle}`" />
        <LibraryFeeRemindersTable v-else :title="`کتابخانه - ${activeTitle}`" />
    </ModulePage>
</template>
