<?php

namespace Database\Factories;

use App\Models\Airline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Airline>
 */
class AirlineFactory extends Factory
{
    protected $model = Airline::class; // only needed if guessing fails
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            //
        ];
    }
}
