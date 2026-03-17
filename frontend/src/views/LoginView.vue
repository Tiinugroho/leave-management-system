<template>
    <div class="login-wrapper">
        <div class="login-card">

            <div class="brand-header">
                <h1><span class="text-dark">Leave</span><span class="text-blue">Hub</span></h1>
                <p class="subtitle">Leave Request Management System</p>
            </div>

            <div v-if="generalError" class="alert-error">
                {{ generalError }}
            </div>

            <form @submit.prevent="handleLogin" class="form-body">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" v-model="form.email" :class="{ 'input-error': errors.email }"
                        placeholder="admin@energeek.id" />
                    <span v-if="errors.email" class="text-error">{{ errors.email[0] }}</span>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" v-model="form.password"
                        :class="{ 'input-error': errors.password }" placeholder="••••••••••" />
                    <span v-if="errors.password" class="text-error">{{ errors.password[0] }}</span>
                </div>

                <button type="submit" class="btn-primary" :disabled="isLoading">
                    {{ isLoading ? 'Memproses...' : 'Login' }}
                </button>
            </form>

            <div class="login-footer">
                <p>Sanctum PAT · No register endpoint</p>
            </div>

        </div>

        <Modal :isOpen="showLogoutModal" title="Logout Sukses" @close="closeLogoutModal">
            <div class="text-center">
                <p>Anda telah berhasil logout dari sistem.</p>
            </div>
        </Modal>
    </div>
</template>

<script setup lang="ts">
import {
    ref,
    onMounted
} from 'vue';
import {
    useRouter,
    useRoute
} from 'vue-router';
import {
    useAuthStore
} from '../stores/authStore';
import axios from 'axios';
import Modal from '../components/Modal.vue';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const form = ref({
    email: '',
    password: ''
});
const isLoading = ref(false);
const errors = ref<Record<string,
    string[]>>({});
const generalError = ref('');

// State untuk memunculkan popup logout
const showLogoutModal = ref(false);

onMounted(() => {
    // Jika URL memiliki parameter ?logged_out=1, tampilkan modal
    if (route.query.logged_out) {
        showLogoutModal.value = true;
    }
});

const closeLogoutModal = () => {
    showLogoutModal.value = false;
    // Bersihkan parameter dari URL agar modal tidak muncul terus saat refresh
    router.replace({
        query: {}
    });
};

const handleLogin = async () => {
    isLoading.value = true;
    errors.value = {};
    generalError.value = '';

    try {
        await authStore.login({
            email: form.value.email,
            password: form.value.password
        });

        sessionStorage.setItem('isNewLogin', 'yes');
        if (authStore.user?.role === 'admin') {
            router.push('/dashboard'); // Arahkan ke Dashboard Admin
        } else {
            router.push('/user/dashboard'); // Arahkan ke Dashboard User (Sisa Kuota)
        }

    } catch (error: any) {
        if (axios.isAxiosError(error) && error.response) {
            const status = error.response.status;
            const data = error.response.data;

            if (status === 422 && data.errors) {
                errors.value = data.errors;
            } else if (status === 401) {
                generalError.value = data.message || 'Email atau password salah.';
            } else {
                generalError.value = 'Terjadi kesalahan pada server. Silakan coba lagi.';
            }
        } else {
            generalError.value = 'Tidak dapat terhubung ke server.';
        }
    } finally {
        isLoading.value = false;
    }
};
</script>

<style scoped>
/* Reset Font untuk menyesuaikan mockup (menggunakan font sans-serif modern) */
.login-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background-color: #f8fafc;
    /* Warna background abu-abu sangat terang */
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

/* Card Login Utama */
.login-card {
    background: #ffffff;
    padding: 2.5rem 2rem 1.5rem 2rem;
    border-radius: 12px;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    width: 100%;
    max-width: 420px;
    border: 1px solid #f1f5f9;
}

/* Header & Logo */
.brand-header {
    margin-bottom: 2rem;
}

.brand-header h1 {
    margin: 0 0 0.25rem 0;
    font-size: 1.75rem;
    font-weight: 800;
    letter-spacing: -0.5px;
}

.text-dark {
    color: #1e293b;
}

.text-blue {
    color: #3b82f6;
}

.subtitle {
    margin: 0;
    color: #64748b;
    font-size: 0.95rem;
    font-weight: 500;
}

/* Form Styles */
.form-group {
    margin-bottom: 1.25rem;
    display: flex;
    flex-direction: column;
}

label {
    margin-bottom: 0.4rem;
    font-weight: 700;
    color: #334155;
    font-size: 0.9rem;
}

input {
    padding: 0.75rem 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 1rem;
    color: #0f172a;
    outline: none;
    transition: all 0.2s;
    background-color: #fdfdfd;
}

input::placeholder {
    color: #94a3b8;
}

input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    background-color: #ffffff;
}

/* Error States */
.input-error {
    border-color: #ef4444;
}

.text-error {
    color: #ef4444;
    font-size: 0.8rem;
    margin-top: 0.4rem;
    font-weight: 500;
}

.alert-error {
    background: #fef2f2;
    color: #b91c1c;
    padding: 0.75rem;
    border-radius: 6px;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
    border: 1px solid #f87171;
    text-align: center;
}

/* Button */
.btn-primary {
    width: 100%;
    padding: 0.875rem;
    background-color: #3b82f6;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.2s;
    margin-top: 0.5rem;
}

.btn-primary:hover {
    background-color: #2563eb;
}

.btn-primary:disabled {
    background-color: #93c5fd;
    cursor: not-allowed;
}

/* Footer */
.login-footer {
    margin-top: 1.5rem;
    text-align: center;
}

.login-footer p {
    margin: 0;
    color: #94a3b8;
    font-size: 0.8rem;
    font-weight: 500;
}
</style>
