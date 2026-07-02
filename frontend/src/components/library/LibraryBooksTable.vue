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
        default: '/api/library/books',
    },
    title: {
        type: String,
        default: 'جستجوی سریع کتاب‌ها',
    },
});

const filters = reactive({
    q: '',
    status: '',
    category: '',
    shelf: '',
    page: 1,
    per_page: 10,
});

const columns = [
    { key: 'book', label: 'کتاب' },
    { key: 'category', label: 'دسته / قفسه' },
    { key: 'copies', label: 'نسخه‌ها' },
    { key: 'status', label: 'وضعیت' },
    { key: 'actions', label: 'عملیات' },
];

const { data, error, execute, loading } = useAsyncState(async () => {
    const response = await api.get(props.endpoint, { params: filters });

    return response.data;
});

const rows = computed(() => data.value?.data || []);
const meta = computed(() => data.value?.meta || {});
const filterMeta = computed(() => data.value?.filters || {});

function percent(row) {
    const total = Number(row.total_copies || 0);

    if (total <= 0) {
        return 0;
    }

    return Math.min(100, Math.round((Number(row.available_copies || 0) / total) * 100));
}

function goToPage(page) {
    filters.page = page;
    execute();
}

function clearFilters() {
    filters.q = '';
    filters.status = '';
    filters.category = '';
    filters.shelf = '';
    filters.page = 1;
    execute();
}

watch(
    () => [filters.q, filters.status, filters.category, filters.shelf],
    () => {
        filters.page = 1;
        execute();
    },
);

onMounted(() => execute());
</script>

<template>
    <section class="fanous-vue-library-books">
        <header class="fanous-vue-summary__header">
            <div>
                <h2 class="fanous-vue-title">{{ title }}</h2>
                <p v-if="meta.total !== undefined">
                    نمایش {{ meta.from || 0 }} تا {{ meta.to || 0 }} از {{ meta.total || 0 }} کتاب
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
                <span>عنوان‌ها</span>
                <strong>{{ data.summary.titles }}</strong>
            </article>
            <article>
                <span>نسخه‌های موجود</span>
                <strong>{{ data.summary.available_copies }}</strong>
            </article>
            <article>
                <span>کل نسخه‌ها</span>
                <strong>{{ data.summary.total_copies }}</strong>
            </article>
        </div>

        <div class="fanous-vue-book-filters">
            <SearchInput v-model="filters.q" placeholder="جستجوی عنوان، نویسنده، ISBN، بارکد یا کد نسخه" />

            <select v-model="filters.status" class="fanous-vue-input">
                <option value="">همه وضعیت‌ها</option>
                <option v-for="(label, value) in filterMeta.statuses || {}" :key="value" :value="value">
                    {{ label }}
                </option>
            </select>

            <select v-model="filters.category" class="fanous-vue-input">
                <option value="">همه دسته‌ها</option>
                <option v-for="category in filterMeta.categories || []" :key="category" :value="category">
                    {{ category }}
                </option>
            </select>

            <input v-model="filters.shelf" class="fanous-vue-input" placeholder="قفسه">
        </div>

        <ErrorState v-if="error" :message="error.message" />

        <DataTable v-else :columns="columns" :loading="loading" :rows="rows">
            <template #cell-book="{ row }">
                <div class="fanous-vue-finance-party">
                    <strong>{{ row.title }}</strong>
                    <small>{{ row.author || 'نویسنده نامشخص' }}</small>
                    <small v-if="row.isbn" class="fanous-vue-ltr">{{ row.isbn }}</small>
                </div>
            </template>

            <template #cell-category="{ row }">
                <div class="fanous-vue-finance-party">
                    <strong>{{ row.category || 'بدون دسته' }}</strong>
                    <small>قفسه: <span class="fanous-vue-ltr">{{ row.shelf_code || 'N/A' }}</span></small>
                </div>
            </template>

            <template #cell-copies="{ row }">
                <div class="fanous-vue-copy-cell">
                    <strong>{{ row.available_copies }} / {{ row.total_copies }}</strong>
                    <span class="fanous-vue-usage"><span :style="{ width: `${percent(row)}%` }"></span></span>
                    <small>{{ row.physical_copies }} نسخه فزیکی</small>
                </div>
            </template>

            <template #cell-status="{ row }">
                <span class="fanous-vue-badge" :class="`is-${row.status}`">{{ row.status_label }}</span>
            </template>

            <template #cell-actions="{ row }">
                <div class="fanous-vue-table-actions">
                    <a v-if="row.links.edit" :href="row.links.edit">ویرایش</a>
                    <a v-if="row.links.labels" :href="row.links.labels">لیبل‌ها</a>
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
