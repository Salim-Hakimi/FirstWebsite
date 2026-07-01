import { api } from './api';

export function login(payload) {
    return api.post('/login', payload);
}

export function logout() {
    return api.post('/logout');
}
