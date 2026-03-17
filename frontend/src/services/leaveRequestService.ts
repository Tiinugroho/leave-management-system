import api from './api';

export const leaveRequestService = {
  // --- ENDPOINT UNTUK ADMIN ---
  getAllAdminRequests() { return api.get('/admin/leave-requests'); },
  approveRequest(id: number, data: { admin_notes: string }) { return api.post(`/admin/leave-requests/${id}/approve`, data); },
  rejectRequest(id: number, data: { admin_notes: string }) { return api.post(`/admin/leave-requests/${id}/reject`, data); },
  deleteRequest(id: number) { return api.delete(`/admin/leave-requests/${id}`); },

  // --- ENDPOINT UNTUK USER ---
  // Mengambil data dashboard user (saldo, stats, dan riwayat)
  getUserDashboard() {
    return api.get('/user/leave-requests/dashboard');
  },
  
  // Mengajukan cuti baru
  submitRequest(data: { leave_type_id: number, start_date: string, end_date: string, reason: string }) {
    return api.post('/user/leave-requests', data);
  },

  // Membatalkan cuti (hanya jika pending)
  cancelRequest(id: number) {
    return api.post(`/user/leave-requests/${id}/cancel`);
  }
};