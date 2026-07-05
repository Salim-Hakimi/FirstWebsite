<script setup>
import { computed, onMounted, reactive, watch } from 'vue';
import { api } from '../../services/api';
import { useAsyncState } from '../../composables/useAsyncState';
import SearchInput from '../forms/SearchInput.vue';
import DataTable from '../tables/DataTable.vue';
import ErrorState from '../common/ErrorState.vue';

const props = defineProps({
    endpoint: {
        type: String,
        default: '/api/library/members',
    },
    title: {
        type: String,
        default: 'اعضای کتابخانه',
    },
});

const filters = reactive({
    q: '',
    status: '',
    page: 1,
    per_page: 8,
});

const columns = [
    { key: 'member', label: 'عضو' },
    { key: 'phone', label: 'واتساپ' },
    { key: 'payment', label: 'پرداخت' },
    { key: 'balance', label: 'باقی' },
    { key: 'card', label: 'اعتبار کارت' },
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
        left: 'خارج شده',
    }[status] || status || 'نامشخص';
}

function paymentLabel(status) {
    return status === 'paid' ? 'پرداخت شده' : 'پرداخت نشده';
}

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
    <section class="fanous-vue-library-members">
        <header class="fanous-vue-summary__header">
            <div>
                <h2 class="fanous-vue-title">{{ title }}</h2>
                <p v-if="meta.total !== undefined">
                    نمایش {{ meta.from || 0 }} تا {{ meta.to || 0 }} از {{ meta.total || 0 }} عضو
                </p>
            </div>
            <button class="fanous-vue-refresh" type="button" :disabled="loading" @click="execute">
                تازه‌سازی
            </button>
        </header>

        <div class="fanous-vue-member-filters">
            <SearchInput v-model="filters.q" placeholder="جستجوی نام، تماس، تذکره یا کد عضویت" />
            <select v-model="filters.status" class="fanous-vue-input">
                <option value="">همه وضعیت‌ها</option>
                <option value="active">فعال</option>
                <option value="suspended">مسدود</option>
                <option value="left">خارج شده</option>
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
                        <small>{{ row.member_code || 'بدون کد' }} · {{ statusLabel(row.status) }}</small>
                    </div>
                </div>
            </template>

            <template #cell-payment="{ row }">
                {{ paymentLabel(row.payment_status) }}
            </template>

            <template #cell-balance="{ row }">
                {{ money(row.monthly_balance) }}
            </template>

            <template #cell-card="{ row }">
                {{ dateLabel(row.membership_expires_at) }}
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
