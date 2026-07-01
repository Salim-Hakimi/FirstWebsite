<script setup>
import EmptyState from '../common/EmptyState.vue';
import LoadingState from '../common/LoadingState.vue';

defineProps({
    columns: {
        type: Array,
        default: () => [],
    },
    rows: {
        type: Array,
        default: () => [],
    },
    loading: Boolean,
});
</script>

<template>
    <LoadingState v-if="loading" />
    <EmptyState v-else-if="rows.length === 0" />
    <div v-else class="fanous-vue-table-wrap">
        <table class="fanous-vue-table">
            <thead>
                <tr>
                    <th v-for="column in columns" :key="column.key">
                        {{ column.label }}
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(row, index) in rows" :key="row.id ?? index">
                    <td v-for="column in columns" :key="column.key">
                        <slot :name="`cell-${column.key}`" :row="row" :value="row[column.key]">
                            {{ row[column.key] }}
                        </slot>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
