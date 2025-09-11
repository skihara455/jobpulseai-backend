<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Models\Role;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            /* ---------------- 1) Roles ---------------- */
            $roles = [
                ['name' => 'user',     'description' => 'General application user'],
                ['name' => 'employer', 'description' => 'Company or recruiter posting jobs'],
            ];

            foreach ($roles as $r) {
                Role::updateOrCreate(
                    ['name' => Str::lower($r['name'])],
                    ['description' => $r['description']]
                );
            }

            $roleIds = Role::pluck('id', 'name')->mapWithKeys(fn($id, $name) => [Str::lower($name) => $id]);

            /* ---------------- 2) Users ---------------- */
            $userEmail = Str::lower('skihara479@gmail.com');
            $employerEmail = Str::lower('skihara455@gmail.com');

            User::updateOrCreate(
                ['email' => $userEmail],
                [
                    'name'     => 'Samuel Kihara (User)',
                    'password' => Hash::make('Samuel@123'),
                    'role_id'  => $roleIds['user'] ?? null,
                ]
            );

            User::updateOrCreate(
                ['email' => $employerEmail],
                [
                    'name'     => 'Samuel Kihara (Employer)',
                    'password' => Hash::make('Samuel@234'),
                    'role_id'  => $roleIds['employer'] ?? null,
                ]
            );

            /* ---------------- 3) Console hint ---------------- */
            if (app()->runningInConsole()) {
                $this->command->info('Seed complete. Test logins:');
                $this->command->line("  User:     {$userEmail} / Samuel@123");
                $this->command->line("  Employer: {$employerEmail} / Samuel@234");
            }
        });
    }
}
