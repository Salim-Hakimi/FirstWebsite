<script setup>
import { watch } from 'vue';
import { useDebouncedRef } from '../../composables/useDebouncedRef';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    delay: {
        type: Number,
        default: 300,
    },
    placeholder: {
        type: String,
        default: 'جستجو...',
    },
});

const emit = defineEmits(['update:modelValue']);
const search = useDebouncedRef(props.modelValue, props.delay);

watch(search, (value) => emit('update:modelValue', value));
</script>

<template>
    <input
        v-model="search"
        class="fanous-vue-input"
        type="search"
        :placeholder="placeholder"
    >
</template>
