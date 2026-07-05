<script setup>
defineProps({
    eyebrow: {
        type: String,
        default: '',
    },
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
    actions: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <div class="fanous-vue-module">
        <header class="fanous-vue-module__header">
            <div>
                <span v-if="eyebrow">{{ eyebrow }}</span>
                <h1>{{ title }}</h1>
                <p v-if="description">{{ description }}</p>
            </div>

            <div v-if="actions.length" class="fanous-vue-module__actions">
                <a
                    v-for="action in actions"
                    :key="action.href"
                    :class="{ 'is-primary': action.primary }"
                    :href="action.href"
                    :data-fanous-page-modal="action.modal ? '' : null"
                    :data-modal-title="action.modalTitle || action.label"
                >
                    {{ action.label }}
                </a>
            </div>
        </header>

        <slot />
    </div>
</template>
