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
        default: '/api/library/inventory/copies',
    },
    title: {
        type: String,
        default: 'جستجوی سریع نسخه‌ها',
    },
});

const filters = reactive({
    q: '',
    status: '',
    shelf: '',
    category: '',
    page: 1,
    per_page: 10,
});

const columns = [
    { key: 'book', label: 'کتاب' },
    { key: 'copy', label: 'کد نسخه' },
    { key: 'shelf', label: 'قفسه' },
    { key: 'status', label: 'وضعیت' },
    { key: 'price', label: 'ارزش' },
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

function goToPage(page) {
    filters.page = page;
    execute();
}

function clearFilters() {
    filters.q = '';
    filters.status = '';
    filters.shelf = '';
    filters.category = '';
    filters.page = 1;
    execute();
}

watch(
    () => [filters.q, filters.status, filters.shelf, filters.category],
    () => {
        filters.page = 1;
        execute();
    },
);

onMounted(() => execute());
</script>

<template>
    <section class="fanous-vue-inventory-copies">
        <header class="fanous-vue-summary__header">
            <div>
                <h2 class="fanous-vue-title">{{ title }}</h2>
                <p v-if="meta.total !== undefined">
                    نمایش {{ meta.from || 0 }} تا {{ meta.to || 0 }} از {{ meta.total || 0 }} نسخه
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
                <span>کل نسخه‌ها</span>
                <strong>{{ data.summary.total }}</strong>
            </article>
            <article>
                <span>موجود</span>
                <strong>{{ data.summary.available }}</strong>
            </article>
            <article>
                <span>مشکل‌دار</span>
                <strong>{{ data.summary.problem }}</strong>
            </article>
        </div>

        <div class="fanous-vue-inventory-filters">
            <SearchInput v-model="filters.q" placeholder="جستجوی کد نسخه، بارکد، عنوان، نویسنده یا ISBN" />

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
                    <strong>{{ row.book?.title || 'کتاب نامشخص' }}</strong>
                    <small>{{ row.book?.author || 'نویسنده نامشخص' }}</small>
                    <small>{{ row.book?.category || 'بدون دسته' }}</small>
                </div>
            </template>

            <template #cell-copy="{ row }">
                <div class="fanous-vue-finance-party">
                    <strong class="fanous-vue-ltr">{{ row.copy_code }}</strong>
                    <small class="fanous-vue-ltr">{{ row.barcode || 'N/A' }}</small>
                </div>
            </template>

            <template #cell-shelf="{ row }">
                <span class="fanous-vue-ltr">{{ row.shelf_code || 'N/A' }}</span>
            </template>

            <template #cell-status="{ row }">
                <span class="fanous-vue-badge" :class="`is-${row.status}`">{{ row.status_label }}</span>
                <small v-if="row.condition">{{ row.condition }}</small>
            </template>

            <template #cell-price="{ row }">
                {{ money(row.purchase_price) }}
            </template>

            <template #cell-actions="{ row }">
                <div class="fanous-vue-table-actions">
                    <a v-if="row.links.manage" :href="row.links.manage">مدیریت</a>
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
