<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CollegeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        User::create([
            'name' => 'Company User',
            'email' => 'college@college.com',
            'password' => Hash::make('password'),
            'role' => UserRole::COLLEGE,
            'status' => 'approved',
            'email_verified_at' => now(),
        ]);
    }
}
