<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan urutannya benar. LeaveType dulu, baru User.
        $this->call([
            LeaveTypeSeeder::class,
            UserSeeder::class,
        ]);
    }
}