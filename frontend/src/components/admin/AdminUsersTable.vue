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
        default: '/api/admin/users',
    },
    title: {
        type: String,
        default: 'جستجوی سریع کاربران',
    },
    vueActions: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['edit']);

const filters = reactive({
    q: '',
    role: '',
    status: '',
    page: 1,
    per_page: 10,
});

const columns = [
    { key: 'user', label: 'کاربر' },
    { key: 'contact', label: 'تماس / ایمیل' },
    { key: 'role', label: 'نقش' },
    { key: 'status', label: 'وضعیت' },
    { key: 'created_at', label: 'ساخته‌شده' },
    { key: 'actions', label: 'عملیات' },
];

const { data, error, execute, loading } = useAsyncState(async () => {
    const response = await api.get(props.endpoint, { params: filters });

    return response.data;
});

const rows = computed(() => data.value?.data || []);
const meta = computed(() => data.value?.meta || {});
const filterMeta = computed(() => data.value?.filters || {});

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
    filters.role = '';
    filters.status = '';
    filters.page = 1;
    execute();
}

watch(
    () => [filters.q, filters.role, filters.status],
    () => {
        filters.page = 1;
        execute();
    },
);

onMounted(() => execute());

defineExpose({
    reload: execute,
});
</script>

<template>
    <section class="fanous-vue-users">
        <header class="fanous-vue-summary__header">
            <div>
                <h2 class="fanous-vue-title">{{ title }}</h2>
                <p v-if="meta.total !== undefined">
                    نمایش {{ meta.from || 0 }} تا {{ meta.to || 0 }} از {{ meta.total || 0 }} کاربر
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

        <div class="fanous-vue-user-filters">
            <SearchInput v-model="filters.q" placeholder="جستجوی نام، ایمیل یا شماره تماس" />

            <select v-model="filters.role" class="fanous-vue-input">
                <option value="">همه نقش‌ها</option>
                <option v-for="(label, value) in filterMeta.roles || {}" :key="value" :value="value">
                    {{ label }}
                </option>
            </select>

            <select v-model="filters.status" class="fanous-vue-input">
                <option value="">همه وضعیت‌ها</option>
                <option v-for="(label, value) in filterMeta.statuses || {}" :key="value" :value="value">
                    {{ label }}
                </option>
            </select>
        </div>

        <ErrorState v-if="error" :message="error.message" />

        <DataTable v-else :columns="columns" :loading="loading" :rows="rows">
            <template #cell-user="{ row }">
                <div class="fanous-vue-member-cell">
                    <img v-if="row.profile_photo_url" :src="row.profile_photo_url" :alt="row.name">
                    <span v-else>{{ row.name?.slice(0, 1) }}</span>
                    <div>
                        <strong>{{ row.name }}</strong>
                        <small v-if="row.is_current_user">حساب فعلی</small>
                    </div>
                </div>
            </template>

            <template #cell-contact="{ row }">
                <div class="fanous-vue-finance-party">
                    <span class="fanous-vue-ltr">{{ row.email }}</span>
                    <small>{{ row.phone || 'واتساپ ثبت نشده' }}</small>
                </div>
            </template>

            <template #cell-role="{ row }">
                <span class="fanous-vue-badge is-primary">{{ row.role_label }}</span>
            </template>

            <template #cell-status="{ row }">
                <span class="fanous-vue-badge" :class="`is-${row.status}`">{{ row.status_label }}</span>
            </template>

            <template #cell-created_at="{ row }">
                {{ dateLabel(row.created_at) }}
            </template>

            <template #cell-actions="{ row }">
                <div class="fanous-vue-table-actions">
                    <button v-if="vueActions && row.links.api_show" type="button" @click="emit('edit', row)">ویرایش</button>
                    <a v-else-if="row.links.edit" :href="row.links.edit">ویرایش</a>
                    <span v-else class="fanous-vue-lock-label">قفل</span>
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
