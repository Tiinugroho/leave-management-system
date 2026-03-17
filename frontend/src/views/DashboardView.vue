<template>
    <div class="app-layout">
        <Sidebar />

        <div class="main-wrapper">
            <header class="top-navbar">
                <div class="navbar-container">
                    <Breadcrumb :items="['Dashboard', 'Leave Request']" />
                    <div class="role-toggle">
                        <span class="text-sm font-medium text-gray-500 mr-3">View as:</span>
                        <div class="toggle-bg">
                            <button class="toggle-btn active">Admin</button>
                            <button class="toggle-btn" disabled title="Beralih ke tampilan User">User</button>
                        </div>
                    </div>
                </div>
            </header>

            <main class="content-area">
                <div class="content-container">
                    <div class="page-header">
                        <h2 class="page-title">Leave Request</h2>
                        <p class="page-description">
                            Kelola dan respons permohonan pengajuan cuti dari seluruh karyawan.
                        </p>
                    </div>

                    <Loader v-if="isLoading" text="Memuat data request cuti..." />
                    
                    <div v-else-if="generalError" class="alert-error">
                        {{ generalError }}
                    </div>

                    <template v-else>
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-content">
                                    <dt class="stat-label">Pending Approval</dt>
                                    <dd class="stat-value text-yellow-600">{{ stats.pending }}</dd>
                                </div>
                                <div class="stat-icon-wrapper bg-yellow-100 text-yellow-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-content">
                                    <dt class="stat-label">Approved Requests</dt>
                                    <dd class="stat-value text-green-600">{{ stats.approved }}</dd>
                                </div>
                                <div class="stat-icon-wrapper bg-green-100 text-green-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>

                            <div class="stat-card">
                                <div class="stat-content">
                                    <dt class="stat-label">Rejected Requests</dt>
                                    <dd class="stat-value text-red-600">{{ stats.rejected }}</dd>
                                </div>
                                <div class="stat-icon-wrapper bg-red-100 text-red-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="icon-svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="table-card">
                            <div class="table-header">
                                <h3 class="table-title">Perlu Tindakan</h3>
                                <span v-if="stats.pending > 0" class="badge bg-yellow-100 text-yellow-800">
                                    {{ stats.pending }} pending
                                </span>
                            </div>
                            <div class="table-wrapper">
                                <DataTable :columns="actionColumns" :data="pendingRequests">
                                    <template #user="{ item }">
                                        <span class="font-medium text-gray-900">{{ item.user?.name }}</span>
                                    </template>
                                    <template #tipe="{ item }">
                                        <span class="text-gray-600">{{ item.leave_type?.name }}</span>
                                    </template>
                                    <template #tanggal="{ item }">
                                        <span class="text-gray-600">{{ formatDate(item.start_date) }} - {{ formatDate(item.end_date) }}</span>
                                    </template>
                                    <template #total_days="{ item }">
                                        <span class="text-gray-600">{{ item.total_days }} Hari</span>
                                    </template>
                                    <template #reason="{ item }">
                                        <span class="text-gray-500 text-sm truncate-text" :title="item.reason">{{ item.reason }}</span>
                                    </template>
                                    <template #status>
                                        <span class="status-badge bg-yellow-100 text-yellow-800">Pending</span>
                                    </template>
                                    <template #aksi="{ item }">
                                        <div class="flex gap-2">
                                            <Button text="Approve" variant="primary" @click="openApproveModal(item)" />
                                            <Button text="Reject" variant="secondary" @click="openRejectModal(item)" />
                                        </div>
                                    </template>
                                </DataTable>
                            </div>
                        </div>

                        <div class="table-card mt-8">
                            <div class="table-header border-b border-gray-200">
                                <h3 class="table-title">Riwayat Semua Request</h3>
                            </div>
                            <div class="table-wrapper">
                                <DataTable :columns="historyColumns" :data="historyRequests">
                                    <template #user="{ item }">
                                        <span class="font-medium text-gray-900">{{ item.user?.name }}</span>
                                    </template>
                                    <template #tipe="{ item }">
                                        <span class="text-gray-600">{{ item.leave_type?.name }}</span>
                                    </template>
                                    <template #tanggal="{ item }">
                                        <span class="text-gray-600">{{ formatDate(item.start_date) }}</span>
                                    </template>
                                    <template #total_days="{ item }">
                                        <span class="text-gray-600">{{ item.total_days }} hr</span>
                                    </template>
                                    <template #direspon="{ item }">
                                        <span class="text-gray-500 text-sm">{{ item.responded_at ? formatDate(item.responded_at) : "—" }}</span>
                                    </template>
                                    <template #catatan="{ item }">
                                        <span class="text-gray-500 text-sm truncate-text block" :title="item.admin_notes">{{ item.admin_notes || "—" }}</span>
                                    </template>
                                    <template #status="{ item }">
                                        <span v-if="item.status === 'approved'" class="status-badge bg-green-100 text-green-800">Approved</span>
                                        <span v-else-if="item.status === 'rejected'" class="status-badge bg-red-100 text-red-800">Rejected</span>
                                        <span v-else class="status-badge bg-gray-100 text-gray-800">Cancelled</span>
                                    </template>
                                    <template #aksi="{ item }">
<Button text="Hapus" variant="text-danger" size="sm" @click="openDeleteModal(item)" />
                                    </template>
                                </DataTable>
                            </div>
                        </div>
                    </template>
                </div>
            </main>
        </div>

        <Modal :isOpen="showWelcomeModal" @close="showWelcomeModal = false" :showFooter="false">
            <div class="welcome-modal-content flex flex-col items-center justify-center p-4">
                <div class="icon-success-circle mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="welcome-heading text-xl font-bold text-gray-900 mb-2">Login Berhasil</h3>
                <p class="welcome-text text-center text-gray-600 mb-6">
                    Selamat datang kembali, <strong class="text-gray-900">{{ authStore.user?.name }}</strong>. Anda sekarang berada di halaman Dashboard Admin.
                </p>
                <Button text="Lanjutkan" variant="primary" @click="showWelcomeModal = false" />
            </div>
        </Modal>

        <Modal :isOpen="showApproveModal" title="Setujui Cuti" @close="showApproveModal = false" :showFooter="false">
            <form @submit.prevent="submitApprove">
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-3">
                        Anda akan menyetujui cuti <strong>{{ selectedRequest?.user?.name }}</strong> selama <strong>{{ selectedRequest?.total_days }} hari</strong>.
                        <br><span class="text-yellow-600 font-medium">Catatan: Saldo cuti karyawan akan otomatis terpotong. Aksi ini bersifat final.</span>
                    </p>
                    
                    <Label text="Catatan Admin (Opsional)" />
                    <textarea v-model="adminNotes" class="form-textarea" rows="3" placeholder="Tambahkan pesan untuk karyawan..."></textarea>
                </div>
                <div class="modal-footer-custom">
                    <Button text="Batal" variant="secondary" @click="showApproveModal = false" :disabled="isProcessing" />
                    <Button text="Ya, Setujui" variant="primary" type="submit" :isLoading="isProcessing" />
                </div>
            </form>
        </Modal>

        <Modal :isOpen="showRejectModal" title="Tolak Cuti" @close="showRejectModal = false" :showFooter="false">
            <form @submit.prevent="submitReject">
                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-3">
                        Anda akan menolak permohonan cuti dari <strong>{{ selectedRequest?.user?.name }}</strong>. Saldo cuti karyawan tidak akan dipotong.
                    </p>
                    
                    <Label text="Alasan Penolakan (Wajib)" required />
                    <textarea v-model="adminNotes" class="form-textarea" rows="3" placeholder="Beri tahu alasan penolakan..." required></textarea>
                </div>
                <div class="modal-footer-custom">
                    <Button text="Batal" variant="secondary" @click="showRejectModal = false" :disabled="isProcessing" />
                    <Button text="Tolak Request" variant="danger" type="submit" :isLoading="isProcessing" :disabled="!adminNotes.trim()" />
                </div>
            </form>
        </Modal>

        <Modal :isOpen="showDeleteModal" title="Hapus Riwayat" @close="showDeleteModal = false" :showFooter="false">
            <div class="mb-4">
                <p class="text-sm text-gray-600">
                    Apakah Anda yakin ingin menghapus riwayat cuti ini dari daftar? Data akan disembunyikan (Soft Delete) namun tetap tersimpan di database.
                </p>
            </div>
            <div class="modal-footer-custom">
                <Button text="Batal" variant="secondary" @click="showDeleteModal = false" :disabled="isProcessing" />
                <Button text="Ya, Hapus" variant="danger" @click="submitDelete" :isLoading="isProcessing" />
            </div>
        </Modal>

    </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from "vue";
import { useAuthStore } from "../stores/authStore";
import { leaveRequestService } from "../services/leaveRequestService";
import Sidebar from "../components/Sidebar.vue";
import Breadcrumb from "../components/Breadcrumb.vue";
import DataTable from "../components/DataTable.vue";
import Modal from "../components/Modal.vue";
import Button from "../components/Button.vue";
import Label from "../components/Label.vue";
import Loader from "../components/Loader.vue";

const authStore = useAuthStore();
const isLoading = ref(true);
const isProcessing = ref(false);
const generalError = ref("");

// Modals State
const showWelcomeModal = ref(false);
const showApproveModal = ref(false);
const showRejectModal = ref(false);
const showDeleteModal = ref(false);
const selectedRequest = ref<any>(null);
const adminNotes = ref('');

// Data State
const stats = ref({ pending: 0, approved: 0, rejected: 0 });
const pendingRequests = ref([]);
const historyRequests = ref([]);

// Table Columns
const actionColumns = [
    { key: "user", label: "Karyawan" },
    { key: "tipe", label: "Tipe Cuti" },
    { key: "tanggal", label: "Tanggal" },
    { key: "total_days", label: "Durasi" },
    { key: "reason", label: "Alasan" },
    { key: "status", label: "Status" },
    { key: "aksi", label: "Aksi" },
];

const historyColumns = [
    { key: "user", label: "Karyawan" },
    { key: "tipe", label: "Tipe" },
    { key: "tanggal", label: "Mulai" },
    { key: "total_days", label: "Durasi" },
    { key: "status", label: "Status" },
    { key: "direspon", label: "Direspon" },
    { key: "catatan", label: "Catatan Admin" },
    { key: "aksi", label: "Aksi" },
];

const formatDate = (dateString: string) => {
    if (!dateString) return "";
    return new Date(dateString).toLocaleDateString("id-ID", { day: "numeric", month: "short", year: "numeric" });
};

// Fetch Data
const fetchRequests = async () => {
    isLoading.value = true;
    generalError.value = "";
    
    try {
        const response = await leaveRequestService.getAllAdminRequests();
        if (response.data) {
            stats.value = response.data.stats || { pending: 0, approved: 0, rejected: 0 };
            pendingRequests.value = response.data.pending_requests || [];
            historyRequests.value = response.data.history_requests || [];
        }
    } catch (error) {
        generalError.value = "Backend belum merespons atau terjadi kesalahan.";
        console.warn("Error fetching admin requests:", error);
    } finally {
        isLoading.value = false;
    }
};

// Actions
const openApproveModal = (item: any) => { selectedRequest.value = item; adminNotes.value = ''; showApproveModal.value = true; };
const openRejectModal = (item: any) => { selectedRequest.value = item; adminNotes.value = ''; showRejectModal.value = true; };
const openDeleteModal = (item: any) => { selectedRequest.value = item; showDeleteModal.value = true; };

const submitApprove = async () => {
    if (!selectedRequest.value) return;
    isProcessing.value = true;
    try {
        await leaveRequestService.approveRequest(selectedRequest.value.id, { admin_notes: adminNotes.value });
        showApproveModal.value = false;
        fetchRequests();
    } catch (error) {
        alert('Gagal menyetujui request. Pastikan backend berjalan.');
    } finally {
        isProcessing.value = false;
    }
};

const submitReject = async () => {
    if (!selectedRequest.value) return;
    isProcessing.value = true;
    try {
        await leaveRequestService.rejectRequest(selectedRequest.value.id, { admin_notes: adminNotes.value });
        showRejectModal.value = false;
        fetchRequests();
    } catch (error) {
        alert('Gagal menolak request.');
    } finally {
        isProcessing.value = false;
    }
};

const submitDelete = async () => {
    if (!selectedRequest.value) return;
    isProcessing.value = true;
    try {
        await leaveRequestService.deleteRequest(selectedRequest.value.id);
        showDeleteModal.value = false;
        fetchRequests();
    } catch (error) {
        alert('Gagal menghapus riwayat.');
    } finally {
        isProcessing.value = false;
    }
};

onMounted(() => {
    if (!authStore.user) authStore.init();

    // Welcome Modal Logic
    if (sessionStorage.getItem('isNewLogin') === 'yes') {
        showWelcomeModal.value = true;
        sessionStorage.removeItem('isNewLogin');
    }

    fetchRequests();
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

/* Flex & Typography Utilities */
.flex { display: flex; }
.flex-col { flex-direction: column; }
.items-center { align-items: center; }
.justify-between { justify-content: space-between; }
.justify-center { justify-content: center; }
.gap-2 { gap: 0.5rem; }
.mb-2 { margin-bottom: 0.5rem; }
.mb-3 { margin-bottom: 0.75rem; }
.mb-4 { margin-bottom: 1rem; }
.mt-8 { margin-top: 2rem; }
.p-4 { padding: 1rem; }
.text-center { text-align: center; }
.text-sm { font-size: 0.875rem; }
.text-xl { font-size: 1.25rem; }
.font-medium { font-weight: 500; }
.font-bold { font-weight: 700; }
.block { display: block; }
.max-w-xs { max-width: 20rem; }

/* Colors */
.text-gray-500 { color: #6b7280; }
.text-gray-600 { color: #4b5563; }
.text-gray-900 { color: #111827; }
.text-yellow-600 { color: #d97706; }
.text-green-600 { color: #16a34a; }
.text-red-600 { color: #dc2626; }
.bg-yellow-100 { background-color: #fef3c7; }
.bg-green-100 { background-color: #dcfce7; }
.bg-red-100 { background-color: #fee2e2; }
.bg-gray-100 { background-color: #f3f4f6; }
.text-yellow-800 { color: #92400e; }
.text-green-800 { color: #166534; }
.text-red-800 { color: #991b1b; }
.text-gray-800 { color: #1f2937; }

/* Header & Toggle */
.page-title { font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0 0 0.25rem 0; }
.page-description { font-size: 0.875rem; color: #64748b; margin: 0 0 2rem 0; }
.role-toggle { display: flex; align-items: center; }
.toggle-bg { background-color: #f3f4f6; padding: 0.25rem; border-radius: 0.5rem; display: flex; gap: 0.25rem; }
.toggle-btn { padding: 0.375rem 1rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; border: none; background: transparent; color: #6b7280; }
.toggle-btn.active { background-color: #ffffff; color: #111827; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }

/* Stats Grid */
.stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
.stat-card { background: white; border: 1px solid #f1f5f9; padding: 1.5rem; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.stat-label { font-size: 0.875rem; color: #64748b; font-weight: 500; margin-bottom: 0.25rem; }
.stat-value { font-size: 1.875rem; font-weight: 800; margin: 0; }
.stat-icon-wrapper { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; }
.icon-svg { width: 1.5rem; height: 1.5rem; }

/* Table Components */
.table-card { background: white; border-radius: 12px; border: 1px solid #f1f5f9; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.table-header { padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
.table-title { font-size: 1.125rem; font-weight: 700; color: #334155; margin: 0; }
.border-b { border-bottom: 1px solid #e5e7eb; }

/* Badges & Texts */
.badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; display: inline-flex; }
.status-badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; display: inline-flex; }
.truncate-text { display: inline-block; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* Form Elements */
.form-textarea { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem; font-family: inherit; outline: none; transition: border-color 0.2s; resize: vertical; }
.form-textarea:focus { border-color: #4f46e5; box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1); }
.modal-footer-custom { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb; }
.alert-error { padding: 1rem; background-color: #fef2f2; color: #991b1b; border: 1px solid #f87171; border-radius: 0.5rem; text-align: center; }

/* Welcome Modal Specifics */
.icon-success-circle { background-color: #dcfce7; border-radius: 50%; padding: 1rem; display: inline-flex; }
</style>