<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceAdjustment;
use App\Models\BreakAdjustment;
use App\Models\BreakModel;

class BreakAdjustmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adjustments = AttendanceAdjustment::query()
            ->with(['attendance.breaks'])
            ->whereHas('attendance.breaks')
            ->get();

        foreach ($adjustments as $adjustment) {
            foreach ($adjustment->attendance->breaks as $break) {
                BreakAdjustment::Create(
                    [
                        'attendance_adjustment_id' => $adjustment->id,
                        'sequence' => $break->sequence,
                        'break_id' => $break->id,
                        'after_break_start_at' => '12:00:00',
                        'after_break_end_at' => '13:00:00',
                    ]
                );

                If ($adjustment->is_approval) {
                    BreakModel::find($break->id)->update([
                        'break_start_at' => '12:00:00',
                        'break_end_at' => '13:00:00',
                    ]);
                }
            }
        }
    }
}
