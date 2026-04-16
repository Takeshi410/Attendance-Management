<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Attendance;
use App\Models\AttendanceAdjustment;

class AttendanceAdjustmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $today = Carbon::today();
        $start_date = $today->copy()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $end_date   = $today->copy()->subMonthNoOverflow()->endOfMonth()->toDateString();

        // 検証ユーザー用の修正依頼データ（承認済）
        $attendance_ids = Attendance::query()
            ->whereBetween('work_date', [$start_date, $end_date])
            ->where('user_id', 2)
            ->inRandomOrder()
            ->limit(3)
            ->pluck('id');

        foreach ($attendance_ids as $attendance_id) {
            AttendanceAdjustment::create([
                'attendance_id' => $attendance_id,
                'after_clock_in_at' => '08:00:00',
                'after_clock_out_at' => '19:00:00',
                'remarks' => '勤怠修正申請（Seeder）',
                'is_approval' => true,
                'is_admin' => false,
            ]);

            Attendance::find($attendance_id)->update([
                'clock_in_at' => '08:00:00',
                'clock_out_at' => '19:00:00',
            ]);
        }

        // 検証ユーザー用の修正依頼データ（未承認）
        $next_attendance_ids = Attendance::query()
            ->whereBetween('work_date', [$start_date, $end_date])
            ->where('user_id', 2)
            ->whereNotIn('id', $attendance_ids)
            ->inRandomOrder()
            ->limit(3)
            ->pluck('id');

        foreach ($next_attendance_ids as $attendance_id) {
            AttendanceAdjustment::create([
                'attendance_id' => $attendance_id,
                'after_clock_in_at' => '08:00:00',
                'after_clock_out_at' => '19:00:00',
                'remarks' => '勤怠修正申請（Seeder）',
                'is_approval' => false,
                'is_admin' => false,
            ]);
        }


        // 検証用ユーザー以外の修正依頼（承認済）
        $attendance_ids = Attendance::query()
            ->whereBetween('work_date', [$start_date, $end_date])
            ->where('user_id', '!=', 2)
            ->inRandomOrder()
            ->limit(6)
            ->pluck('id');

        foreach ($attendance_ids as $attendance_id) {
            AttendanceAdjustment::create([
                'attendance_id' => $attendance_id,
                'after_clock_in_at' => '08:00:00',
                'after_clock_out_at' => '19:00:00',
                'remarks' => '勤怠修正申請（Seeder）',
                'is_approval' => true,
                'is_admin' => false,
            ]);

            Attendance::find($attendance_id)->update([
                'clock_in_at' => '08:00:00',
                'clock_out_at' => '19:00:00',
            ]);
        }

        // 検証用ユーザー以外の修正依頼（未承認）
        $next_attendance_ids = Attendance::query()
            ->whereBetween('work_date', [$start_date, $end_date])
            ->where('user_id', '!=', 2)
            ->whereNotIn('id', $attendance_ids)
            ->inRandomOrder()
            ->limit(4)
            ->pluck('id');

        foreach ($next_attendance_ids as $attendance_id) {
            AttendanceAdjustment::create([
                'attendance_id' => $attendance_id,
                'after_clock_in_at' => '08:00:00',
                'after_clock_out_at' => '19:00:00',
                'remarks' => '勤怠修正申請（Seeder）',
                'is_approval' => false,
                'is_admin' => false,
            ]);
        }
    }
}
