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
        default: '/api/library/fee-reminders',
    },
    title: {
        type: String,
        default: 'جستجوی سریع یادآوری فیس',
    },
});

const filters = reactive({
    q: '',
    status: 'due_soon',
    page: 1,
    per_page: 10,
});

const columns = [
    { key: 'member', label: 'عضو' },
    { key: 'due', label: 'سررسید' },
    { key: 'amount', label: 'قابل پرداخت' },
    { key: 'message', label: 'پیام' },
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
    filters.status = 'due_soon';
    filters.page = 1;
    execute();
}

watch(
    () => [filters.q, filters.status],
    () => {
        filters.page = 1;
        execute();
    },
);

onMounted(() => execute());
</script>

<template>
    <section class="fanous-vue-fee-reminders">
        <header class="fanous-vue-summary__header">
            <div>
                <h2 class="fanous-vue-title">{{ title }}</h2>
                <p v-if="meta.total !== undefined">
                    نمایش {{ meta.from || 0 }} تا {{ meta.to || 0 }} از {{ meta.total || 0 }} عضو
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
                <span>نیازمند یادآوری</span>
                <strong>{{ data.summary.due_soon }}</strong>
            </article>
            <article>
                <span>گذشته از سررسید</span>
                <strong>{{ data.summary.overdue }}</strong>
            </article>
        </div>

        <div class="fanous-vue-fee-filters">
            <SearchInput v-model="filters.q" placeholder="جستجوی نام، تماس، نام پدر یا کد عضویت" />

            <select v-model="filters.status" class="fanous-vue-input">
                <option v-for="(label, value) in filterMeta.statuses || {}" :key="value" :value="value">
                    {{ label }}
                </option>
            </select>
        </div>

        <ErrorState v-if="error" :message="error.message" />

        <DataTable v-else :columns="columns" :loading="loading" :rows="rows">
            <template #cell-member="{ row }">
                <div class="fanous-vue-member-cell">
                    <img v-if="row.profile_photo_url" :src="row.profile_photo_url" :alt="row.full_name">
                    <span v-else>{{ row.full_name?.slice(0, 1) }}</span>
                    <div>
                        <strong>{{ row.full_name }}</strong>
                        <small>{{ row.member_code || 'بدون کد' }} · <span class="fanous-vue-ltr">{{ row.phone || 'بدون تماس' }}</span></small>
                    </div>
                </div>
            </template>

            <template #cell-due="{ row }">
                <span class="fanous-vue-badge" :class="row.is_overdue ? 'is-late' : 'is-borrowed'">
                    {{ row.is_overdue ? 'گذشته' : 'نزدیک' }}
                </span>
                <small>{{ dateLabel(row.next_payment_due_at) }}</small>
                <small>آخرین: {{ dateLabel(row.last_fee_reminder_at) }}</small>
            </template>

            <template #cell-amount="{ row }">
                <div class="fanous-vue-finance-party">
                    <strong>{{ money(row.balance) }}</strong>
                    <small>فیس: {{ money(row.membership_fee) }}</small>
                    <small>جریمه: {{ money(row.fine_amount) }}</small>
                </div>
            </template>

            <template #cell-message="{ row }">
                <p class="fanous-vue-message-preview">{{ row.message }}</p>
            </template>

            <template #cell-actions="{ row }">
                <div class="fanous-vue-table-actions">
                    <a v-if="row.links.whatsapp" :href="row.links.whatsapp" target="_blank" rel="noopener">واتساپ</a>
                    <a :href="row.links.show">پروفایل</a>
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
