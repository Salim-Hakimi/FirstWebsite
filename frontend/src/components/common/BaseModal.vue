<script setup>
defineProps({
    open: Boolean,
    title: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['close']);
</script>

<template>
    <Teleport to="body">
        <div v-if="open" class="fanous-vue-modal" role="dialog" aria-modal="true">
            <button class="fanous-vue-modal__backdrop" type="button" @click="emit('close')" />
            <section class="fanous-vue-modal__panel">
                <header class="fanous-vue-modal__header">
                    <h2>{{ title }}</h2>
                    <button type="button" @click="emit('close')">×</button>
                </header>
                <slot />
            </section>
        </div>
    </Teleport>
</template>
