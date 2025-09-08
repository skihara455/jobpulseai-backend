<?php

namespace Database\Factories;

use App\Models\Job;
use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobFactory extends Factory
{
    protected $model = Job::class;

    public function definition(): array
    {
        $types = ['full-time', 'part-time', 'contract', 'internship', 'remote'];
        $tagsPool = ['Laravel','Vue','SQL','API','Docker','Kubernetes','Python','React','Node','Testing'];

        $min = $this->faker->numberBetween(30000, 120000);
        $max = $min + $this->faker->numberBetween(10000, 80000);

        return [
            // If not provided, these will create fresh records; DatabaseSeeder will override.
            'employer_id' => User::factory(),
            'company_id'  => Company::factory(),

            'title'       => $this->faker->jobTitle(),
            'location'    => $this->faker->randomElement(['Nairobi', 'Remote', 'Mombasa', 'Kisumu']),
            'type'        => $this->faker->randomElement($types),

            'salary_min'  => $min,
            'salary_max'  => $max,

            'tags'        => implode(',', $this->faker->randomElements($tagsPool, rand(3, 5))),
            'description' => $this->faker->paragraphs(3, true),

            'status'      => 'open', // default to open for demo
        ];
    }
}
