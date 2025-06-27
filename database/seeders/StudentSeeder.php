<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Student User',
            'email' => 'student@student.com',
            'password' => Hash::make('password'),
            'role' => UserRole::STUDENT,
            'status' => 'approved',
            'email_verified_at' => now(),
        ]);
    }
} 