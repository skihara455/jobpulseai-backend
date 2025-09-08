<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition(): array
    {
        // default to seeker unless overridden
        $seekerRoleId = Role::where('name', 'seeker')->value('id');

        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('Password123!'), // known seed password
            'remember_token' => Str::random(10),
            'role_id' => $seekerRoleId,
            'avatar_path' => null,
            'avatar_url'  => null,
            'resume_path' => null,
            'resume_url'  => null,
        ];
    }
}
