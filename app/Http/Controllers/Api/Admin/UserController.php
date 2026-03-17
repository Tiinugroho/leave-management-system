<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index()
    {
        // Memuat user beserta relasi balances dan tipenya
        $users = User::with('leaveBalances.leaveType')->orderBy('name', 'asc')->get();
        return response()->json(['data' => $users]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,user',
            'annual_quota' => 'required|integer|min:0',
            'sick_quota' => 'required|integer|min:0',
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                // 1. Buat User
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role' => $validated['role'],
                ]);

                // 2. Mapping Quota ke Leave Balances
                $this->updateOrCreateBalances($user, $validated['annual_quota'], $validated['sick_quota']);

                return response()->json([
                    'message' => 'Karyawan dan pengaturan cuti berhasil ditambahkan.',
                    'data' => $user->load('leaveBalances.leaveType')
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menambahkan karyawan: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,user',
            'password' => 'nullable|string|min:8',
            'annual_quota' => 'required|integer|min:0',
            'sick_quota' => 'required|integer|min:0',
        ]);

        try {
            return DB::transaction(function () use ($user, $validated) {
                // 1. Update Profil User
                $userData = [
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'role' => $validated['role'],
                ];

                if (!empty($validated['password'])) {
                    $userData['password'] = Hash::make($validated['password']);
                }

                $user->update($userData);

                // 2. Update Quota di tabel leave_balances
                $this->updateOrCreateBalances($user, $validated['annual_quota'], $validated['sick_quota']);

                return response()->json([
                    'message' => 'Data karyawan dan jatah cuti berhasil diperbarui.',
                    'data' => $user->load('leaveBalances.leaveType')
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui karyawan.'], 500);
        }
    }

    /**
     * Helper: Sync jatah cuti tahunan dan sakit
     */
    private function updateOrCreateBalances($user, $annualQuota, $sickQuota)
    {
        $year = date('Y');
        
        // Cari ID tipe cuti berdasarkan nama (pastikan di DB namanya persis seperti ini)
        $annualType = LeaveType::where('name', 'Annual Leave')->first();
        $sickType = LeaveType::where('name', 'Sick Leave')->first();

        if ($annualType) {
            LeaveBalance::updateOrCreate(
                ['user_id' => $user->id, 'leave_type_id' => $annualType->id, 'year' => $year],
                ['total_quota' => $annualQuota]
            );
        }

        if ($sickType) {
            LeaveBalance::updateOrCreate(
                ['user_id' => $user->id, 'leave_type_id' => $sickType->id, 'year' => $year],
                ['total_quota' => $sickQuota]
            );
        }
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return response()->json(['message' => 'Anda tidak bisa menghapus akun sendiri.'], 403);
        }

        $user->delete();
        return response()->json(['message' => 'Karyawan berhasil dihapus.']);
    }

    public function generateBalances()
    {
        $year = date('Y');
        $users = User::all();
        $leaveTypes = LeaveType::all();
        $count = 0;

        if ($leaveTypes->isEmpty()) {
            return response()->json(['message' => 'Master data Tipe Cuti kosong.'], 400);
        }

        DB::beginTransaction();
        try {
            foreach ($users as $user) {
                foreach ($leaveTypes as $type) {
                    $exists = LeaveBalance::where('user_id', $user->id)
                        ->where('leave_type_id', $type->id)
                        ->where('year', $year)
                        ->exists();

                    if (!$exists) {
                        LeaveBalance::create([
                            'user_id' => $user->id,
                            'leave_type_id' => $type->id,
                            'year' => $year,
                            'total_quota' => $type->default_quota,
                            'used' => 0,
                        ]);
                        $count++;
                    }
                }
            }
            DB::commit();
            return response()->json(['message' => "Berhasil men-generate $count data saldo baru."]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal generate saldo.'], 500);
        }
    }
}