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
        default: '/api/purchaser/records',
    },
    title: {
        type: String,
        default: 'ثبت‌های خریداری',
    },
});

const filters = reactive({
    q: '',
    type: '',
    date_from: '',
    date_to: '',
    period: '',
    page: 1,
    per_page: 10,
});

const columns = [
    { key: 'date', label: 'تاریخ' },
    { key: 'source', label: 'شاگرد / منبع' },
    { key: 'type', label: 'نوع' },
    { key: 'period', label: 'دوره' },
    { key: 'amount', label: 'مبلغ' },
    { key: 'recorded_by', label: 'ثبت‌کننده' },
    { key: 'actions', label: 'عملیات' },
];

const { data, error, execute, loading } = useAsyncState(async () => {
    const response = await api.get(props.endpoint, { params: filters });

    return response.data;
});

const rows = computed(() => data.value?.data || []);
const meta = computed(() => data.value?.meta || {});
const filterMeta = computed(() => data.value?.filters || {});

function money(value) {
    return new Intl.NumberFormat('fa-AF').format(Number(value || 0)) + ' افغانی';
}

function dateLabel(value) {
    if (! value) {
        return 'ثبت نشده';
    }

    return new Intl.DateTimeFormat('fa-AF', { dateStyle: 'short' }).format(new Date(value));
}

function goToPage(page) {
    filters.page = page;
    execute();
}

function clearFilters() {
    filters.q = '';
    filters.type = '';
    filters.date_from = '';
    filters.date_to = '';
    filters.period = '';
    filters.page = 1;
    execute();
}

watch(
    () => [filters.q, filters.type, filters.date_from, filters.date_to, filters.period],
    () => {
        filters.page = 1;
        execute();
    },
);

onMounted(() => execute());
</script>

<template>
    <section class="fanous-vue-purchaser">
        <header class="fanous-vue-summary__header">
            <div>
                <h2 class="fanous-vue-title">{{ title }}</h2>
                <p v-if="meta.total !== undefined">
                    Showing {{ meta.from || 0 }} to {{ meta.to || 0 }} of {{ meta.total || 0 }} records
                </p>
            </div>
            <div class="fanous-vue-inline-actions">
                <button class="fanous-vue-refresh is-muted" type="button" :disabled="loading" @click="clearFilters">
                    Clear
                </button>
                <button class="fanous-vue-refresh" type="button" :disabled="loading" @click="execute">
                    Refresh
                </button>
            </div>
        </header>

        <div v-if="data?.summary" class="fanous-vue-room-summary">
            <article>
                <span>Collected</span>
                <strong>{{ money(data.summary.income) }}</strong>
            </article>
            <article>
                <span>Expenses</span>
                <strong>{{ money(data.summary.expense) }}</strong>
            </article>
            <article>
                <span>Balance</span>
                <strong>{{ money(data.summary.balance) }}</strong>
            </article>
        </div>

        <div class="fanous-vue-purchaser-filters">
            <SearchInput v-model="filters.q" placeholder="Student, phone, room, vendor, source, or note" />

            <select v-model="filters.type" class="fanous-vue-input">
                <option value="">All types</option>
                <option v-for="(label, value) in filterMeta.types || {}" :key="value" :value="value">
                    {{ label }}
                </option>
            </select>

            <input v-model="filters.date_from" class="fanous-vue-input" type="date" aria-label="از تاریخ">
            <input v-model="filters.date_to" class="fanous-vue-input" type="date" aria-label="تا تاریخ">
            <input v-model="filters.period" class="fanous-vue-input" placeholder="Period">
        </div>

        <ErrorState v-if="error" :message="error.message" />

        <DataTable v-else :columns="columns" :loading="loading" :rows="rows">
            <template #cell-date="{ row }">
                {{ dateLabel(row.recorded_at) }}
            </template>

            <template #cell-source="{ row }">
                <div class="fanous-vue-finance-party">
                    <strong>{{ row.student?.full_name || 'General expense' }}</strong>
                    <small>{{ row.vendor_or_source || row.description || 'No vendor/source' }}</small>
                    <small v-if="row.student?.room_number">Room {{ row.student.room_number }}</small>
                </div>
            </template>

            <template #cell-type="{ row }">
                <span class="fanous-vue-badge" :class="row.is_expense ? 'is-expense' : 'is-income'">
                    {{ row.type_label }}
                </span>
            </template>

            <template #cell-period="{ row }">
                {{ row.period || 'No period' }}
            </template>

            <template #cell-amount="{ row }">
                <strong class="fanous-vue-amount" :class="row.is_expense ? 'is-expense' : 'is-income'">
                    {{ money(row.amount) }}
                </strong>
            </template>

            <template #cell-actions="{ row }">
                <div class="fanous-vue-table-actions">
                    <a :href="row.links.receipt">Receipt</a>
                    <a v-if="row.links.student" :href="row.links.student">Profile</a>
                </div>
            </template>
        </DataTable>

        <footer v-if="meta.last_page > 1" class="fanous-vue-pagination">
            <button type="button" :disabled="loading || meta.current_page <= 1" @click="goToPage(meta.current_page - 1)">
                Previous
            </button>
            <span>{{ meta.current_page }} / {{ meta.last_page }}</span>
            <button type="button" :disabled="loading || meta.current_page >= meta.last_page" @click="goToPage(meta.current_page + 1)">
                Next
            </button>
        </footer>
    </section>
</template>
