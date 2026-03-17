import { defineStore } from 'pinia';
import { ref } from 'vue';
import api from '../services/api';

export const useAuthStore = defineStore('auth', () => {
const user = ref<any>(null);
    const token = ref<string | null>(localStorage.getItem('access_token'));

        const setAuth = (userData: any, authToken: string) => {
        user.value = userData;
        token.value = authToken;
        localStorage.setItem('access_token', authToken);
        localStorage.setItem('user', JSON.stringify(userData));
        };

        const clearAuth = () => {
        user.value = null;
        token.value = null;
        localStorage.removeItem('access_token');
        localStorage.removeItem('user');
        };

        const login = async (credentials: { email: string; password: string }) => {
        const response = await api.post('/login', credentials);
        setAuth(response.data.user, response.data.access_token);
        return response.data;
        };

        const logout = async () => {
        try {
        await api.post('/logout');
        } finally {
        clearAuth();
        }
        };

        // Load user dari local storage saat inisialisasi
        const init = () => {
        const storedUser = localStorage.getItem('user');
        if (storedUser) {
        user.value = JSON.parse(storedUser);
        }
        };

        return { user, token, login, logout, clearAuth, init };
        });
