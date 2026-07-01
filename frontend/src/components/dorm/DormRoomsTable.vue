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
        default: '/api/dorm/rooms',
    },
    title: {
        type: String,
        default: 'نمای سریع اتاق‌ها',
    },
});

const filters = reactive({
    q: '',
    status: '',
    floor: '',
});

const columns = [
    { key: 'room', label: 'اتاق' },
    { key: 'capacity', label: 'ظرفیت' },
    { key: 'occupied', label: 'اشغال' },
    { key: 'free', label: 'خالی' },
    { key: 'usage', label: 'استفاده' },
    { key: 'actions', label: 'عملیات' },
];

const { data, error, execute, loading } = useAsyncState(async () => {
    const response = await api.get(props.endpoint, { params: filters });
    return response.data;
});

const rows = computed(() => data.value?.data || []);
const summary = computed(() => data.value?.summary || {});
const filterMeta = computed(() => data.value?.filters || {});

function number(value) {
    return new Intl.NumberFormat('fa-AF').format(Number(value || 0));
}

watch(
    () => [filters.q, filters.status, filters.floor],
    () => execute(),
);

onMounted(() => execute());
</script>

<template>
    <section class="fanous-vue-library-members">
        <header class="fanous-vue-summary__header">
            <div>
                <h2 class="fanous-vue-title">{{ title }}</h2>
                <p>
                    {{ number(summary.free_beds) }} تخت خالی از {{ number(summary.total_capacity) }} ظرفیت
                    · {{ number(summary.occupancy_rate) }}٪ استفاده
                </p>
            </div>
            <button class="fanous-vue-refresh" type="button" :disabled="loading" @click="execute">
                تازه‌سازی
            </button>
        </header>

        <div class="fanous-vue-room-summary">
            <article>
                <span>اتاق‌ها</span>
                <strong>{{ number(summary.total_rooms) }}</strong>
            </article>
            <article>
                <span>فعال</span>
                <strong>{{ number(summary.active_rooms) }}</strong>
            </article>
            <article>
                <span>در تعمیر</span>
                <strong>{{ number(summary.maintenance_rooms) }}</strong>
            </article>
            <article>
                <span>بسته</span>
                <strong>{{ number(summary.closed_rooms) }}</strong>
            </article>
        </div>

        <div class="fanous-vue-student-filters">
            <SearchInput v-model="filters.q" placeholder="جستجوی شماره اتاق یا منزل" />
            <select v-model="filters.status" class="fanous-vue-input">
                <option value="">همه وضعیت‌ها</option>
                <option v-for="(label, value) in filterMeta.statuses" :key="value" :value="value">
                    {{ label }}
                </option>
            </select>
            <select v-model="filters.floor" class="fanous-vue-input">
                <option value="">همه منزل‌ها</option>
                <option v-for="floor in filterMeta.floors" :key="floor" :value="floor">
                    {{ floor }}
                </option>
            </select>
        </div>

        <ErrorState v-if="error" :message="error.message" />

        <DataTable v-else :columns="columns" :loading="loading" :rows="rows">
            <template #cell-room="{ row }">
                <div>
                    <strong>اتاق {{ row.room_number }}</strong>
                    <small>منزل: {{ row.floor || 'ثبت نشده' }} · {{ row.status_label }}</small>
                </div>
            </template>

            <template #cell-capacity="{ row }">
                {{ number(row.capacity) }}
            </template>

            <template #cell-occupied="{ row }">
                {{ number(row.occupied_beds) }}
            </template>

            <template #cell-free="{ row }">
                {{ number(row.free_beds) }}
            </template>

            <template #cell-usage="{ row }">
                <div class="fanous-vue-usage">
                    <span :style="{ width: `${row.usage_percent}%` }" />
                </div>
                <small>{{ number(row.usage_percent) }}٪</small>
            </template>

            <template #cell-actions="{ row }">
                <div class="fanous-vue-table-actions">
                    <a :href="row.links.show">مدیریت</a>
                    <a :href="row.links.edit">ویرایش</a>
                </div>
            </template>
        </DataTable>
    </section>
</template>
