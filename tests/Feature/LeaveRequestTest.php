<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use Illuminate\Support\Facades\Hash;

class LeaveRequestTest extends TestCase
{

    /** @test */
    public function user_can_submit_leave_request()
    {
        // Gunakan email yang sangat unik agar tidak duplicate di DB asli Anda
        $email = 'test_success_' . time() . '@example.com';
        
        $user = User::create([
            'name' => 'Tester Success',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'user'
        ]);

        $leaveType = LeaveType::first() ?? LeaveType::create(['name' => 'Annual Leave']);
        
        LeaveBalance::create([
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'total_quota' => 12,
            'used' => 0,
            'year' => 2026
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/user/leave-requests', [
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-03-25',
            'end_date' => '2026-03-27',
            'reason' => 'Testing pengerjaan',
            'total_days' => 3
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function user_cannot_submit_leave_if_balance_insufficient()
    {
        $email = 'test_fail_' . time() . '@example.com';

        $user = User::create([
            'name' => 'Tester Fail',
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => 'user'
        ]);

        $leaveType = LeaveType::first() ?? LeaveType::create(['name' => 'Annual Leave']);
        
        LeaveBalance::create([
            'user_id' => $user->id,
            'leave_type_id' => $leaveType->id,
            'total_quota' => 1,
            'used' => 0,
            'year' => 2026
        ]);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/user/leave-requests', [
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-03-25',
            'end_date' => '2026-03-27',
            'reason' => 'Testing saldo kurang',
            'total_days' => 3
        ]);

        $response->assertStatus(400);
    }
}