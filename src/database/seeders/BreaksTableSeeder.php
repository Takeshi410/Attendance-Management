<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\BreakModel;

class BreaksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attendance_ids = Attendance::pluck('id');

        foreach ($attendance_ids as $attendance_id) {
            BreakModel::factory()->create([
                'attendance_id' => $attendance_id,
            ]);
        }
    }
}
