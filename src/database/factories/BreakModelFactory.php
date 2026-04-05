<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\BreakModel;

class BreakModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'attendance_id' => 1,
            'sequence' => 1,
            'break_start_at' => $this->faker->dateTimeBetween('12:00', '12:05')->format('H:i'),
            'break_end_at' => $this->faker->dateTimeBetween('12:55', '13:00')->format('H:i'),
        ];
    }
}
