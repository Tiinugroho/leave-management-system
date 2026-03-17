import { createRouter, createWebHistory } from 'vue-router';
import LoginView from '../views/LoginView.vue';
import { useAuthStore } from '../stores/authStore';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', redirect: '/dashboard' },
    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: { requiresGuest: true }
    },
    {
      path: '/dashboard',
      name: 'dashboard',
      component: () => import('../views/DashboardView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/kelola-user',
      name: 'kelola_user',
      component: () => import('../views/ManageUsersView.vue'),
      meta: { requiresAuth: true }
    },
    {
      path: '/user/dashboard',
      name: 'UserDashboard',
      component: () => import('../views/UserDashboardView.vue'),
      // Tambahkan meta auth
    },
    {
        path: '/user/ajukan-cuti',
        name: 'AjukanCuti',
        component: () => import('../views/UserLeaveRequestView.vue'),
        meta: { requiresAuth: true, role: 'user' }
    },
    {
        path: '/user/riwayat-cuti',
        name: 'RiwayatCuti',
        component: () => import('../views/UserLeaveHistoryView.vue'),
        meta: { requiresAuth: true, role: 'user' }
    },
  ]
});

router.beforeEach((to, from) => {
  const authStore = useAuthStore();
  const isAuthenticated = !!authStore.token;

  if (to.meta.requiresAuth && !isAuthenticated) {
    return { name: 'login' }; 
  }

  if (to.meta.requiresGuest && isAuthenticated) {
    return { name: 'dashboard' }; 
  }

});

export default router;