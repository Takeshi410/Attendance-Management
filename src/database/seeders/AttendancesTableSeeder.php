<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\CarbonPeriod;
use App\Models\User;
use App\Models\Attendance;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = now();

        $period = CarbonPeriod::create(
            $now->copy()->subMonths(2)->startOfMonth(),
            $now->copy()->subDay()
        );

        $userIds = User::where('is_admin', false)->pluck('id');
        $dates = collect($period);

        $skipDays = [0, 6];

        foreach ($userIds as $userId) {
            foreach ($period as $date) {

                if (in_array($date->dayOfWeek, $skipDays, true)) {
                    continue;
                }

                Attendance::factory()->create([
                    'user_id' => $userId,
                    'work_date' => $date->toDateString(),
                ]);
            }
        }

    }
}