<?php

namespace Database\Factories;

use App\Models\Mentor;
use Illuminate\Database\Eloquent\Factories\Factory;

class MentorFactory extends Factory
{
    protected $model = Mentor::class;

    public function definition(): array
    {
        $skills = ['CV','Interview','Negotiation','Career Switch','Tech','Design','Leadership','AI','Finance','Marketing'];

        return [
            'name'         => $this->faker->name(),
            'headline'     => $this->faker->jobTitle(),   // ✅ match migration + model
            'bio'          => $this->faker->paragraphs(2, true),
            'expertise'    => implode(',', $this->faker->randomElements($skills, 3)),
            'location'     => $this->faker->city(),
            'website'      => $this->faker->optional()->url(),
            'linkedin_url' => $this->faker->optional()->url(),
            'github_url'   => $this->faker->optional()->url(),
            'avatar_url'   => $this->faker->optional()->imageUrl(256, 256, 'people'),
        ];
    }
}
