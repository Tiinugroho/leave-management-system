<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;

class LeaveRequestTest extends TestCase
{
    /** @test */
    public function check_api_leave_types_accessible()
    {
        // Mencari user pertama yang ada di database PostgreSQL Anda
        $user = User::first();

        if ($user) {
            $response = $this->actingAs($user, 'sanctum')->getJson('/api/leave-types');
            $response->assertStatus(200);
        } else {
            $this->markTestSkipped('Tidak ada user di database untuk testing.');
        }
    }
}