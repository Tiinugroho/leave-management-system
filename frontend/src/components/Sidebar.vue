<template>
  <aside class="sidebar">
    <div class="sidebar-top">
      <div class="sidebar-brand">
        <div class="brand-text">
          <h2>Leave<span>Hub</span></h2>
          <p class="brand-subtitle">Leave Request Management</p>
        </div>
      </div>

      <nav class="sidebar-nav">
        <template v-if="authStore.user?.role === 'admin'">
          <div class="nav-section-label">ADMIN MENU</div>
          
          <router-link to="/dashboard" class="nav-link">
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <span>Leave Requests</span>
          </router-link>

          <router-link to="/kelola-user" class="nav-link">
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <span>Kelola User</span>
          </router-link>
        </template>

        <template v-else>
          <div class="nav-section-label">USER MENU</div>
          
          <router-link to="/user/dashboard" class="nav-link">
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <span>Sisa Kuota</span>
          </router-link>

          <router-link to="/user/ajukan-cuti" class="nav-link">
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
            </svg>
            <span>Ajukan Cuti</span>
          </router-link>

          <router-link to="/user/riwayat-cuti" class="nav-link">
            <svg xmlns="http://www.w3.org/2000/svg" class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
            </svg>
            <span>Riwayat Cuti</span>
          </router-link>
        </template>
      </nav>
    </div>

    <div class="sidebar-footer">
      <div class="user-card">
        <div class="user-avatar" :class="authStore.user?.role === 'admin' ? 'bg-indigo' : 'bg-emerald'">
          {{ authStore.user?.name?.charAt(0)?.toUpperCase() || 'U' }}
        </div>
        <div class="user-details">
          <span class="user-name">{{ authStore.user?.name || 'User' }}</span>
          <span class="user-email">{{ authStore.user?.email || 'user@email.com' }}</span>
        </div>
        <button @click="showConfirmLogout = true" class="logout-btn" title="Logout">
          <svg xmlns="http://www.w3.org/2000/svg" class="logout-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-1.414 1.414a5 5 0 11-9.899 0L5.636 5.636a7 7 0 1012.728 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v9" />
          </svg>
        </button>
      </div>
    </div>

    <Modal :isOpen="showConfirmLogout" title="Konfirmasi Logout" @close="showConfirmLogout = false">
      <div class="text-center py-4">
        <h4 class="confirm-title">Siap untuk keluar?</h4>
        <p class="confirm-text">Apakah Anda yakin ingin keluar dari aplikasi LeaveHub?</p>
      </div>
      <template #footer>
        <button @click="showConfirmLogout = false" class="btn-cancel">Batal</button>
        <button @click="handleLogout" class="btn-confirm-logout">Ya, Logout</button>
      </template>
    </Modal>
  </aside>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useAuthStore } from '../stores/authStore';
import { useRouter } from 'vue-router';
import Modal from './Modal.vue';

const authStore = useAuthStore();
const router = useRouter();
const showConfirmLogout = ref(false);

onMounted(() => {
  if (!authStore.user) {
    authStore.init();
  }
});

const handleLogout = async () => {
  showConfirmLogout.value = false;
  await authStore.logout();
  router.push({ path: '/login', query: { logged_out: '1' } });
};
</script>

<style scoped>
/* TEMA GELAP (DARK THEME) SESUAI MOCKUP */
.sidebar {
  width: 260px;
  background-color: #1e1e2d; /* Warna background dark navy */
  height: 100vh;
  position: fixed;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  z-index: 50;
  box-shadow: 2px 0 10px rgba(0,0,0,0.1);
}

/* Branding */
.sidebar-brand {
  padding: 1.5rem 1.5rem 2rem 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.brand-text h2 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 800;
  color: #ffffff;
  letter-spacing: -0.5px;
}

.brand-text span {
  color: #6366f1; /* Warna ungu hub */
}

.brand-subtitle {
  color: #9ca3af;
  font-size: 0.75rem;
  margin: 2px 0 0 0;
}

/* Navigasi */
.sidebar-nav {
  padding: 0 1rem;
}

.nav-section-label {
  font-size: 0.65rem;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #6b7280;
  font-weight: 700;
  padding: 1rem 0.75rem 0.5rem;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  color: #9ca3af; /* Warna abu-abu redup untuk link tidak aktif */
  text-decoration: none;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  transition: all 0.2s ease;
  margin-bottom: 0.25rem;
}

.nav-link:hover {
  color: #ffffff;
  background-color: rgba(255, 255, 255, 0.05);
}

.nav-icon {
  width: 1.25rem;
  height: 1.25rem;
  color: #9ca3af;
  transition: color 0.2s;
}

/* State Aktif (Ungu menyala dengan teks putih) */
.router-link-active {
  background-color: #4f46e5 !important;
  color: #ffffff !important;
}

.router-link-active .nav-icon {
  color: #ffffff !important;
}

/* Footer Profile */
.sidebar-footer {
  padding: 1.25rem 1rem;
  background-color: #171723; /* Sedikit lebih gelap dari body sidebar */
  border-top: 1px solid #2b2b40;
}

.user-card {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-avatar {
  width: 36px;
  height: 36px;
  color: #ffffff;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 0.875rem;
}

.bg-indigo { background-color: #4f46e5; }
.bg-emerald { background-color: #10b981; }

.user-details {
  display: flex;
  flex-direction: column;
  flex: 1;
  min-width: 0;
}

.user-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: #ffffff;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-email {
  font-size: 0.7rem;
  color: #9ca3af;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Tombol Power */
.logout-btn {
  background: none;
  border: none;
  color: #6b7280;
  cursor: pointer;
  padding: 0.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: color 0.2s;
}

.logout-btn:hover {
  color: #ef4444;
}

.logout-icon {
  width: 1.25rem;
  height: 1.25rem;
}

/* Modal Styling */
.confirm-title { font-size: 1.1rem; color: #111827; font-weight: 600; margin: 0 0 0.5rem 0; }
.confirm-text { color: #6b7280; font-size: 0.9rem; margin: 0; }
.btn-confirm-logout { background-color: #ef4444; color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; border: none; cursor: pointer; transition: 0.2s; }
.btn-confirm-logout:hover { background-color: #dc2626; }
.btn-cancel { background-color: white; color: #374151; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.875rem; font-weight: 600; border: 1px solid #d1d5db; cursor: pointer; transition: 0.2s; }
.btn-cancel:hover { background-color: #f3f4f6; }
.text-center { text-align: center; }
.py-4 { padding-top: 1rem; padding-bottom: 1rem; }
</style>