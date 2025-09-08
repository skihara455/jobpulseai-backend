<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'owner_id'     => User::factory(), // creates a user (usually employer role) if not supplied
            'name'         => $this->faker->company(),
            'website'      => $this->faker->optional()->url(),
            'location'     => $this->faker->city(),
            'industry'     => $this->faker->randomElement([
                'Technology', 'Finance', 'Healthcare', 'Education', 'Retail', 'Manufacturing',
            ]),
            'size'         => $this->faker->randomElement(['1-10', '11-50', '51-200', '201-500', '500+']),
            'description'  => $this->faker->paragraph(),
            'logo_path'    => null,
            'logo_url'     => $this->faker->optional()->imageUrl(200, 200, 'business', true, 'logo'),
            'linkedin_url' => $this->faker->optional()->url(),
            'twitter_url'  => $this->faker->optional()->url(),
        ];
    }
}
