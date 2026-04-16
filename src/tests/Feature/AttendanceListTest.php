<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Database\Seeders\WorkPatternsTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\AttendancesTableSeeder;
use Database\Seeders\BreaksTableSeeder;
use Carbon\Carbon;

use App\Models\Attendance;
use App\Models\User;
use App\Models\BreakModel;
use App\Models\AttendanceAdjustment;
use App\Models\BreakAdjustment;

class AttendanceListTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_attendance_list_data() // 一覧に表示されているデータテスト
    {
        $this->seed(WorkPatternsTableSeeder::class);
        $this->seed(UsersTableSeeder::class);
        $this->seed(AttendancesTableSeeder::class);
        $this->seed(BreaksTableSeeder::class);


        // ログイン情報
        $email = 'general@example.com';
        $password = 'password';

        // ログイン
        $response = $this->post('/login', [
            'email' => $email,
            'password' => $password,
        ]);

        // 対象データを抽出
        $user_id = auth()->id();
        $date = Carbon::today();
        $start_date = $date->copy()->startOfMonth();
        $end_date = $date->copy()->endOfMonth();

        $query = Attendance::where('user_id', $user_id)
            ->whereBetween('work_date', [$start_date->toDateString(), $end_date->toDateString()])
            ->with('breaks')
            ->get();

        # 休憩時間の取得
        $attendances = $query->map(function ($a) {
            $break_total_minutes = $a->breaks->sum(function ($b) {
                if (!$b->break_start_at || !$b->break_end_at) return 0;
                return $b->break_end_at->diffInMinutes($b->break_start_at);
            });

            # 休憩時間の算出
            $break_hours = intdiv($break_total_minutes, 60);
            $break_minutes = $break_total_minutes % 60;
            $a->break_hm = sprintf('%d:%02d', $break_hours, $break_minutes);

            if (!$a->clock_in_at || !$a->clock_out_at) {
                $a->work_minutes = 0;
                return $a;
            }


            # 勤務合計時間の算出
            $work_total_minutes = $a->clock_out_at->diffInMinutes($a->clock_in_at);
            $a->work_time = max(0, $work_total_minutes - $break_total_minutes);

            $work_hours = intdiv($a->work_time, 60);
            $work_minutes = $a->work_time % 60;
            $a->work_hm = sprintf('%d:%02d', $work_hours, $work_minutes);

            return $a;
        });

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        // 取得したデータが一覧に表示されているか確認
        foreach ($attendances as $attendance){
            $date = $attendance->work_date->format('m/d');
            $clock_in = $attendance->clock_in_at->format('H:i');
            $clock_out = $attendance->clock_out_at->format('H:i');
            $break_hm = $attendance->break_hm;
            $work_hm = $attendance->work_hm;

            $response->assertSeeInOrder([$date, $clock_in, $clock_out, $break_hm, $work_hm]);
        }
    }


    public function test_attendance_list_month() // 一覧画面に遷移した際の年月テスト
    {
        $this->seed(WorkPatternsTableSeeder::class);
        $this->seed(UsersTableSeeder::class);

        // ログイン情報
        $email = 'general@example.com';
        $password = 'password';

        // ログイン
        $response = $this->post('/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        // 現在の年月が表示されているか確認
        $month = Carbon::today()->format('Y/m');
        $response->assertSee($month);
    }


    public function test_attendance_list_last_month() // 前月へ移行時の年月テスト
    {
        $this->seed(WorkPatternsTableSeeder::class);
        $this->seed(UsersTableSeeder::class);

        // ログイン情報
        $email = 'general@example.com';
        $password = 'password';

        // ログイン
        $response = $this->post('/login', [
            'email' => $email,
            'password' => $password,
        ]);

        // 一覧画面へ遷移
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        $month = Carbon::today();
        $last_month = $month->subMonth()->format('Y/m');

        // 前月へ以降
        $response = $this->post('/attendance/list', [
                'month' => $last_month
            ]);

        // 前月の年月が表示されている事を確認
        $response->assertSee($last_month);
    }


    public function test_attendance_list_next_month() // 翌月へ移行時の年月テスト
    {
        $this->seed(WorkPatternsTableSeeder::class);
        $this->seed(UsersTableSeeder::class);

        // ログイン情報
        $email = 'general@example.com';
        $password = 'password';

        // ログイン
        $response = $this->post('/login', [
            'email' => $email,
            'password' => $password,
        ]);

        // 一覧画面へ遷移
        $response = $this->get('/attendance/list');
        $response->assertStatus(200);

        // 現在の年月が表示されているか確認
        $month = Carbon::today();
        $next_month = $month->addMonth()->format('Y/m');

        // 翌月へ以降
        $response = $this->post('/attendance/list', [
                'month' => $next_month
            ]);

        // 翌月の年月が表示されている事を確認
        $response->assertSee($next_month);
    }


    
}
