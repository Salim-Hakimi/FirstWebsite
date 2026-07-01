<script setup>
import { computed, onMounted } from 'vue';
import { api } from '../../services/api';
import { useAsyncState } from '../../composables/useAsyncState';
import BaseCard from '../common/BaseCard.vue';
import ErrorState from '../common/ErrorState.vue';
import LoadingState from '../common/LoadingState.vue';

const props = defineProps({
    endpoint: {
        type: String,
        default: '/api/dashboard/summary',
    },
    title: {
        type: String,
        default: 'Live dashboard summary',
    },
});

const { data, error, execute, loading } = useAsyncState(async () => {
    const response = await api.get(props.endpoint);
    return response.data;
});

const cards = computed(() => data.value?.cards || []);
const generatedAt = computed(() => {
    if (! data.value?.generated_at) {
        return '';
    }

    return new Intl.DateTimeFormat('fa-AF', {
        dateStyle: 'short',
        timeStyle: 'short',
    }).format(new Date(data.value.generated_at));
});

onMounted(() => execute());
</script>

<template>
    <BaseCard>
        <header class="fanous-vue-summary__header">
            <div>
                <h2 class="fanous-vue-title">{{ title }}</h2>
                <p v-if="generatedAt">آخرین بروزرسانی: {{ generatedAt }}</p>
            </div>
            <button class="fanous-vue-refresh" type="button" :disabled="loading" @click="execute">
                تازه‌سازی
            </button>
        </header>

        <LoadingState v-if="loading && cards.length === 0" />
        <ErrorState v-else-if="error" :message="error.message" />

        <div v-else class="fanous-vue-summary-grid">
            <article v-for="card in cards" :key="card.key" class="fanous-vue-summary-card">
                <span>{{ card.label }}</span>
                <strong>{{ card.value }}</strong>
                <small>{{ card.hint }}</small>
            </article>
        </div>
    </BaseCard>
</template>
