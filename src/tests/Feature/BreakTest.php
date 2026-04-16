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

class BreakTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_break_start() // 休憩入テスト
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

        // 休憩入ボタンが表示されていることを確認
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('<button type="submit" class="btn btn--break">休憩入</button>', false);

        // 休憩スタート
        $response = $this->post('/attendance/break-start', [
            'attendance_id' => $attendance->id,
        ])->assertRedirect('/attendance');

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        // ステータスが休憩中になっている事を確認
        $response->assertSee('休憩中');
    }


    public function test_break_start_multiple() // 休憩入再実行可能テスト
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

        // 休憩スタート
        $response = $this->post('/attendance/break-start', [
            'attendance_id' => $attendance->id,
        ])->assertRedirect('/attendance');

        $break = BreakModel::where('attendance_id', $attendance->id)
            ->latest('id')
            ->firstOrFail();

        // 休憩戻り
        $response = $this->patch('/attendance/break-end', [
            'attendance_id' => $attendance->id,
            'break_id' => $break->id,
        ])->assertRedirect('/attendance');

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        // 休憩入ボタンが表示されている事を確認
        $response->assertSee('<button type="submit" class="btn btn--break">休憩入</button>', false);
    }


    public function test_break_end() // 休憩戻りテスト
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

        // 休憩スタート
        $response = $this->post('/attendance/break-start', [
            'attendance_id' => $attendance->id,
        ])->assertRedirect('/attendance');

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        // 休憩戻ボタンが表示されている事を確認
        $response->assertSee('<button type="submit" class="btn btn--break">休憩戻</button>', false);

        $break = BreakModel::where('attendance_id', $attendance->id)
            ->latest('id')
            ->firstOrFail();

        // 休憩戻り
        $response = $this->patch('/attendance/break-end', [
            'attendance_id' => $attendance->id,
            'break_id' => $break->id,
        ])->assertRedirect('/attendance');

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        // ステータスが出勤中になっている事を確認
        $response->assertSee('出勤中');
    }


    public function test_break_end_multiple() // 休憩戻再実行可能テスト
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

        // 休憩スタート
        $response = $this->post('/attendance/break-start', [
            'attendance_id' => $attendance->id,
        ])->assertRedirect('/attendance');

        $break = BreakModel::where('attendance_id', $attendance->id)
            ->latest('id')
            ->firstOrFail();

        // 休憩戻り
        $response = $this->patch('/attendance/break-end', [
            'attendance_id' => $attendance->id,
            'break_id' => $break->id,
        ])->assertRedirect('/attendance');


        // 休憩再スタート
        $response = $this->post('/attendance/break-start', [
            'attendance_id' => $attendance->id,
        ])->assertRedirect('/attendance');

        $response = $this->get('/attendance');
        $response->assertStatus(200);

        // 休憩戻ボタンが表示されている事を確認
        $response->assertSee('<button type="submit" class="btn btn--break">休憩戻</button>', false);
    }


    public function test_break_list() // 勤怠一覧の休憩時間テスト
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

        Carbon::setTestNow($today . ' 12:00:00');

        // 休憩スタート
        $response = $this->post('/attendance/break-start', [
            'attendance_id' => $attendance->id,
        ])->assertRedirect('/attendance');

        $break = BreakModel::where('attendance_id', $attendance->id)
            ->latest('id')
            ->firstOrFail();

        Carbon::setTestNow($today . ' 12:55:00');

        // 休憩戻り
        $response = $this->patch('/attendance/break-end', [
            'attendance_id' => $attendance->id,
            'break_id' => $break->id,
        ])->assertRedirect('/attendance');

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        $date = $attendance->work_date->format('m/d');

        // 対象日付後に休憩時間が表示されている事を確認
        $response->assertSeeInOrder([$date, '0:55']);

        Carbon::setTestNow();

    }
}
