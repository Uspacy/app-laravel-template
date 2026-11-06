<?php

namespace Database\Factories;

use App\Models\Portal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Portal>
 */
class PortalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'domain' => $this->faker->domainName(),
            'integration_code' => $this->faker->slug(2),
            'token' => $this->faker->sha256(),
            'refresh_token' => $this->faker->sha256(),
            'expiry_date' => now()->addHour(),
        ];
    }
}
