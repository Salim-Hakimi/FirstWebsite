<script setup>
defineProps({
    message: {
        type: String,
        default: 'درخواست انجام نشد.',
    },
});
</script>

<template>
    <div class="fanous-vue-state is-error" role="alert">
        {{ message }}
    </div>
</template>
