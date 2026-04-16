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
use App\Models\Attendance;

class AttendanceDetailTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_attendance_detail_name() // 勤怠詳細画面の名前テスト
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

        // ログインユーザーの勤怠情報を1件ランダムで取得
        $user_id = auth()->id();

        $attendance = Attendance::query()
            ->where('user_id', $user_id)
            ->inRandomOrder()
            ->first();

        $user_name = auth()->user()->name;

        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);
        $response->assertSee([$user_name]);

    }


    public function test_attendance_detail_date() // 勤怠詳細画面の日付テスト
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

        // ログインユーザーの勤怠情報を1件ランダムで取得
        $user_id = auth()->id();

        $attendance = Attendance::query()
            ->where('user_id', $user_id)
            ->inRandomOrder()
            ->first();

        $year = $attendance->work_date->format('Y年');
        $date = $attendance->work_date->format('n月j日');
        // $clock_in = $attendance->clock_in_at->format('H:i');
        // $clock_out = $attendance->clock_out_at->format('H:i');

        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);
        $response->assertSeeInOrder([$year, $date]);
    }


    public function test_attendance_detail_clock() // 勤怠詳細画面の出退勤時刻テスト
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

        // ログインユーザーの勤怠情報を1件ランダムで取得
        $user_id = auth()->id();

        $attendance = Attendance::query()
            ->where('user_id', $user_id)
            ->inRandomOrder()
            ->first();

        $clock_in = $attendance->clock_in_at->format('H:i');
        $clock_out = $attendance->clock_out_at->format('H:i');

        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);
        $response->assertSeeInOrder([$clock_in, $clock_out]);
    }


    public function test_attendance_detail_break() // 勤怠詳細画面の休憩時刻テスト
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

        // ログインユーザーの勤怠情報を1件ランダムで取得
        $user_id = auth()->id();

        $attendance = Attendance::query()
            ->with('breaks')
            ->where('user_id', $user_id)
            ->inRandomOrder()
            ->first();

        $response = $this->get(route('attendance.detail', ['id' => $attendance->id]));
        $response->assertStatus(200);

        foreach($attendance->breaks as $break){
            $break_start = $break->break_start_at->format('H:i');
            $break_end = $break->break_end_at->format('H:i');

            $response->assertSeeInOrder([$break_start, $break_end]);
        }
    }
}
