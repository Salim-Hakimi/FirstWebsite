import { defineStore } from 'pinia';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: window.FanousUser || null,
        intendedUrl: null,
    }),
    getters: {
        isAuthenticated: (state) => Boolean(state.user),
    },
    actions: {
        setUser(user) {
            this.user = user;
        },
        clearUser() {
            this.user = null;
        },
    },
});
