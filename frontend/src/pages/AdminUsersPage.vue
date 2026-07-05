<script setup>
import { ref } from 'vue';
import AdminUserFormModal from '../components/admin/AdminUserFormModal.vue';
import AdminUsersTable from '../components/admin/AdminUsersTable.vue';
import ModulePage from '../components/layout/ModulePage.vue';

const actions = [
    { label: 'نسخه Blade', href: '/admin/users' },
];

const tableRef = ref(null);
const modalOpen = ref(false);
const editingUserId = ref(null);

function openCreate() {
    editingUserId.value = null;
    modalOpen.value = true;
}

function openEdit(row) {
    editingUserId.value = row.id;
    modalOpen.value = true;
}

function closeModal() {
    modalOpen.value = false;
}

function handleSaved() {
    tableRef.value?.reload();
}
</script>

<template>
    <ModulePage
        eyebrow="مدیریت"
        title="کاربران و نقش‌ها"
        description="فهرست کاربران، نقش‌ها و وضعیت حساب‌ها در Vue."
        :actions="actions"
    >
        <div class="fanous-vue-inline-actions">
            <button class="fanous-vue-refresh" type="button" @click="openCreate">ساخت کاربر</button>
        </div>

        <AdminUsersTable ref="tableRef" title="حساب‌های کاربری" vue-actions @edit="openEdit" />

        <AdminUserFormModal
            :open="modalOpen"
            :user-id="editingUserId"
            @close="closeModal"
            @saved="handleSaved"
        />
    </ModulePage>
</template>
