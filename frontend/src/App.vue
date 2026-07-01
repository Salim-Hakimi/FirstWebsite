<script setup>
import AppShell from './components/layout/AppShell.vue';

defineProps({
    context: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <AppShell :context="context">
        <RouterView />
    </AppShell>
</template>
