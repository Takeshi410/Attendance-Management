<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;

class AttendanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => 1,
            'work_date' => $this->faker->date(),
            'clock_in_at' => $this->faker->dateTimeBetween('08:50', '09:00')->format('H:i'),
            'clock_out_at' => $this->faker->dateTimeBetween('18:00', '19:00')->format('H:i'),
        ];
    }
}
