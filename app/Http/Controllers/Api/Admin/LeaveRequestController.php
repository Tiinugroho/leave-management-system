<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveRequestController extends Controller
{
    public function index()
    {
        $requests = LeaveRequest::with(['user', 'leaveType'])
            ->orderBy('created_at', 'desc')
            ->get();

        $pendingRequests = $requests->where('status', 'pending')->values();
        $historyRequests = $requests->whereIn('status', ['approved', 'rejected', 'cancelled'])->values();

        return response()->json([
            'stats' => [
                'pending' => $pendingRequests->count(),
                'approved' => $requests->where('status', 'approved')->count(),
                'rejected' => $requests->where('status', 'rejected')->count(),
            ],
            'pending_requests' => $pendingRequests,
            'history_requests' => $historyRequests,
        ]);
    }

    public function approve(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        if ($leaveRequest->status !== 'pending') {
            return response()->json(['message' => 'Hanya request pending yang bisa disetujui.'], 422);
        }

        try {
            DB::transaction(function () use ($leaveRequest, $request) {
                // 1. Ubah status menjadi approved
                $leaveRequest->update([
                    'status' => 'approved',
                    'admin_notes' => $request->admin_notes,
                    'responded_by' => auth()->id(),
                    'responded_at' => now(),
                ]);

                // 2. Sesuai ERD: Tambah jumlah cuti ke kolom 'used' di tabel leave_balances
                $leaveYear = date('Y', strtotime($leaveRequest->start_date));
                
                $balance = LeaveBalance::where('user_id', $leaveRequest->user_id)
                    ->where('leave_type_id', $leaveRequest->leave_type_id)
                    ->where('year', $leaveYear)
                    ->first();

                if ($balance) {
                    $balance->increment('used', $leaveRequest->total_days);
                }
            });

            return response()->json(['message' => 'Cuti disetujui dan kuota terpakai (used) telah ditambahkan.']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memproses persetujuan cuti.'], 500);
        }
    }

    public function reject(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        if ($leaveRequest->status !== 'pending') {
            return response()->json(['message' => 'Hanya request pending yang bisa ditolak.'], 422);
        }

        $request->validate(['admin_notes' => 'required|string']);

        // Ubah status menjadi rejected (Saldo/Balance TIDAK BERUBAH)
        $leaveRequest->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
            'responded_by' => auth()->id(),
            'responded_at' => now(),
        ]);

        return response()->json(['message' => 'Cuti berhasil ditolak.']);
    }

    public function destroy($id)
    {
        $leaveRequest = LeaveRequest::findOrFail($id);

        if ($leaveRequest->status === 'pending') {
            return response()->json(['message' => 'Request yang masih pending tidak bisa dihapus.'], 422);
        }

        // Sesuai ERD: Catat siapa yang menghapus sebelum di-soft-delete
        $leaveRequest->update(['deleted_by' => auth()->id()]);
        $leaveRequest->delete();

        return response()->json(['message' => 'Riwayat berhasil disembunyikan (Soft Delete).']);
    }
}