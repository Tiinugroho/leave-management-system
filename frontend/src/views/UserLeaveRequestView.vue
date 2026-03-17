<template>
    <div class="app-layout">
        <Sidebar />

        <div class="main-wrapper">
            <header class="top-navbar">
                <div class="navbar-container">
                    <Breadcrumb :items="['User Menu', 'Ajukan Cuti']" />
                    <div class="role-toggle">
                        <span class="text-sm font-medium text-gray-500 mr-3">Role:</span>
                        <div class="toggle-bg">
                            <button class="toggle-btn" disabled>Admin</button>
                            <button class="toggle-btn active">User</button>
                        </div>
                    </div>
                </div>
            </header>

            <main class="content-area">
                <div class="content-container max-w-3xl">
                    <div class="page-header mb-6">
                        <h2 class="page-title">Ajukan Cuti</h2>
                        <p class="page-description">Isi form untuk mengajukan permohonan cuti baru.</p>
                    </div>

                    <div class="form-card mb-6">
                        <h3 class="card-title">Form Pengajuan Cuti</h3>
                        <form @submit.prevent="submitLeaveRequest">
                            
                            <div class="mb-4">
                                <Label text="Jenis Cuti" required />
                                <select v-model="form.leave_type_id" class="form-input" required>
                                    <option value="" disabled>Pilih tipe cuti...</option>
                                    <option v-for="type in leaveTypes" :key="type.id" :value="type.id">
                                        {{ type.name }} (sisa: {{ getSisa(type.id) }} hari)
                                    </option>
                                </select>
                            </div>

                            <div class="grid-2 mb-4">
                                <div>
                                    <Label text="Tanggal Mulai" required />
                                    <input type="date" v-model="form.start_date" class="form-input" required :min="today">
                                </div>
                                <div>
                                    <Label text="Tanggal Selesai" required />
                                    <input type="date" v-model="form.end_date" class="form-input" required :min="form.start_date || today">
                                </div>
                            </div>

                            <div v-if="form.start_date && form.end_date && form.leave_type_id" class="calculation-box mb-4">
                                <p class="calc-text font-bold text-indigo-900">
                                    📅 Total: <strong> {{ calculatedDays }}</strong> hari ({{ formatDate(form.start_date) }} - {{ formatDate(form.end_date) }})
                                </p>
                                <p class="calc-text text-indigo-700 mt-1">
                                    Sisa balance setelah approved: <strong>{{ currentBalance }}</strong> &rarr; 
                                    <strong :class="currentBalance - calculatedDays < 0 ? 'text-red-600' : 'text-indigo-900'">
                                        {{ currentBalance - calculatedDays }} hari
                                    </strong>
                                </p>
                            </div>

                            <div class="mb-6">
                                <Label text="Alasan" required />
                                <textarea v-model="form.reason" class="form-input" rows="3" placeholder="Contoh: Liburan keluarga ke Malang" required></textarea>
                            </div>

                            <Button 
                                :text="isProcessing ? 'Memproses...' : 'Submit Pengajuan'" 
                                variant="primary" 
                                type="submit" 
                                :disabled="isProcessing || (currentBalance - calculatedDays < 0)"
                            />
                        </form>
                    </div>

                    <div class="validation-card-green mb-4">
                        <h4 class="font-bold text-gray-800 mb-2 text-sm">Validasi yang Dicek</h4>
                        <ul class="check-list">
                            <li>✅ <strong>Tanggal valid</strong> (start &le; end, tidak di masa lalu)</li>
                            <li>✅ <strong>Kuota cukup</strong> (sisa hari &ge; hari yang diajukan)</li>
                            <li>✅ <strong>Tidak ada overlap</strong> dengan request pending/approved</li>
                        </ul>
                    </div>
                </div>
            </main>
        </div>

        <Modal :isOpen="showSuccessModal" @close="handleSuccessClose" :showFooter="false">
            <div class="success-modal-content">
                <div class="icon-success-wrapper mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="success-heading">Pengajuan Berhasil!</h3>
                <p class="success-text">
                    Permohonan cuti Anda telah disubmit dan saat ini berstatus <strong>Pending</strong> menunggu persetujuan Admin.
                </p>
                <div class="success-action">
                    <Button text="Lihat Riwayat Cuti" variant="primary" @click="handleSuccessClose" />
                </div>
            </div>
        </Modal>

    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/authStore';
import { useRouter } from 'vue-router';
import Sidebar from '../components/Sidebar.vue';
import Breadcrumb from '../components/Breadcrumb.vue';
import Label from '../components/Label.vue';
import Button from '../components/Button.vue';
import Modal from '../components/Modal.vue';

const authStore = useAuthStore();
const router = useRouter();
const isProcessing = ref(false);
const showSuccessModal = ref(false); // State untuk mengontrol Modal Sukses

const leaveTypes = ref<any[]>([]);
const userBalances = ref<any[]>([]);
const today = new Date().toISOString().split('T')[0];

const form = ref({ leave_type_id: '', start_date: '', end_date: '', reason: '' });

const getHeaders = () => ({ headers: { Authorization: `Bearer ${authStore.token}` } });

// Ambil data Master Cuti & Saldo User
const fetchData = async () => {
    try {
        const [typeRes, dashboardRes] = await Promise.all([
            axios.get('http://localhost:8000/api/leave-types', getHeaders()),
            axios.get('http://localhost:8000/api/user/dashboard', getHeaders())
        ]);
        leaveTypes.value = typeRes.data.data || [];
        userBalances.value = dashboardRes.data.balances || [];
    } catch (error) {
        console.error("Gagal memuat data", error);
    }
};

// Utilities & Kalkulasi
const getSisa = (typeId: number | string) => {
    const balance = userBalances.value.find((b: any) => b.leave_type_id === typeId);
    return balance ? (balance.total_quota - balance.used) : 0;
};

const currentBalance = computed(() => form.value.leave_type_id ? getSisa(form.value.leave_type_id) : 0);

const calculatedDays = computed(() => {
    if (!form.value.start_date || !form.value.end_date) return 0;
    const start = new Date(form.value.start_date);
    const end = new Date(form.value.end_date);
    if (end < start) return 0;
    return Math.ceil((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24)) + 1;
});

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
};

// Submit Action
const submitLeaveRequest = async () => {
    if (currentBalance.value - calculatedDays.value < 0) {
        alert("Gagal: Kuota cuti Anda tidak mencukupi!"); // Alert error dibiarkan standar atau bisa diganti modal error juga
        return;
    }

    isProcessing.value = true;
    try {
        await axios.post('http://localhost:8000/api/user/leave-requests', {
            ...form.value,
            total_days: calculatedDays.value
        }, getHeaders());
        
        // JIKA SUKSES: Buka Modal, Jangan redirect dulu
        showSuccessModal.value = true;

    } catch (error: any) {
        alert(error.response?.data?.message || "Gagal mengajukan cuti.");
    } finally {
        isProcessing.value = false;
    }
};

// Fungsi untuk menutup modal dan pindah halaman
const handleSuccessClose = () => {
    showSuccessModal.value = false;
    router.push('/user/riwayat-cuti');
};

onMounted(() => {
    authStore.init();
    fetchData();
});
</script>

<style scoped>
/* Layout CSS bawaan */
.app-layout { display: flex; min-height: 100vh; background-color: #f8fafc; font-family: sans-serif; }
.main-wrapper { flex: 1; margin-left: 260px; display: flex; flex-direction: column; }
.navbar-container { width: 100%; display: flex; justify-content: space-between; align-items: center; padding: 0 2rem;}
.top-navbar { background: white; border-bottom: 1px solid #edf2f7; height: 64px; display: flex; align-items: center; position: sticky; top: 0; z-index: 20; }
.content-area { padding: 2rem; }
.max-w-3xl { max-width: 48rem; margin: 0 auto; }

/* Header & Toggle */
.page-title { font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0 0 0.25rem 0; }
.page-description { font-size: 0.875rem; color: #64748b; margin: 0; }
.role-toggle { display: flex; align-items: center; }
.toggle-bg { background-color: #f3f4f6; padding: 0.25rem; border-radius: 0.5rem; display: flex; gap: 0.25rem; }
.toggle-btn { padding: 0.375rem 1rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; border: none; background: transparent; color: #6b7280; }
.toggle-btn.active { background-color: #4f46e5; color: white; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); }

/* Form Styling */
.form-card { background: white; border: 1px solid #f1f5f9; padding: 1.5rem; border-radius: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.card-title { font-size: 1.125rem; font-weight: 700; color: #334155; margin: 0 0 1.25rem 0; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.75rem; }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.mb-4 { margin-bottom: 1rem; }
.mb-6 { margin-bottom: 1.5rem; }
.mt-1 { margin-top: 0.25rem; }
.form-input { width: 100%; padding: 0.625rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.875rem; outline: none; }
.form-input:focus { border-color: #4f46e5; box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1); }

/* Calculation Box (Sesuai Mockup) */
.calculation-box { background-color: #eef2ff; border-radius: 8px; padding: 1rem; border: 1px solid #e0e7ff; }
.calc-text { font-size: 0.875rem; margin: 0; }
.text-indigo-900 { color: #312e81; }
.text-indigo-700 { color: #4338ca; }
.text-red-600 { color: #dc2626; }

/* Validation Info Card */
.validation-card-green { background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 1.25rem; }
.check-list { list-style: none; padding: 0; margin: 0; font-size: 0.875rem; color: #166534; line-height: 1.6; }

/* Custom Success Modal CSS */
.success-modal-content { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1rem; text-align: center; }
.icon-success-wrapper { background-color: #dcfce7; border-radius: 50%; padding: 1rem; display: inline-flex; }
.success-heading { font-size: 1.25rem; font-weight: 700; color: #111827; margin: 0 0 0.5rem 0; }
.success-text { font-size: 0.875rem; color: #6b7280; margin: 0 0 1.5rem 0; line-height: 1.5; }
.success-action { width: 100%; display: flex; justify-content: center; }
</style>