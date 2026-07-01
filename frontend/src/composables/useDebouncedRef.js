import { customRef } from 'vue';

export function useDebouncedRef(initialValue = '', delay = 300) {
    let value = initialValue;
    let timeout;

    return customRef((track, trigger) => ({
        get() {
            track();
            return value;
        },
        set(nextValue) {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                value = nextValue;
                trigger();
            }, delay);
        },
    }));
}
