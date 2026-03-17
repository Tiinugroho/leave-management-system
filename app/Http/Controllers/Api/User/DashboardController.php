<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $currentYear = date('Y');

        // 1. Ambil Saldo Cuti (Annual & Sick)
        $balances = LeaveBalance::with('leaveType')
            ->where('user_id', $user->id)
            ->where('year', $currentYear)
            ->get();

        // 2. Hitung Statistik Pengajuan Cuti (Pending, Approved, Rejected)
        $requests = LeaveRequest::where('user_id', $user->id)->get();

        $stats = [
            'pending' => $requests->where('status', 'pending')->count(),
            'approved' => $requests->where('status', 'approved')->count(),
            'rejected' => $requests->where('status', 'rejected')->count(),
        ];

        return response()->json([
            'year' => $currentYear,
            'balances' => $balances,
            'stats' => $stats
        ]);
    }
}