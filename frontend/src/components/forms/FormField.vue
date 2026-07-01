<script setup>
defineProps({
    error: {
        type: String,
        default: '',
    },
    label: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <label class="fanous-vue-field">
        <span>{{ label }}</span>
        <slot />
        <small v-if="error" class="fanous-vue-field__error">{{ error }}</small>
    </label>
</template>
