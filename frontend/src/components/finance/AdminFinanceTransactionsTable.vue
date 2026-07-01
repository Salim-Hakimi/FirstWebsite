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
        default: '/api/admin/finance/transactions',
    },
    title: {
        type: String,
        default: 'جستجوی سریع ثبت‌های مالی',
    },
});

const filters = reactive({
    q: '',
    type: '',
    category: '',
    payment_method: '',
    date_from: '',
    date_to: '',
    page: 1,
    per_page: 10,
});

const columns = [
    { key: 'date', label: 'تاریخ' },
    { key: 'counterparty', label: 'شخص / منبع' },
    { key: 'type', label: 'نوع' },
    { key: 'receipt', label: 'رسید' },
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
    filters.category = '';
    filters.payment_method = '';
    filters.date_from = '';
    filters.date_to = '';
    filters.page = 1;
    execute();
}

watch(
    () => [filters.q, filters.type, filters.category, filters.payment_method, filters.date_from, filters.date_to],
    () => {
        filters.page = 1;
        execute();
    },
);

onMounted(() => execute());
</script>

<template>
    <section class="fanous-vue-finance">
        <header class="fanous-vue-summary__header">
            <div>
                <h2 class="fanous-vue-title">{{ title }}</h2>
                <p v-if="meta.total !== undefined">
                    نمایش {{ meta.from || 0 }} تا {{ meta.to || 0 }} از {{ meta.total || 0 }} ثبت مالی
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

        <div class="fanous-vue-finance-filters">
            <SearchInput v-model="filters.q" placeholder="جستجوی نام، رسید، دسته‌بندی، پروژه یا توضیحات" />

            <select v-model="filters.type" class="fanous-vue-input">
                <option value="">همه نوع‌ها</option>
                <option value="income">درآمد</option>
                <option value="expense">مصرف</option>
            </select>

            <select v-model="filters.category" class="fanous-vue-input">
                <option value="">همه دسته‌ها</option>
                <option v-for="category in filterMeta.categories || []" :key="category.id" :value="category.id">
                    {{ category.name }}
                </option>
            </select>

            <select v-model="filters.payment_method" class="fanous-vue-input">
                <option value="">همه روش‌ها</option>
                <option v-for="method in filterMeta.payment_methods || []" :key="method" :value="method">
                    {{ method }}
                </option>
            </select>

            <input v-model="filters.date_from" class="fanous-vue-input" type="date" aria-label="از تاریخ">
            <input v-model="filters.date_to" class="fanous-vue-input" type="date" aria-label="تا تاریخ">
        </div>

        <ErrorState v-if="error" :message="error.message" />

        <DataTable v-else :columns="columns" :loading="loading" :rows="rows">
            <template #cell-date="{ row }">
                {{ dateLabel(row.transaction_date) }}
            </template>

            <template #cell-counterparty="{ row }">
                <div class="fanous-vue-finance-party">
                    <strong>{{ row.counterparty }}</strong>
                    <small>{{ row.category?.name || 'بدون دسته' }}</small>
                </div>
            </template>

            <template #cell-type="{ row }">
                <span class="fanous-vue-badge" :class="row.type === 'income' ? 'is-income' : 'is-expense'">
                    {{ row.type_label }}
                </span>
                <span class="fanous-vue-badge is-muted">{{ row.status_label }}</span>
            </template>

            <template #cell-receipt="{ row }">
                <span class="fanous-vue-ltr">{{ row.receipt_number || 'ندارد' }}</span>
            </template>

            <template #cell-amount="{ row }">
                <strong class="fanous-vue-amount" :class="row.type === 'income' ? 'is-income' : 'is-expense'">
                    {{ money(row.amount) }}
                </strong>
            </template>

            <template #cell-actions="{ row }">
                <div class="fanous-vue-table-actions">
                    <a :href="row.links.edit">ویرایش</a>
                    <a :href="row.links.receipt">رسید</a>
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
