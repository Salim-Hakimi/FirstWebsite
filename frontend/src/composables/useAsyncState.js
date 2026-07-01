import { ref } from 'vue';

export function useAsyncState(callback) {
    const loading = ref(false);
    const error = ref(null);
    const data = ref(null);

    async function execute(...args) {
        loading.value = true;
        error.value = null;

        try {
            data.value = await callback(...args);
            return data.value;
        } catch (caught) {
            error.value = caught;
            throw caught;
        } finally {
            loading.value = false;
        }
    }

    return {
        data,
        error,
        execute,
        loading,
    };
}
