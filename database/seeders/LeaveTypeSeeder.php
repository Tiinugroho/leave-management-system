<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaveTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        
        DB::table('leave_types')->insert([
            [
                'name' => 'Annual Leave',
                'default_quota' => 12,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Sick Leave',
                'default_quota' => 6,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
