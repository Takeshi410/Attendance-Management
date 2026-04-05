<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\WorkPattern;

class WorkPatternsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
        'pattern_name' => '通常勤務',
        'start_time' => '08:00:00',
        'end_time' => '17:00:00',
        'break_minutes' => 60,
        ];

        DB::table('work_patterns')->insert($param);
    }
}
