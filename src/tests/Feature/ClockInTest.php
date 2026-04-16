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



class ClockInTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_clock_in_function() // 出勤機能テスト
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

        // 出勤ボタンが表示されていることを確認
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('<button type="submit" class="btn">出勤</button>', false);

        // 出勤登録
        $response = $this->post('/attendance/clock-in')
            ->assertRedirect('/attendance');

        // ステータスが出勤中になっている事を確認
        $response = $this->get('/attendance');
        $response->assertSee('出勤中');
    }


    public function test_clock_in_multiple() // 退勤後の出勤不可テスト
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

        // 出勤ボタンが表示されていない事を確認
        $response = $this->get('/attendance');
        $response->assertStatus(200);
        $response->assertDontSee('<button type="submit" class="btn">出勤</button>', false);

    }


    public function test_clock_in_list() // 一覧表示の出勤時間確認
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

        // 出勤登録
        $response = $this->post('/attendance/clock-in')
            ->assertRedirect('/attendance');

        $attendance = Attendance::where('user_id', auth()->id())
            ->latest('id')
            ->firstOrFail();

        $date = $attendance->work_date->format('m/d');
        $clock_in = $attendance->clock_in_at->format('H:i');

        $response = $this->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSeeInOrder([$date, $clock_in]);
    }

}
