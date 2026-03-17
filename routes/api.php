<?php

use App\Http\Controllers\Api\Admin\LeaveRequestController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\User\DashboardController;
use App\Http\Controllers\Api\User\LeaveRequestController as UserLeaveRequestController;
use App\Models\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Endpoint publik (tidak perlu token)
Route::post('/login', [AuthController::class, 'login']);

// Endpoint yang dilindungi (wajib menyertakan token Bearer)
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);

    // Endpoint untuk mendapatkan data user yang sedang login (opsional tapi berguna untuk frontend)
    Route::get('/user', function (Request $request) {
        return response()->json([
            'message' => 'Data user berhasil diambil',
            'user' => $request->user(),
        ]);
    });

    Route::get('/leave-types', function () {
        return response()->json([
            'data' => LeaveType::all()
        ]);
    });

    Route::prefix('user')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index']);
        // Endpoint Pengajuan Cuti User
        Route::get('/leave-requests', [UserLeaveRequestController::class, 'index']);
        Route::post('/leave-requests', [UserLeaveRequestController::class, 'store']);
        Route::patch('/leave-requests/{id}/cancel', [UserLeaveRequestController::class, 'cancel']);
        Route::delete('/leave-requests/{id}', [UserLeaveRequestController::class, 'destroy']);
    });

    Route::prefix('admin')->group(function () {
        // Rute baru untuk dashboard admin
        Route::get('/leave-requests', [LeaveRequestController::class, 'index']);
        Route::post('/leave-requests/{id}/approve', [LeaveRequestController::class, 'approve']);
        Route::post('/leave-requests/{id}/reject', [LeaveRequestController::class, 'reject']);
        Route::delete('/leave-requests/{id}', [LeaveRequestController::class, 'destroy']);

        // --- RUTE KELOLA USER (MANUAL, TANPA apiResource) ---
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
        Route::post('/users/generate-balances', [UserController::class, 'generateBalances']);

        Route::get('/leave-types', function () {
            return response()->json(['data' => LeaveType::all()]);
        });
    });
});

Route::get('/', function () {
    return view('welcome');
});
