<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\User;
use App\Models\Company;
use App\Models\Job;
use App\Models\Mentor;
use App\Models\Application;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Ensure roles exist
        $roles = [
            ['name' => 'admin',    'description' => 'Platform administrator'],
            ['name' => 'employer', 'description' => 'Company or recruiter posting jobs'],
            ['name' => 'seeker',   'description' => 'Job seeker / candidate'],
            ['name' => 'mentor',   'description' => 'Mentor providing guidance'],
        ];
        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r['name']], $r);
        }

        // Grab role IDs for later use
        $roleIds = Role::pluck('id', 'name');

        // 2) Create known users (with fixed logins for testing)
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Password123!'),
                'role_id' => $roleIds['admin'] ?? null,
            ]
        );

        $employer = User::firstOrCreate(
            ['email' => 'employer@example.com'],
            [
                'name' => 'Employer One',
                'password' => Hash::make('Password123!'),
                'role_id' => $roleIds['employer'] ?? null,
            ]
        );

        $seeker = User::firstOrCreate(
            ['email' => 'seeker@example.com'],
            [
                'name' => 'Seeker One',
                'password' => Hash::make('Password123!'),
                'role_id' => $roleIds['seeker'] ?? null,
            ]
        );

        // 3) Extra seekers
        User::factory()->count(5)->create(['role_id' => $roleIds['seeker'] ?? null]);

        // 4) Company for the employer
        $company = Company::firstOrCreate(
            ['name' => 'JobPulseAI Inc.'],
            [
                'owner_id' => $employer->id,
                'website' => 'https://example.com',
                'location' => 'Nairobi',
                'industry' => 'Tech',
                'size' => '11-50',
                'description' => 'AI-powered job matching at scale.',
                'logo_url' => 'https://picsum.photos/seed/jobpulseai/200/200',
            ]
        );

        // 5) Mentors
        Mentor::factory()->count(6)->create();

        // 6) Jobs: a mix of random + tied to employer/company
        Job::factory()->count(8)->create(); // random jobs
        Job::factory()->count(3)->create([
            'employer_id' => $employer->id,
            'company_id'  => $company->id,
            'status'      => 'open',
        ]);

        // 7) Applications: seeker applies to first few open jobs
        $openJobs = Job::where('status', 'open')->take(3)->get();
        foreach ($openJobs as $oj) {
            Application::firstOrCreate(
                ['job_id' => $oj->id, 'user_id' => $seeker->id],
                [
                    'cover_letter' => 'Excited to apply to this role!',
                    'status' => 'pending',
                ]
            );
        }
    }
}
