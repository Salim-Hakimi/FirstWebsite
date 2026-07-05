<script setup>
import { ref } from 'vue';
import DormRoomFormModal from '../components/dorm/DormRoomFormModal.vue';
import DormRoomsTable from '../components/dorm/DormRoomsTable.vue';
import ModulePage from '../components/layout/ModulePage.vue';

const actions = [
    { label: 'نسخه Blade', href: '/dorm/rooms' },
];

const tableRef = ref(null);
const modalOpen = ref(false);
const editingRoomId = ref(null);

function openCreate() {
    editingRoomId.value = null;
    modalOpen.value = true;
}

function openEdit(row) {
    editingRoomId.value = row.id;
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
        eyebrow="لیلیه"
        title="اتاق‌ها"
        description="نمای سریع ظرفیت، تخت‌های خالی، اشغال و وضعیت اتاق‌های لیلیه."
        :actions="actions"
    >
        <div class="fanous-vue-inline-actions">
            <button class="fanous-vue-refresh" type="button" @click="openCreate">ثبت اتاق</button>
        </div>

        <DormRoomsTable ref="tableRef" title="مدیریت اتاق‌ها" vue-actions @edit="openEdit" />

        <DormRoomFormModal
            :open="modalOpen"
            :room-id="editingRoomId"
            @close="closeModal"
            @saved="handleSaved"
        />
    </ModulePage>
</template>
