<?php

namespace Database\Seeders;

use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Seeder;

class JobsSeeder extends Seeder
{
    public function run(): void
    {
        // Use the employer user as the job owner
        $employerId = User::where('email', 'employer@example.com')->value('id');

        if (!$employerId) {
            // If someone ran this seeder alone, just exit gracefully
            return;
        }

        Job::firstOrCreate(
            ['title' => 'Junior Laravel Developer', 'employer_id' => $employerId],
            [
                'location'    => 'Nairobi (Hybrid)',
                'type'        => 'full-time',
                'salary_min'  => 60000,
                'salary_max'  => 120000,
                'tags'        => 'php,laravel,vue,rest',
                'description' => 'Join our growing team to build modern Laravel APIs and SPAs.',
                'status'      => 'open',
            ]
        );
    }
}
