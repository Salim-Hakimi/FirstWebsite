<script setup>
defineProps({
    message: {
        type: String,
        default: 'موردی برای نمایش وجود ندارد.',
    },
});
</script>

<template>
    <div class="fanous-vue-state">
        {{ message }}
    </div>
</template>
