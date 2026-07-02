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
        default: '/api/library/loans',
    },
    title: {
        type: String,
        default: 'جستجوی سریع امانت‌ها',
    },
});

const filters = reactive({
    q: '',
    status: '',
    date_from: '',
    date_to: '',
    page: 1,
    per_page: 10,
});

const columns = [
    { key: 'member', label: 'عضو' },
    { key: 'book', label: 'کتاب / نسخه' },
    { key: 'dates', label: 'تاریخ‌ها' },
    { key: 'status', label: 'وضعیت' },
    { key: 'fine', label: 'جریمه' },
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
        return 'ندارد';
    }

    return new Intl.DateTimeFormat('fa-AF', { dateStyle: 'short' }).format(new Date(value));
}

function goToPage(page) {
    filters.page = page;
    execute();
}

function clearFilters() {
    filters.q = '';
    filters.status = '';
    filters.date_from = '';
    filters.date_to = '';
    filters.page = 1;
    execute();
}

watch(
    () => [filters.q, filters.status, filters.date_from, filters.date_to],
    () => {
        filters.page = 1;
        execute();
    },
);

onMounted(() => execute());
</script>

<template>
    <section class="fanous-vue-library-loans">
        <header class="fanous-vue-summary__header">
            <div>
                <h2 class="fanous-vue-title">{{ title }}</h2>
                <p v-if="meta.total !== undefined">
                    نمایش {{ meta.from || 0 }} تا {{ meta.to || 0 }} از {{ meta.total || 0 }} امانت
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
                <span>فعال</span>
                <strong>{{ data.summary.active }}</strong>
            </article>
            <article>
                <span>دیرشده</span>
                <strong>{{ data.summary.late }}</strong>
            </article>
            <article>
                <span>برگشت‌شده</span>
                <strong>{{ data.summary.returned }}</strong>
            </article>
        </div>

        <div class="fanous-vue-loan-filters">
            <SearchInput v-model="filters.q" placeholder="جستجوی عضو، کتاب، کد نسخه، بارکد یا کد امانت" />

            <select v-model="filters.status" class="fanous-vue-input">
                <option value="">همه وضعیت‌ها</option>
                <option v-for="(label, value) in filterMeta.statuses || {}" :key="value" :value="value">
                    {{ label }}
                </option>
            </select>

            <input v-model="filters.date_from" class="fanous-vue-input" type="date" aria-label="از تاریخ">
            <input v-model="filters.date_to" class="fanous-vue-input" type="date" aria-label="تا تاریخ">
        </div>

        <ErrorState v-if="error" :message="error.message" />

        <DataTable v-else :columns="columns" :loading="loading" :rows="rows">
            <template #cell-member="{ row }">
                <div class="fanous-vue-finance-party">
                    <strong>{{ row.member?.full_name || 'عضو نامشخص' }}</strong>
                    <small>{{ row.member?.member_code || 'بدون کد' }}</small>
                    <small v-if="row.member?.phone" class="fanous-vue-ltr">{{ row.member.phone }}</small>
                </div>
            </template>

            <template #cell-book="{ row }">
                <div class="fanous-vue-finance-party">
                    <strong>{{ row.book?.title || 'کتاب نامشخص' }}</strong>
                    <small>{{ row.book?.author || 'نویسنده نامشخص' }}</small>
                    <small class="fanous-vue-ltr">Copy {{ row.copy?.copy_code || 'N/A' }}</small>
                </div>
            </template>

            <template #cell-dates="{ row }">
                <div class="fanous-vue-finance-party">
                    <small>امانت: {{ dateLabel(row.borrowed_at) }}</small>
                    <small>برگشت: {{ dateLabel(row.due_at) }}</small>
                    <small v-if="row.returned_at">تسلیم: {{ dateLabel(row.returned_at) }}</small>
                </div>
            </template>

            <template #cell-status="{ row }">
                <span class="fanous-vue-badge" :class="row.is_late ? 'is-late' : `is-${row.status}`">
                    {{ row.is_late ? 'دیرشده' : row.status_label }}
                </span>
            </template>

            <template #cell-fine="{ row }">
                {{ money(row.fine_amount) }}
            </template>

            <template #cell-actions="{ row }">
                <div class="fanous-vue-table-actions">
                    <a v-if="row.links.member" :href="row.links.member">پروفایل</a>
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
