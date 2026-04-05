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
        $attendanceIds = Attendance::pluck('id');

        foreach ($attendanceIds as $attendanceId) {
            BreakModel::factory()->create([
                'attendance_id' => $attendanceId,
            ]);
        }
    }
}
