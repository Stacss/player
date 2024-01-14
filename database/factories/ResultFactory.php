<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\Result;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Result>
 */
class ResultFactory extends Factory
{
    protected $model = Result::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'member_id' => $this->faker->boolean(10) ? $this->faker->numberBetween(1, 10000) : Member::factory(),
            'milliseconds' => $this->faker->numberBetween(10, 10000),
        ];
    }
}
