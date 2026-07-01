<script setup>
import { computed, onMounted, reactive, watch } from 'vue';
import { api } from '../../services/api';
import { useAsyncState } from '../../composables/useAsyncState';
import DataTable from '../tables/DataTable.vue';
import ErrorState from '../common/ErrorState.vue';
import SearchInput from '../forms/SearchInput.vue';

const props = defineProps({
    endpoint: {
        type: String,
        default: '/api/dorm/students',
    },
    title: {
        type: String,
        default: 'جستجوی سریع شاگردان لیلیه',
    },
});

const filters = reactive({
    q: '',
    status: '',
    room: '',
    date: '',
    page: 1,
    per_page: 8,
});

const columns = [
    { key: 'student', label: 'شاگرد' },
    { key: 'phone', label: 'تماس' },
    { key: 'room', label: 'اتاق' },
    { key: 'documents', label: 'اسناد' },
    { key: 'card', label: 'کارت' },
    { key: 'actions', label: 'عملیات' },
];

const { data, error, execute, loading } = useAsyncState(async () => {
    const response = await api.get(props.endpoint, { params: filters });
    return response.data;
});

const rows = computed(() => data.value?.data || []);
const meta = computed(() => data.value?.meta || {});

function statusLabel(status) {
    return {
        active: 'فعال',
        suspended: 'مسدود',
        graduated: 'فارغ شده',
        left: 'خارج شده',
    }[status] || status || 'نامشخص';
}

function dateLabel(value) {
    if (! value) {
        return 'ندارد';
    }

    return new Intl.DateTimeFormat('fa-AF', { dateStyle: 'short' }).format(new Date(value));
}

function goToPage(page) {
    filters.page = page;
    execute();
}

watch(
    () => [filters.q, filters.status, filters.room, filters.date],
    () => {
        filters.page = 1;
        execute();
    },
);

onMounted(() => execute());
</script>

<template>
    <section class="fanous-vue-library-members">
        <header class="fanous-vue-summary__header">
            <div>
                <h2 class="fanous-vue-title">{{ title }}</h2>
                <p v-if="meta.total !== undefined">
                    نمایش {{ meta.from || 0 }} تا {{ meta.to || 0 }} از {{ meta.total || 0 }} شاگرد
                </p>
            </div>
            <button class="fanous-vue-refresh" type="button" :disabled="loading" @click="execute">
                تازه‌سازی
            </button>
        </header>

        <div class="fanous-vue-student-filters">
            <SearchInput v-model="filters.q" placeholder="جستجوی نام، تماس، تذکره یا اتاق" />
            <select v-model="filters.status" class="fanous-vue-input">
                <option value="">همه وضعیت‌ها</option>
                <option value="active">فعال</option>
                <option value="suspended">مسدود</option>
                <option value="graduated">فارغ شده</option>
                <option value="left">خارج شده</option>
            </select>
            <input v-model="filters.room" class="fanous-vue-input" placeholder="اتاق">
            <input v-model="filters.date" class="fanous-vue-input" type="date">
        </div>

        <ErrorState v-if="error" :message="error.message" />

        <DataTable v-else :columns="columns" :loading="loading" :rows="rows">
            <template #cell-student="{ row }">
                <div class="fanous-vue-member-cell">
                    <img v-if="row.profile_photo_url" :src="row.profile_photo_url" :alt="row.full_name">
                    <span v-else>{{ row.full_name?.slice(0, 1) }}</span>
                    <div>
                        <strong>{{ row.full_name }}</strong>
                        <small>{{ row.student_code }} · {{ statusLabel(row.status) }} · پدر: {{ row.father_name }}</small>
                    </div>
                </div>
            </template>

            <template #cell-room="{ row }">
                {{ row.room_number ? `اتاق ${row.room_number}` : 'تعیین نشده' }}
                <small v-if="row.bed_number"> · تخت {{ row.bed_number }}</small>
            </template>

            <template #cell-documents="{ row }">
                {{ row.document_count }} فایل
            </template>

            <template #cell-card="{ row }">
                {{ dateLabel(row.card_expires_at) }}
            </template>

            <template #cell-actions="{ row }">
                <div class="fanous-vue-table-actions">
                    <a :href="row.links.show">مشاهده</a>
                    <a v-if="row.links.edit" :href="row.links.edit">ویرایش</a>
                </div>
            </template>
        </DataTable>

        <footer v-if="meta.last_page > 1" class="fanous-vue-pagination">
            <button type="button" :disabled="loading || meta.current_page <= 1" @click="goToPage(meta.current_page - 1)">
                قبلی
            </button>
            <span>{{ meta.current_page }} / {{ meta.last_page }}</span>
            <button type="button" :disabled="loading || meta.current_page >= meta.last_page" @click="goToPage(meta.current_page + 1)">
                بعدی
            </button>
        </footer>
    </section>
</template>
