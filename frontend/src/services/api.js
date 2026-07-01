import axios from 'axios';

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

export const api = axios.create({
    baseURL: import.meta.env.VITE_API_BASE_URL || '',
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
    },
    withCredentials: true,
});

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401 && ! window.location.pathname.startsWith('/login')) {
            window.location.assign('/login');
        }

        return Promise.reject(normalizeApiError(error));
    },
);

export function normalizeApiError(error) {
    const response = error.response;
    const data = response?.data || {};

    return {
        status: response?.status || 0,
        message: data.message || error.message || 'Request failed.',
        errors: data.errors || {},
        raw: error,
    };
}
