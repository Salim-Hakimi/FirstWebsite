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
        default: '/api/representative/collections',
    },
    title: {
        type: String,
        default: 'جستجوی سریع ثبت‌های نماینده',
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
    { key: 'student', label: 'شاگرد / منبع' },
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
    <section class="fanous-vue-representative">
        <header class="fanous-vue-summary__header">
            <div>
                <h2 class="fanous-vue-title">{{ title }}</h2>
                <p v-if="meta.total !== undefined">
                    نمایش {{ meta.from || 0 }} تا {{ meta.to || 0 }} از {{ meta.total || 0 }} ثبت
                </p>
            </div>
            <div class="fanous-vue-inline-actions">
                <button class="fanous-vue-refresh is-muted" type="button" :disabled="loading" @click="clearFilters">
                    پاک کردن
                </button>
                <button class="fanous-vue-refresh" type="button" :disabled="loading" @click="execute">
                    تازه‌سازی
                </button>
            </div>
        </header>

        <div v-if="data?.summary" class="fanous-vue-room-summary">
            <article>
                <span>درآمد</span>
                <strong>{{ money(data.summary.income) }}</strong>
            </article>
            <article>
                <span>مصرف</span>
                <strong>{{ money(data.summary.expense) }}</strong>
            </article>
            <article>
                <span>باقی‌مانده</span>
                <strong>{{ money(data.summary.balance) }}</strong>
            </article>
        </div>

        <div class="fanous-vue-representative-filters">
            <SearchInput v-model="filters.q" placeholder="جستجوی شاگرد، تماس، اتاق، تذکره یا یادداشت" />

            <select v-model="filters.type" class="fanous-vue-input">
                <option value="">همه نوع‌ها</option>
                <option v-for="(label, value) in filterMeta.types || {}" :key="value" :value="value">
                    {{ label }}
                </option>
            </select>

            <input v-model="filters.date_from" class="fanous-vue-input" type="date" aria-label="از تاریخ">
            <input v-model="filters.date_to" class="fanous-vue-input" type="date" aria-label="تا تاریخ">
            <input v-model="filters.period" class="fanous-vue-input" placeholder="دوره / ماه">
        </div>

        <ErrorState v-if="error" :message="error.message" />

        <DataTable v-else :columns="columns" :loading="loading" :rows="rows">
            <template #cell-date="{ row }">
                {{ dateLabel(row.collected_at) }}
            </template>

            <template #cell-student="{ row }">
                <div class="fanous-vue-finance-party">
                    <strong>{{ row.student?.full_name || 'مصرف عمومی نماینده' }}</strong>
                    <small>{{ row.notes || 'بدون یادداشت' }}</small>
                    <small v-if="row.student?.room_number">اتاق {{ row.student.room_number }}</small>
                </div>
            </template>

            <template #cell-type="{ row }">
                <span class="fanous-vue-badge" :class="row.is_expense ? 'is-expense' : 'is-income'">
                    {{ row.type_label }}
                </span>
            </template>

            <template #cell-period="{ row }">
                {{ row.period || 'ندارد' }}
            </template>

            <template #cell-amount="{ row }">
                <strong class="fanous-vue-amount" :class="row.is_expense ? 'is-expense' : 'is-income'">
                    {{ money(row.amount) }}
                </strong>
            </template>

            <template #cell-actions="{ row }">
                <div class="fanous-vue-table-actions">
                    <a :href="row.links.receipt">رسید</a>
                    <a v-if="row.links.student" :href="row.links.student">پروفایل</a>
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
