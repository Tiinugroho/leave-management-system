<template>
    <div class="app-layout">
        <Sidebar />

        <div class="main-wrapper">
            <header class="top-navbar">
                <div class="navbar-container">
                    <div class="breadcrumb-area">
                        <Breadcrumb :items="breadcrumbItems" />
                    </div>
                    <div class="role-toggle">
                        <span class="text-sm font-medium text-gray-500 mr-3">Mode:</span>
                        <div class="toggle-bg">
                            <button class="toggle-btn active">Admin</button>
                            <button class="toggle-btn" disabled>User</button>
                        </div>
                    </div>
                </div>
            </header>

            <main class="content-area">
                <div class="content-container">
                    <div class="page-header">
                        <h2 class="page-title">Kelola User</h2>
                        <p class="page-description">Buat dan kelola akun user. Maksimal 2 user.</p>
                    </div>

                    <div class="stats-grid mb-6">
                        <div class="stat-card">
                            <div class="stat-content flex items-center gap-4">
                                <div class="stat-icon-wrapper bg-blue-50">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <dt class="stat-label">Total User</dt>
                                    <dd class="stat-value text-blue-900">{{ users.length }}</dd>
                                    <p class="text-xs text-gray-400 mt-1">Maks. 2 user</p>
                                </div>
                            </div>
                        </div>

                        <div class="stat-card">
                            <div class="stat-content flex items-center gap-4">
                                <div class="stat-icon-wrapper bg-green-50">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div>
                                    <dt class="stat-label">Slot Tersedia</dt>
                                    <dd class="stat-value text-green-900">{{ Math.max(0, 2 - users.length) }}</dd>
                                    <p class="text-xs text-gray-400 mt-1">{{ users.length >= 2 ? 'Kuota penuh' : 'Tersedia' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="generalError" class="alert-error mb-4">
                        {{ generalError }}
                    </div>

                    <div v-if="isLoading" class="flex justify-center py-12 text-gray-500">
                        Memuat data karyawan...
                    </div>

                    <div v-else class="table-card">
                        <div class="table-header border-b">
                            <h3 class="table-title">Daftar User</h3>
                            <div class="flex gap-3">
                                <button @click="showGenerateModal = true" class="btn-secondary-action">Generate Saldo</button>
                                <button @click="openAddModal" class="btn-primary-action" :disabled="users.length >= 2">
                                    <span class="mr-1">+</span> Tambah User
                                </button>
                            </div>
                        </div>
                        <div class="table-wrapper">
                            <DataTable :columns="userColumns" :data="users">
                                <template #nama="{ item }">
                                    <div class="flex items-center gap-3">
                                        <div class="avatar-sm">{{ getInitial(item.name) }}</div>
                                        <div>
                                            <div class="font-medium text-gray-900">{{ item.name }}</div>
                                            <div class="text-xs text-gray-500">{{ item.email }}</div>
                                        </div>
                                    </div>
                                </template>
                                <template #annual_leave="{ item }">
                                    <span class="text-gray-600 font-medium">{{ getAnnualText(item.leave_balances) }}</span>
                                </template>
                                <template #sick_leave="{ item }">
                                    <span class="text-gray-600 font-medium">{{ getSickText(item.leave_balances) }}</span>
                                </template>
                                <template #aksi="{ item }">
                                    <div class="flex gap-2">
                                        <button @click="openEditModal(item)" class="btn-outline-sm">Edit</button>
                                        <button @click="openPasswordModal(item)" class="btn-outline-sm">Update Password</button>
                                        <button @click="openDeleteModal(item)" class="btn-text-danger">Hapus</button>
                                    </div>
                                </template>
                            </DataTable>
                        </div>
                    </div>
                </div>
            </main>
        </div>

        <Modal :isOpen="showFormModal" :title="formModalTitle" @close="showFormModal = false" :showFooter="false">
            <form @submit.prevent="submitUser" class="form-body">
                <h4 class="section-title">Informasi Dasar</h4>
                <div class="grid-2 mb-4">
                    <div>
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" v-model="form.name" class="form-input" required>
                    </div>
                    <div>
                        <label class="form-label">Alamat Email</label>
                        <input type="email" v-model="form.email" class="form-input" required>
                    </div>
                </div>

                <div class="mb-4" v-if="!isEditing">
                    <label class="form-label">Password Sementara</label>
                    <input type="password" v-model="form.password" class="form-input" required minlength="8">
                </div>

                <h4 class="section-title mt-6">Leave Setting (Quota)</h4>
                <div class="grid-2 mb-6">
                    <div>
                        <label class="form-label">Annual Leave (Hari)</label>
                        <input type="number" v-model="form.annual_quota" class="form-input" min="0" required>
                    </div>
                    <div>
                        <label class="form-label">Sick Leave (Hari)</label>
                        <input type="number" v-model="form.sick_quota" class="form-input" min="0" required>
                    </div>
                </div>

                <div class="modal-footer-custom">
                    <button type="button" @click="showFormModal = false" class="btn-cancel" :disabled="isProcessing">Batal</button>
                    <button type="submit" class="btn-action bg-indigo-600 text-white" :disabled="isProcessing">
                        {{ saveBtnText }}
                    </button>
                </div>
            </form>
        </Modal>

        <Modal :isOpen="showPasswordModal" title="Update Password" @close="showPasswordModal = false" :showFooter="false">
            <form @submit.prevent="submitPassword" class="form-body">
                <p class="text-sm text-gray-600 mb-4">Mengubah password untuk: <strong>{{ selectedUser?.name }}</strong></p>
                <div class="mb-6">
                    <label class="form-label">Password Baru</label>
                    <input type="password" v-model="newPassword" class="form-input" required minlength="8">
                </div>
                <div class="modal-footer-custom">
                    <button type="button" @click="showPasswordModal = false" class="btn-cancel" :disabled="isProcessing">Batal</button>
                    <button type="submit" class="btn-action bg-indigo-600 text-white" :disabled="isProcessing">
                        {{ updatePassBtnText }}
                    </button>
                </div>
            </form>
        </Modal>

        <Modal :isOpen="showDeleteModal" title="Hapus User" @close="showDeleteModal = false">
            <div class="mb-4">
                <p class="text-sm text-gray-600">Apakah Anda yakin ingin menghapus user <strong>{{ selectedUser?.name }}</strong>?</p>
            </div>
            <template #footer>
                <button @click="showDeleteModal = false" class="btn-cancel" :disabled="isProcessing">Batal</button>
                <button @click="executeDelete" class="btn-action bg-red-600 text-white" :disabled="isProcessing">
                    {{ deleteBtnText }}
                </button>
            </template>
        </Modal>

        <Modal :isOpen="showGenerateModal" title="Generate Saldo Cuti" @close="showGenerateModal = false">
            <div class="mb-4">
                <p class="text-sm text-gray-600">Sistem akan membuatkan saldo cuti tahun ini secara otomatis untuk seluruh karyawan.</p>
            </div>
            <template #footer>
                <button @click="showGenerateModal = false" class="btn-cancel" :disabled="isProcessing">Batal</button>
                <button @click="executeGenerate" class="btn-action bg-gray-800 text-white" :disabled="isProcessing">
                    {{ processBtnText }}
                </button>
            </template>
        </Modal>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import axios from 'axios';
import { useAuthStore } from "../stores/authStore";
import Sidebar from "../components/Sidebar.vue";
import Breadcrumb from "../components/Breadcrumb.vue";
import DataTable from "../components/DataTable.vue";
import Modal from "../components/Modal.vue";

const authStore = useAuthStore();
const isLoading = ref(false);
const isProcessing = ref(false);
const generalError = ref("");
const users = ref<any[]>([]);

// State Modals
const showFormModal = ref(false);
const showPasswordModal = ref(false);
const showDeleteModal = ref(false);
const showGenerateModal = ref(false);

const isEditing = ref(false);
const selectedUser = ref<any>(null);
const newPassword = ref("");

const form = ref({
    id: null, name: '', email: '', password: '', role: 'user',
    annual_quota: 12, sick_quota: 6
});

// Clean Extractions (Anti Vite String Error)
const breadcrumbItems = ['Dashboard', 'Kelola User'];
const formModalTitle = computed(() => isEditing.value ? 'Edit Profil Karyawan' : 'Tambah User Baru');
const saveBtnText = computed(() => isProcessing.value ? 'Menyimpan...' : 'Simpan Data');
const updatePassBtnText = computed(() => isProcessing.value ? 'Menyimpan...' : 'Update Password');
const deleteBtnText = computed(() => isProcessing.value ? 'Menghapus...' : 'Ya, Hapus');
const processBtnText = computed(() => isProcessing.value ? 'Memproses...' : 'Proses');

const userColumns = [
    { key: "nama", label: "NAMA" },
    { key: "email", label: "EMAIL" },
    { key: "annual_leave", label: "ANNUAL LEAVE" },
    { key: "sick_leave", label: "SICK LEAVE" },
    { key: "aksi", label: "AKSI" },
];

const getHeaders = () => ({ headers: { Authorization: `Bearer ${authStore.token}` } });

// Clean Text Formatters
const getInitial = (name: string) => name ? name.charAt(0).toUpperCase() : 'U';

const getBalanceText = (balances: any[], typeName: string) => {
    if (!balances) return '0 / 0 hari';
    const found = balances.find((b: any) => b.leave_type?.name === typeName);
    if (!found) return '0 / 0 hari';
    return `${found.total_quota - found.used} / ${found.total_quota} hari`;
};

const getAnnualText = (balances: any[]) => getBalanceText(balances, 'Annual Leave');
const getSickText = (balances: any[]) => getBalanceText(balances, 'Sick Leave');

// --- API Executions ---
const fetchUsers = async () => {
    isLoading.value = true;
    try {
        const res = await axios.get('http://localhost:8000/api/admin/users', getHeaders());
        users.value = res.data.data || [];
    } catch (e) { generalError.value = "Koneksi backend bermasalah."; }
    finally { isLoading.value = false; }
};

const openAddModal = () => {
    isEditing.value = false;
    form.value = { id: null, name: '', email: '', password: '', role: 'user', annual_quota: 12, sick_quota: 6 };
    showFormModal.value = true;
};

const openEditModal = (item: any) => {
    isEditing.value = true;
    selectedUser.value = item;
    const aQuota = item.leave_balances?.find((b: any) => b.leave_type?.name === 'Annual Leave')?.total_quota || 12;
    const sQuota = item.leave_balances?.find((b: any) => b.leave_type?.name === 'Sick Leave')?.total_quota || 6;
    form.value = { id: item.id, name: item.name, email: item.email, password: '', role: item.role, annual_quota: aQuota, sick_quota: sQuota };
    showFormModal.value = true;
};

const openPasswordModal = (item: any) => {
    selectedUser.value = item;
    newPassword.value = "";
    showPasswordModal.value = true;
};

const openDeleteModal = (item: any) => {
    selectedUser.value = item;
    showDeleteModal.value = true;
};

const submitUser = async () => {
    isProcessing.value = true;
    try {
        const url = isEditing.value ? `http://localhost:8000/api/admin/users/${form.value.id}` : 'http://localhost:8000/api/admin/users';
        await axios[isEditing.value ? 'put' : 'post'](url, form.value, getHeaders());
        showFormModal.value = false;
        fetchUsers();
    } catch (e: any) { alert(e.response?.data?.message || "Gagal menyimpan."); }
    finally { isProcessing.value = false; }
};

const submitPassword = async () => {
    isProcessing.value = true;
    try {
        await axios.put(`http://localhost:8000/api/admin/users/${selectedUser.value.id}`, {
            ...selectedUser.value,
            password: newPassword.value
        }, getHeaders());
        showPasswordModal.value = false;
        alert("Password updated!");
    } catch (e) { alert("Gagal update password."); }
    finally { isProcessing.value = false; }
};

const executeDelete = async () => {
    isProcessing.value = true;
    try {
        await axios.delete(`http://localhost:8000/api/admin/users/${selectedUser.value.id}`, getHeaders());
        showDeleteModal.value = false;
        fetchUsers();
    } catch (e) { alert("Gagal hapus."); }
    finally { isProcessing.value = false; }
};

const executeGenerate = async () => {
    isProcessing.value = true;
    try {
        await axios.post('http://localhost:8000/api/admin/users/generate-balances', {}, getHeaders());
        showGenerateModal.value = false;
        fetchUsers();
    } catch (e) { alert("Gagal generate saldo."); }
    finally { isProcessing.value = false; }
};

onMounted(() => {
    if (!authStore.token) authStore.init();
    fetchUsers();
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
.gap-3 { gap: 0.75rem; }
.gap-4 { gap: 1rem; }
.mb-4 { margin-bottom: 1rem; }
.mb-6 { margin-bottom: 1.5rem; }
.mt-1 { margin-top: 0.25rem; }
.mt-6 { margin-top: 1.5rem; }
.mr-1 { margin-right: 0.25rem; }
.mr-2 { margin-right: 0.5rem; }
.mr-3 { margin-right: 0.75rem; }
.py-12 { padding-top: 3rem; padding-bottom: 3rem; }
.w-full { width: 100%; }
.w-5 { width: 1.25rem; }
.w-6 { width: 1.5rem; }
.h-5 { height: 1.25rem; }
.h-6 { height: 1.5rem; }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

/* Typography & Colors */
.text-xs { font-size: 0.75rem; }
.text-sm { font-size: 0.875rem; }
.font-medium { font-weight: 500; }
.text-gray-400 { color: #9ca3af; }
.text-gray-500 { color: #6b7280; }
.text-gray-600 { color: #4b5563; }
.text-gray-900 { color: #111827; }
.text-blue-600 { color: #2563eb; }
.text-blue-900 { color: #1e3a8a; }
.text-green-600 { color: #16a34a; }
.text-green-900 { color: #14532d; }
.text-red-600 { color: #dc2626; }
.bg-blue-50 { background-color: #eff6ff; }
.bg-green-50 { background-color: #f0fdf4; }

/* Header & Stats */
.page-title { font-size: 1.5rem; font-weight: 800; color: #1e293b; margin-bottom: 0.25rem; margin-top: 0; }
.page-description { font-size: 0.875rem; color: #64748b; margin-top: 0; margin-bottom: 2rem; }
.stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; }
.stat-card { background: white; border: 1px solid #f1f5f9; padding: 1.5rem; border-radius: 12px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.stat-icon-wrapper { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
.stat-label { font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; margin-bottom: 0.25rem;}
.stat-value { font-size: 1.75rem; font-weight: 800; color: #1e293b; margin: 0; }

/* Table */
.table-card { background: white; border-radius: 12px; border: 1px solid #f1f5f9; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
.table-header { padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; }
.table-title { font-size: 1rem; font-weight: 700; color: #334155; margin: 0;}
.border-b { border-bottom: 1px solid #e5e7eb; }

/* UI Elements */
.avatar-sm { width: 36px; height: 36px; background-color: #e0e7ff; color: #4f46e5; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.875rem; }
.badge { padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; display: inline-flex; }
.bg-indigo-100 { background-color: #e0e7ff; }
.text-indigo-800 { color: #3730a3; }

/* Toggle */
.role-toggle { display: flex; align-items: center; }
.toggle-bg { background-color: #f3f4f6; padding: 0.25rem; border-radius: 0.5rem; display: flex; gap: 0.25rem; }
.toggle-btn { padding: 0.375rem 1rem; border-radius: 0.375rem; font-size: 0.875rem; font-weight: 500; border: none; background: transparent; color: #6b7280; cursor: pointer; transition: all 0.2s; }
.toggle-btn.active { background-color: #ffffff; color: #111827; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1); }

/* Form & Modal */
.section-title { font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; margin-bottom: 1rem; border-bottom: 1px solid #f1f5f9; padding-bottom: 0.5rem; }
.form-label { display: block; font-size: 0.875rem; font-weight: 600; color: #475569; margin-bottom: 0.4rem; }
.form-input { width: 100%; padding: 0.625rem; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.875rem; box-sizing: border-box; outline: none; transition: border-color 0.2s; }
.form-input:focus { border-color: #4f46e5; box-shadow: 0 0 0 2px rgba(79, 70, 229, 0.1); }
.modal-footer-custom { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb; }

/* Buttons */
.btn-primary-action { display: flex; align-items: center; background: #4f46e5; color: white; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; border: none; cursor: pointer; transition: 0.2s; }
.btn-primary-action:hover:not(:disabled) { background: #4338ca; }
.btn-secondary-action { display: flex; align-items: center; background: white; color: #374151; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; border: 1px solid #d1d5db; cursor: pointer; transition: 0.2s; }
.btn-secondary-action:hover:not(:disabled) { background: #f9fafb; border-color: #9ca3af; }

.btn-outline-sm { border: 1px solid #e2e8f0; background: white; padding: 0.4rem 0.75rem; border-radius: 6px; font-size: 0.75rem; font-weight: 600; color: #475569; cursor: pointer; transition: 0.2s; }
.btn-outline-sm:hover { background: #f8fafc; border-color: #cbd5e1; }
.btn-text-danger { background: none; border: none; color: #ef4444; font-size: 0.75rem; font-weight: 600; cursor: pointer; padding: 0.4rem 0.75rem; }
.btn-text-danger:hover { text-decoration: underline; }

.btn-cancel { background: white; border: 1px solid #d1d5db; padding: 0.5rem 1rem; border-radius: 8px; font-weight: 600; cursor: pointer; color: #374151; }
.btn-cancel:hover:not(:disabled) { background: #f9fafb; }
.btn-action { padding: 0.5rem 1.25rem; border-radius: 8px; font-weight: 600; border: none; cursor: pointer; color: white; }
.bg-indigo-600 { background-color: #4f46e5; }
.bg-red-600 { background-color: #dc2626; }
.bg-gray-800 { background-color: #1f2937; }

.btn-action:disabled, .btn-cancel:disabled, .btn-primary-action:disabled, .btn-secondary-action:disabled { opacity: 0.6; cursor: not-allowed; }

.alert-error { padding: 1rem; background: #fef2f2; color: #991b1b; border: 1px solid #f87171; border-radius: 8px; text-align: center; font-size: 0.875rem; }
</style>