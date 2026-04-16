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
use App\Models\BreakModel;

class AttendanceStatusTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_attendance() // 出勤前ステータステスト
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

        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }


    public function test_clock_in() // 出勤後ステータステスト
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
        Attendance::Create([
            'work_date' => $today,
            'user_id' => $user_id,
            'clock_in_at' => '09:00:00',
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }


    public function test_break() // 休憩後ステータステスト
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

        // 休憩データを作成
        BreakModel::Create([
            'attendance_id' => $attendance->id,
            'sequence' => 1,
            'break_start_at' => '12:00:00',
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('休憩中');
    }


    public function test_clock_out() // 退勤後ステータステスト
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

        // 退勤済みデータを作成
        Attendance::Create([
            'work_date' => $today,
            'user_id' => $user_id,
            'clock_in_at' => '09:00:00',
            'clock_out_at' => '18:00:00',
        ]);

        $response = $this->get('/attendance');
        $response->assertSee('退勤済');
    }
}
