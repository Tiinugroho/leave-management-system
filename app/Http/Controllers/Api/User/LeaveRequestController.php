<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    // Menampilkan riwayat pengajuan cuti milik user yang sedang login
    public function index(Request $request)
{
    // 👇 PASTIKAN ADA with('leaveType') DI SINI 👇
    $requests = LeaveRequest::with('leaveType')
        ->where('user_id', $request->user()->id)
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json(['data' => $requests]);
}

    // Memproses form pengajuan cuti baru
    public function store(Request $request)
    {
        // 1. Validasi Input Dasar
        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date'    => 'required|date|after_or_equal:today',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'required|string|max:1000',
            'attachment'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048', // Max 2MB
        ]);

        $user = $request->user();
        $leaveType = LeaveType::findOrFail($request->leave_type_id);

        // 2. Validasi Khusus: Sick Leave Wajib Lampiran
        if (str_contains(strtolower($leaveType->name), 'sick') && !$request->hasFile('attachment')) {
            return response()->json([
                'message' => 'Surat Keterangan Dokter (Lampiran) wajib diunggah untuk Sick Leave.'
            ], 422);
        }

        // 3. Kalkulasi Durasi Hari Cuti (Inklusif)
        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $requestedDays = $start->diffInDays($end) + 1;

        // 4. Validasi Sisa Saldo Cuti
        $balance = LeaveBalance::where('user_id', $user->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('year', date('Y'))
            ->first();

        if (!$balance) {
            return response()->json(['message' => 'Anda tidak memiliki akses/saldo untuk tipe cuti ini.'], 400);
        }

        $sisaCuti = $balance->total_quota - $balance->used;
        if ($requestedDays > $sisaCuti) {
            return response()->json([
                'message' => "Saldo cuti tidak mencukupi. Sisa Anda: $sisaCuti hari, sedangkan Anda mengajukan: $requestedDays hari."
            ], 400);
        }

        // 5. Proses Upload File (Jika Ada)
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            // Simpan ke folder storage/app/public/attachments
            $attachmentPath = $request->file('attachment')->store('attachments', 'public');
        }

        // 6. Simpan Data Pengajuan ke Database
        $leaveRequest = LeaveRequest::create([
            'user_id'       => $user->id,
            'leave_type_id' => $leaveType->id,
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'total_days'    => $requestedDays, // 👈 TAMBAHKAN BARIS INI
            'reason'        => $request->reason,
            'attachment'    => $attachmentPath,
            'status'        => 'pending',
        ]);

        return response()->json([
            'message' => 'Pengajuan cuti berhasil dikirim dan sedang menunggu persetujuan.',
            'data'    => $leaveRequest
        ], 201);
    }
    public function cancel(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Hanya bisa cancel jika statusnya masih pending
        if ($leaveRequest->status !== 'pending') {
            return response()->json(['message' => 'Hanya request pending yang dapat dibatalkan.'], 400);
        }

        $leaveRequest->update(['status' => 'canceled']);
        return response()->json(['message' => 'Pengajuan cuti berhasil dibatalkan.']);
    }

    public function destroy(Request $request, $id)
    {
        $leaveRequest = LeaveRequest::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if (!in_array($leaveRequest->status, ['rejected', 'canceled'])) {
            return response()->json(['message' => 'Hanya request yang ditolak/dibatalkan yang boleh dihapus.'], 400);
        }

        $leaveRequest->delete();
        return response()->json(['message' => 'Riwayat berhasil dihapus.']);
    }
}