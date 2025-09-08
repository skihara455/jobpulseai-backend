<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin',    'description' => 'Platform administrator'],
            ['name' => 'employer', 'description' => 'Company or recruiter posting jobs'],
            ['name' => 'seeker',   'description' => 'Job seeker / candidate'],
            ['name' => 'mentor',   'description' => 'Mentor providing guidance'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
