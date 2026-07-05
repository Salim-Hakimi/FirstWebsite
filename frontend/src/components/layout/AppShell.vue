<script setup>
import { computed } from 'vue';

const props = defineProps({
    context: {
        type: Object,
        default: () => ({}),
    },
});

const user = computed(() => props.context.user || {});
const navigation = computed(() => props.context.navigation || []);
const currentPath = computed(() => window.location.pathname);
const logoUrl = '/logo/logo.jpg';

function isActive(item) {
    return currentPath.value === item.active || currentPath.value.startsWith(`${item.active}/`);
}

function toggleTheme() {
    const nextTheme = document.documentElement.dataset.theme === 'light' ? 'dark' : 'light';
    document.documentElement.dataset.theme = nextTheme;
    localStorage.setItem('fanous.theme', nextTheme);
}

function logout() {
    const form = document.createElement('form');
    const token = document.createElement('input');

    form.method = 'POST';
    form.action = props.context.logoutUrl || '/logout';
    token.type = 'hidden';
    token.name = '_token';
    token.value = props.context.csrfToken || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    form.appendChild(token);
    document.body.appendChild(form);
    form.submit();
}
</script>

<template>
    <section class="fanous-vue-shell fanous-vue-app-shell" dir="rtl">
        <aside class="fanous-vue-sidebar">
            <a class="fanous-vue-brand" href="/app">
                <img :src="logoUrl" alt="Fanous">
                <strong>فانوس</strong>
            </a>

            <div class="fanous-vue-user-card">
                <img v-if="user.profile_photo_url" :src="user.profile_photo_url" :alt="user.name">
                <span v-else>{{ (user.name || 'ف').slice(0, 1) }}</span>
                <div>
                    <strong>{{ user.name || 'کاربر' }}</strong>
                    <small>{{ user.email }}</small>
                </div>
            </div>

            <nav class="fanous-vue-nav" aria-label="ناوبری اصلی">
                <a
                    v-for="item in navigation"
                    :key="item.href"
                    :class="{ 'is-active': isActive(item) }"
                    :href="item.href"
                >
                    <span>{{ item.icon }}</span>
                    <strong>{{ item.label }}</strong>
                </a>
            </nav>
        </aside>

        <main class="fanous-vue-main">
            <header class="fanous-vue-topbar">
                <div>
                    <strong>پنل مدیریت فانوس</strong>
                    <small>نسخه Vue مرحله‌ای</small>
                </div>

                <div class="fanous-vue-topbar__actions">
                    <button type="button" @click="toggleTheme">حالت نمایش</button>
                    <a :href="context.legacyDashboardUrl || '/dashboard'">نسخه قبلی</a>
                    <button type="button" @click="logout">خروج</button>
                </div>
            </header>

            <section class="fanous-vue-page">
                <slot />
            </section>
        </main>
    </section>
</template>
