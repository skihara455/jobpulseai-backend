<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // Helper to fetch role IDs by name
        $adminId    = Role::where('name', 'admin')->value('id');
        $employerId = Role::where('name', 'employer')->value('id');
        $seekerId   = Role::where('name', 'seeker')->value('id');

        // Admin
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name'     => 'Admin User',
                'password' => Hash::make('Password1.'),
                'role_id'  => $adminId,
            ]
        );

        // Employer
        User::firstOrCreate(
            ['email' => 'employer@example.com'],
            [
                'name'     => 'Employer User',
                'password' => Hash::make('Password1.'),
                'role_id'  => $employerId,
            ]
        );

        // Seeker
        User::firstOrCreate(
            ['email' => 'seeker@example.com'],
            [
                'name'     => 'Seeker User',
                'password' => Hash::make('Password1.'),
                'role_id'  => $seekerId,
            ]
        );
    }
}

