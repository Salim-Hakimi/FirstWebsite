<script setup>
defineProps({
    context: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <section class="fanous-vue-shell" dir="rtl">
        <slot />
    </section>
</template>
