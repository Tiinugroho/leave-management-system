<template>
    <div class="app-layout">
        <Sidebar />

        <div class="main-wrapper">
            <header class="top-navbar">
                <div class="navbar-container">
                    <Breadcrumb :items="['User Menu', 'Riwayat Cuti']" />
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
                        <h2 class="page-title">Riwayat Cuti Saya</h2>
                        <p class="page-description">Semua pengajuan cuti yang pernah disubmit.</p>
                    </div>

                    <div class="table-card">
                        <div class="table-header border-b">
                            <h3 class="table-title">Riwayat Pengajuan</h3>
                            <span class="text-sm text-gray-500">{{ leaveRequests.length }} request</span>
                        </div>
                        <div class="table-wrapper">
                            <DataTable :columns="columns" :data="leaveRequests">
                                <template #tipe="{ item }">
                                    <span class="font-medium text-gray-900">{{ item.leave_type?.name }}</span>
                                </template>
                                <template #tanggal="{ item }">
                                    <span class="text-gray-700">{{ formatDate(item.start_date) }} - {{ formatDate(item.end_date) }}</span>
                                </template>
                                <template #hari="{ item }">
                                    <span class="text-gray-700">{{ item.total_days }}</span>
                                </template>
                                <template #alasan="{ item }">
                                    <span class="text-gray-600 truncate-text" :title="item.reason">{{ item.reason }}</span>
                                </template>
                                <template #status="{ item }">
                                    <span :class="getStatusBadgeClass(item.status)">{{ item.status }}</span>
                                </template>
                                <template #catatan_admin="{ item }">
                                    <span class="text-gray-500">{{ item.admin_notes || '—' }}</span>
                                </template>
                                <template #aksi="{ item }">
                                    <button v-if="item.status === 'pending'" @click="openCancelModal(item)" class="btn-cancel-request">
                                        Cancel
                                    </button>
                                    <button v-else-if="item.status === 'rejected' || item.status === 'canceled'" @click="openDeleteModal(item)" class="btn-text-danger">
                                        Hapus
                                    </button>
                                    <span v-else class="text-gray-400">—</span>
                                </template>
                            </DataTable>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <Modal :isOpen="showCancelModal" title="Cancel Request?" @close="showCancelModal = false" :showFooter="false">
            <div class="cancel-modal-body">
                <p class="font-medium text-gray-900 mb-1">
                    {{ selectedRequest?.leave_type?.name }} <span class="font-normal text-gray-600 ml-2">{{ formatDate(selectedRequest?.start_date) }} - {{ formatDate(selectedRequest?.end_date) }} ({{ selectedRequest?.total_days }} hari)</span>
                </p>
                <p class="text-sm text-gray-600 mb-4">
                    Status saat ini: <span class="badge bg-orange-100 text-orange-800">pending</span>
                </p>
                <p class="text-sm text-red-600 font-medium mb-6">
                    Request yang sudah di-cancel tidak bisa dikembalikan.
                </p>

                <div class="flex gap-3">
                    <button @click="executeCancel" class="btn-action bg-red-600 text-white" :disabled="isProcessing">
                        {{ isProcessing ? 'Memproses...' : 'Ya, Cancel' }}
                    </button>
                    <button @click="showCancelModal = false" class="btn-action bg-white text-gray-700 border-gray" :disabled="isProcessing">
                        Batal
                    </button>
                </div>
            </div>
        </Modal>

        <Modal :isOpen="showDeleteModal" title="Hapus Riwayat?" @close="showDeleteModal = false">
            <p class="text-sm text-gray-600">Yakin ingin menghapus catatan riwayat cuti ini dari daftar Anda?</p>
            <template #footer>
                <button @click="showDeleteModal = false" class="btn-cancel-default">Batal</button>
                <button @click="executeDelete" class="btn-action bg-red-600 text-white">Ya, Hapus</button>
            </template>
        </Modal>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/authStore';
import Sidebar from '../components/Sidebar.vue';
import Breadcrumb from '../components/Breadcrumb.vue';
import DataTable from '../components/DataTable.vue';
import Modal from '../components/Modal.vue';

const authStore = useAuthStore();
const leaveRequests = ref<any[]>([]);
const isProcessing = ref(false);

const showCancelModal = ref(false);
const showDeleteModal = ref(false);
const selectedRequest = ref<any>(null);

const columns = [
    { key: 'tipe', label: 'TIPE' },
    { key: 'tanggal', label: 'TANGGAL' },
    { key: 'hari', label: 'HARI' },
    { key: 'alasan', label: 'ALASAN' },
    { key: 'status', label: 'STATUS' },
    { key: 'catatan_admin', label: 'CATATAN ADMIN' },
    { key: 'aksi', label: 'AKSI' },
];

const getHeaders = () => ({ headers: { Authorization: `Bearer ${authStore.token}` } });

const fetchData = async () => {
    try {
        const res = await axios.get('http://localhost:8000/api/user/leave-requests', getHeaders());
        leaveRequests.value = res.data.data || [];
    } catch (e) { console.error(e); }
};

const formatDate = (date: string) => date ? new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) : '-';

const getStatusBadgeClass = (status: string) => {
    if (status === 'approved') return 'badge bg-green-100 text-green-800';
    if (status === 'rejected' || status === 'canceled') return 'badge bg-red-100 text-red-800';
    return 'badge bg-orange-100 text-orange-800';
};

// ACTIONS
const openCancelModal = (item: any) => { selectedRequest.value = item; showCancelModal.value = true; };
const openDeleteModal = (item: any) => { selectedRequest.value = item; showDeleteModal.value = true; };

const executeCancel = async () => {
    isProcessing.value = true;
    try {
        await axios.patch(`http://localhost:8000/api/user/leave-requests/${selectedRequest.value.id}/cancel`, {}, getHeaders());
        showCancelModal.value = false;
        fetchData();
    } catch (e) { alert("Gagal membatalkan request."); }
    finally { isProcessing.value = false; }
};

const executeDelete = async () => {
    isProcessing.value = true;
    try {
        await axios.delete(`http://localhost:8000/api/user/leave-requests/${selectedRequest.value.id}`, getHeaders());
        showDeleteModal.value = false;
        fetchData();
    } catch (e) { alert("Gagal menghapus riwayat."); }
    finally { isProcessing.value = false; }
};

onMounted(() => {
    authStore.init();
    fetchData();
});
</script>

<style scoped>
/* Layout & Basics (Sama seperti sebelumnya) */
.app-layout { display: flex; min-height: 100vh; background-color: #f8fafc; font-family: sans-serif; }
.main-wrapper { flex: 1; margin-left: 260px; display: flex; flex-direction: column; }
.navbar-container { width: 100%; display: flex; justify-content: space-between; align-items: center; padding: 0 2rem;}
.top-navbar { background: white; border-bottom: 1px solid #edf2f7; height: 64px; display: flex; align-items: center; position: sticky; top: 0; z-index: 20; }
.content-area { padding: 2rem; }

.page-title { font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0 0 0.25rem 0; }
.page-description { font-size: 0.875rem; color: #64748b; margin: 0; }
.role-toggle { display: flex; align-items: center; }
.toggle-bg { background-color: #f3f4f6; padding: 0.25rem; border-radius: 0.5rem; display: flex; gap: 0.25rem; }
.toggle-btn { padding: 0.375rem 1rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; border: none; background: transparent; color: #6b7280; }
.toggle-btn.active { background-color: #4f46e5; color: white; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); }

/* Table Styling */
.table-card { background: white; border-radius: 12px; border: 1px solid #f1f5f9; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.table-header { padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; }
.table-title { font-size: 1.125rem; font-weight: 700; color: #334155; margin: 0; }

.badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 700; text-transform: capitalize; }
.bg-green-100 { background-color: #dcfce7; } .text-green-800 { color: #166534; }
.bg-red-100 { background-color: #fee2e2; } .text-red-800 { color: #991b1b; }
.bg-orange-100 { background-color: #ffedd5; } .text-orange-800 { color: #9a3412; }

.truncate-text { display: inline-block; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* Buttons */
.btn-cancel-request { background-color: #ef4444; color: white; padding: 0.4rem 1rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600; border: none; cursor: pointer; }
.btn-cancel-request:hover { background-color: #dc2626; }
.btn-text-danger { background: none; border: none; color: #ef4444; font-size: 0.75rem; font-weight: 600; cursor: pointer; text-decoration: underline; }
.btn-action { padding: 0.5rem 1.25rem; border-radius: 6px; font-weight: 600; font-size: 0.875rem; border: none; cursor: pointer; }
.border-gray { border: 1px solid #d1d5db; }
.btn-cancel-default { background: white; border: 1px solid #d1d5db; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer; }
</style>