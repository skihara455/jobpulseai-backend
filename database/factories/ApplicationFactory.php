<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition(): array
    {
        return [
            'job_id'  => Job::query()->inRandomOrder()->value('id'),
            'user_id' => User::whereHas('role', fn($q)=>$q->where('name','seeker'))->inRandomOrder()->value('id'),
            'cover_letter' => $this->faker->sentences(2, true),
            'status' => 'pending',
        ];
    }
}
