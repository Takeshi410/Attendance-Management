<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Database\Seeders\WorkPatternsTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Carbon\Carbon;
use App\Models\Attendance;


class ClockOutTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_clock_out() // 退勤ボタンテスト
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

        $today = Carbon::now()->format('Y-m-d');
        $user_id = auth()->id();

       // 出勤データを作成
        $attendance = Attendance::Create([
            'work_date' => $today,
            'user_id' => $user_id,
            'clock_in_at' => '09:00:00',
        ]);

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        // 退勤ボタンが表示されている事を確認
        $response->assertSee('<button type="submit" class="btn">退勤</button>', false);

        // 退勤登録
        $response = $this->patch('/attendance/clock-out', [
            'attendance_id' => $attendance->id,
        ])->assertRedirect('/attendance');

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        // ステータスが退勤済になっている事を確認
        $response->assertSee('退勤済');
    }


    public function test_clock_out_list() // 勤怠一覧の退勤時間テスト
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

        $today = Carbon::now()->format('Y-m-d');
        $user_id = auth()->id();

       // 出勤データを作成
        $attendance = Attendance::Create([
            'work_date' => $today,
            'user_id' => $user_id,
            'clock_in_at' => '09:00:00',
        ]);

        Carbon::setTestNow($today . ' 18:00:00');

        // 退勤登録
        $response = $this->patch('/attendance/clock-out', [
            'attendance_id' => $attendance->id,
        ])->assertRedirect('/attendance');

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        $date = $attendance->work_date->format('m/d');

        // 対象日付後に退勤時間が表示されている事を確認
        $response->assertSeeInOrder([$date, '18:00']);

        Carbon::setTestNow();

    }
}
