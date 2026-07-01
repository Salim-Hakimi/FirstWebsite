<script setup>
import { onMounted } from 'vue';
import { api } from '../../services/api';
import { useAsyncState } from '../../composables/useAsyncState';
import BaseCard from './BaseCard.vue';
import LoadingState from './LoadingState.vue';
import ErrorState from './ErrorState.vue';
import EmptyState from './EmptyState.vue';

const props = defineProps({
    endpoint: {
        type: String,
        required: true,
    },
    title: {
        type: String,
        default: '',
    },
});

const { data, error, execute, loading } = useAsyncState(async () => {
    const response = await api.get(props.endpoint);
    return response.data;
});

onMounted(() => execute());
</script>

<template>
    <BaseCard>
        <h3 v-if="title" class="fanous-vue-card__title">{{ title }}</h3>
        <LoadingState v-if="loading" />
        <ErrorState v-else-if="error" :message="error.message" />
        <EmptyState v-else-if="!data" />
        <pre v-else class="fanous-vue-json">{{ data }}</pre>
    </BaseCard>
</template>
