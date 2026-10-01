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
            'token' => $this->faker->sha256(),
            'refresh_token' => $this->faker->sha256(),
            'expiry_date' => $this->faker->dateTime()->add(new \DateInterval('P1H'))->getTimestamp(),
        ];
    }
}
