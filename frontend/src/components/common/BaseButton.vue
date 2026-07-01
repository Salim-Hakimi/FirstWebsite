<script setup>
defineProps({
    busy: Boolean,
    type: {
        type: String,
        default: 'button',
    },
    variant: {
        type: String,
        default: 'primary',
    },
});
</script>

<template>
    <button class="fanous-vue-button" :class="`is-${variant}`" :disabled="busy" :type="type">
        <span v-if="busy" class="fanous-vue-spinner" aria-hidden="true" />
        <slot />
    </button>
</template>
