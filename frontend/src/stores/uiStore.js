import { defineStore } from 'pinia';

export const useUiStore = defineStore('ui', {
    state: () => ({
        loading: false,
        lastError: null,
        sidebarOpen: false,
    }),
    actions: {
        startLoading() {
            this.loading = true;
            this.lastError = null;
        },
        stopLoading() {
            this.loading = false;
        },
        setError(error) {
            this.lastError = error;
            this.loading = false;
        },
        toggleSidebar() {
            this.sidebarOpen = ! this.sidebarOpen;
        },
    },
});
