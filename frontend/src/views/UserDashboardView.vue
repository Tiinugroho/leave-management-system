<template>
    <div class="app-layout">
        <Sidebar />

        <div class="main-wrapper">
            <header class="top-navbar">
                <div class="navbar-container">
                    <div class="breadcrumb-area">
                        <Breadcrumb :items="['User Menu', 'Sisa Kuota']" />
                    </div>
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
                <div class="content-container">
                    <div class="page-header mb-6">
                        <h2 class="page-title">Sisa Kuota Cuti</h2>
                        <p class="page-description">Periode: Tahun {{ currentYear }} - Kalkulasi: total_quota - used</p>
                    </div>

                    <div v-if="isLoading" class="flex justify-center py-12 text-gray-500">
                        Memuat dashboard Anda...
                    </div>

                    <div v-else>
                        <div class="quota-grid mb-6">
                            <div class="quota-card">
                                <div class="flex items-center gap-2 mb-4">
                                    <span class="text-xl">🏖️</span>
                                    <h3 class="quota-title">Annual Leave</h3>
                                </div>
                                <div class="progress-container mb-2">
                                    <div class="progress-bar bg-indigo-500" :style="{ width: getPercentage(annual) + '%' }"></div>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500">Terpakai: <strong class="text-gray-900">{{ annual.used }} hari</strong></span>
                                    <span class="text-gray-500">Sisa: <strong class="text-gray-900">{{ annual.sisa }} / {{ annual.total }} hari</strong></span>
                                </div>
                            </div>

                            <div class="quota-card">
                                <div class="flex items-center gap-2 mb-4">
                                    <span class="text-xl">🏥</span>
                                    <h3 class="quota-title">Sick Leave</h3>
                                </div>
                                <div class="progress-container mb-2">
                                    <div class="progress-bar bg-emerald-500" :style="{ width: getPercentage(sick) + '%' }"></div>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="text-gray-500">Terpakai: <strong class="text-gray-900">{{ sick.used }} hari</strong></span>
                                    <span class="text-gray-500">Sisa: <strong class="text-gray-900">{{ sick.sisa }} / {{ sick.total }} hari</strong></span>
                                </div>
                            </div>
                        </div>

                        <div class="stats-request-grid">
                            <div class="stat-box">
                                <div class="mb-2"><span class="text-2xl">⏳</span></div>
                                <div class="text-gray-500 text-sm font-medium mb-1">Pending</div>
                                <div class="text-3xl font-bold text-orange-500 mb-1">{{ stats.pending }}</div>
                                <div class="text-xs text-gray-400">Menunggu approval</div>
                            </div>

                            <div class="stat-box">
                                <div class="mb-2"><span class="text-2xl text-emerald-500">✓</span></div>
                                <div class="text-gray-500 text-sm font-medium mb-1">Approved</div>
                                <div class="text-3xl font-bold text-emerald-500 mb-1">{{ stats.approved }}</div>
                                <div class="text-xs text-gray-400">Disetujui tahun ini</div>
                            </div>

                            <div class="stat-box">
                                <div class="mb-2"><span class="text-2xl text-red-500">✗</span></div>
                                <div class="text-gray-500 text-sm font-medium mb-1">Rejected</div>
                                <div class="text-3xl font-bold text-red-500 mb-1">{{ stats.rejected }}</div>
                                <div class="text-xs text-gray-400">Ditolak</div>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/authStore';
import Sidebar from '../components/Sidebar.vue';
import Breadcrumb from '../components/Breadcrumb.vue';

const authStore = useAuthStore();
const isLoading = ref(true);
const currentYear = ref(new Date().getFullYear());

// Data Default agar UI tidak error sebelum load
const annual = ref({ used: 0, sisa: 0, total: 12 });
const sick = ref({ used: 0, sisa: 0, total: 6 });
const stats = ref({ pending: 0, approved: 0, rejected: 0 });

// Kalkulasi Persentase Progress Bar (Sisa dibagi Total)
const getPercentage = (leaveData: any) => {
    if (leaveData.total === 0) return 0;
    return (leaveData.sisa / leaveData.total) * 100;
};

const fetchDashboardData = async () => {
    try {
        const response = await axios.get('http://localhost:8000/api/user/dashboard', {
            headers: { Authorization: `Bearer ${authStore.token}` }
        });
        
        const data = response.data;
        currentYear.value = data.year;
        stats.value = data.stats;

        // Ekstrak Balances
        const balances = data.balances;
        const annualData = balances.find((b: any) => b.leave_type.name === 'Annual Leave');
        const sickData = balances.find((b: any) => b.leave_type.name === 'Sick Leave');

        if (annualData) {
            annual.value = {
                total: annualData.total_quota,
                used: annualData.used,
                sisa: annualData.total_quota - annualData.used
            };
        }
        
        if (sickData) {
            sick.value = {
                total: sickData.total_quota,
                used: sickData.used,
                sisa: sickData.total_quota - sickData.used
            };
        }
    } catch (error) {
        console.error("Gagal memuat dashboard user", error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    if (!authStore.token) authStore.init();
    fetchDashboardData();
});
</script>

<style scoped>
/* Core Layout */
.app-layout { display: flex; min-height: 100vh; background-color: #f8fafc; font-family: sans-serif; }
.main-wrapper { flex: 1; margin-left: 260px; display: flex; flex-direction: column; }
.navbar-container { width: 100%; display: flex; justify-content: space-between; align-items: center; max-width: 80rem; margin: 0 auto; }
.content-container { max-width: 80rem; margin: 0 auto; width: 100%; }
.top-navbar { background: white; border-bottom: 1px solid #edf2f7; height: 64px; display: flex; align-items: center; padding: 0 2rem; position: sticky; top: 0; z-index: 20; }
.content-area { padding: 2rem; }

/* Utilities */
.flex { display: flex; }
.items-center { align-items: center; }
.justify-between { justify-content: space-between; }
.justify-center { justify-content: center; }
.gap-2 { gap: 0.5rem; }
.mb-1 { margin-bottom: 0.25rem; }
.mb-2 { margin-bottom: 0.5rem; }
.mb-4 { margin-bottom: 1rem; }
.mb-6 { margin-bottom: 1.5rem; }
.mr-3 { margin-right: 0.75rem; }
.py-12 { padding-top: 3rem; padding-bottom: 3rem; }

/* Typography */
.text-xs { font-size: 0.75rem; }
.text-sm { font-size: 0.875rem; }
.text-xl { font-size: 1.25rem; }
.text-2xl { font-size: 1.5rem; }
.text-3xl { font-size: 1.875rem; }
.font-medium { font-weight: 500; }
.font-bold { font-weight: 700; }
.text-gray-400 { color: #9ca3af; }
.text-gray-500 { color: #6b7280; }
.text-gray-900 { color: #111827; }
.text-indigo-500 { color: #6366f1; }
.text-emerald-500 { color: #10b981; }
.text-orange-500 { color: #f97316; }
.text-red-500 { color: #ef4444; }

/* Header */
.page-title { font-size: 1.5rem; font-weight: 800; color: #1e293b; margin-bottom: 0.25rem; margin-top: 0; }
.page-description { font-size: 0.875rem; color: #64748b; margin-top: 0; }

/* Toggle */
.role-toggle { display: flex; align-items: center; }
.toggle-bg { background-color: #f3f4f6; padding: 0.25rem; border-radius: 0.5rem; display: flex; gap: 0.25rem; }
.toggle-btn { padding: 0.375rem 1rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; border: none; background: transparent; color: #6b7280; transition: all 0.2s; }
.toggle-btn.active { background-color: #4f46e5; color: white; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); }

/* Quota Progress Cards */
.quota-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; }
.quota-card { background: white; border: 1px solid #f1f5f9; padding: 1.5rem; border-radius: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.quota-title { font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0; }

.progress-container { width: 100%; background-color: #f1f5f9; border-radius: 999px; height: 8px; overflow: hidden; }
.progress-bar { height: 100%; border-radius: 999px; transition: width 0.8s ease-in-out; }
.bg-indigo-500 { background-color: #4f46e5; }
.bg-emerald-500 { background-color: #10b981; }

/* Stats Request Cards */
.stats-request-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
.stat-box { background: white; border: 1px solid #f1f5f9; padding: 1.5rem; border-radius: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); display: flex; flex-direction: column; }
</style>