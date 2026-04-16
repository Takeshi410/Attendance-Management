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
use App\Models\AttendanceAdjustment;

class AttendanceRequestTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_attendance_request_clock() // 勤怠詳細情報修正 出退勤時間バリデーションテスト
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
            ->firstOrFail();

        $payload = [
            'clock_in_at' => '12:00',
            'clock_out_at' => '11:00',
            'breaks' => $attendance->breaks
                ->map(fn ($break) => [
                    'break_start_at' => $break->break_start_at->format('H:i'),
                    'break_end_at' => $break->break_end_at->format('H:i'),
                    'sequence' => $break->sequence,
                ])
                ->values()
                ->all(),
        ];

        // 申請を実行し、出勤時間のバリデーションが表示される事を確認
        $response = $this->post(route('attendance.request', ['id' => $attendance->id]), $payload);

        $response->assertSessionHasErrors([
            'clock_in_at' => '出勤時間が不適切な値です',
        ]);
    }


    public function test_attendance_request_break() // 勤怠詳細情報修正 休憩時間バリデーションテスト
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
            ->firstOrFail();

        $payload = [
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'remarks' => '修正申請テスト',
            'breaks' => $attendance->breaks
                ->map(fn ($break, $index) => [
                    'break_start_at' => $index === 0 ? '12:00' : $break->break_start_at->format('H:i'),
                    'break_end_at' => $index === 0 ? '11:00' :  $break->break_end_at->format('H:i'),
                    'sequence' => $break->sequence,
                ])
                ->values()
                ->all(),
        ];

        // 申請を実行し、休憩時間のバリデーションを実行する事を確認
        $response = $this->post(route('attendance.request', ['id' => $attendance->id]), $payload);

        $response->assertSessionHasErrors([
            'breaks.0.break_start_at' => '休憩時間が不適切な値です',
        ]);
    }


    public function test_attendance_request_break_end() // 勤怠詳細情報修正 休憩終了時間バリデーションテスト
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
            ->firstOrFail();

        $payload = [
            'clock_in_at' => '09:00',
            'clock_out_at' => '15:00',
            'remarks' => '修正申請テスト',
            'breaks' => $attendance->breaks
                ->map(fn ($break, $index) => [
                    'break_start_at' => $index === 0 ? '14:00' : $break->break_start_at->format('H:i'),
                    'break_end_at' => $index === 0 ? '16:00' :  $break->break_end_at->format('H:i'),
                    'sequence' => $break->sequence,
                ])
                ->values()
                ->all(),
        ];

        // 申請を実行し、休憩時間のバリデーションが表示される事を確認
        $response = $this->post(route('attendance.request', ['id' => $attendance->id]), $payload);

        $response->assertSessionHasErrors([
            'breaks.0.break_end_at' => '退勤時間もしくは休憩時間が不適切な値です',
        ]);
    }


    public function test_attendance_request_remarks() // 勤怠詳細情報修正 備考欄バリデーションテスト
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
            ->firstOrFail();

        $payload = [
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'remarks' => '',
            'breaks' => $attendance->breaks
                ->map(fn ($break, $index) => [
                    'break_start_at' => $index === 0 ? '12:00' : $break->break_start_at->format('H:i'),
                    'break_end_at' => $index === 0 ? '13:00' :  $break->break_end_at->format('H:i'),
                    'sequence' => $break->sequence,
                ])
                ->values()
                ->all(),
        ];

        // 申請を実行し備考のバリデーションが表示される事を確認
        $response = $this->post(route('attendance.request', ['id' => $attendance->id]), $payload);

        $response->assertSessionHasErrors([
            'remarks' => '備考を記入してください',
        ]);
    }


    public function test_attendance_request_success_admin() // 勤怠詳細情報修正 成功後管理者確認テスト
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
        $user_name = auth()->user()->name;

        $attendance = Attendance::query()
            ->with('breaks')
            ->where('user_id', $user_id)
            ->inRandomOrder()
            ->firstOrFail();

        $payload = [
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'remarks' => '勤怠修正テスト',
            'breaks' => $attendance->breaks
                ->map(fn ($break, $index) => [
                    'break_start_at' => $index === 0 ? '12:00' : $break->break_start_at->format('H:i'),
                    'break_end_at' => $index === 0 ? '13:00' :  $break->break_end_at->format('H:i'),
                    'sequence' => $break->sequence,
                ])
                ->values()
                ->all(),
        ];

        // 申請を実行
        $response = $this->post(route('attendance.request', ['id' => $attendance->id]), $payload);

        $response->assertSessionDoesntHaveErrors([
            'clock_in_at',
            'clock_out_at',
            'remarks',
            'breaks.*.break_start_at',
            'breaks.*.break_end_at',
        ]);

        $adjustment = AttendanceAdjustment::where('attendance_id', $attendance->id)
            ->latest('id')
            ->firstOrFail();

        auth()->logout();

        // 管理者ログイン情報
        $email = 'admin@example.com';
        $password = 'password';

        // 管理者ログイン
        $response = $this->post('/admin/login', [
            'email' => $email,
            'password' => $password,
        ]);

        // 承認詳細画面を確認
        $response = $this->get(route('admin.approve', ['attendance_correction_request_id' => $adjustment->id]));
        $response->assertStatus(200);

        $year = $attendance->work_date->format('Y年');
        $date = $attendance->work_date->format('n月j日');

        $response->assertSeeInOrder([$user_name, $year, $date]);


        // 承認一覧画面に表示されている事を確認
        $response = $this->get('/stamp_correction_request/list');
        $response->assertStatus(200);

        $date = $attendance->work_date->format('Y/m/d');
        $remarks = $adjustment->remarks;

        $response->assertSeeInOrder([$user_name, $date, $remarks]);
    }


    public function test_attendance_request_success() // 勤怠詳細情報修正 成功後一般ユーザー確認テスト
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
        $user_name = auth()->user()->name;

        $attendance = Attendance::query()
            ->with('breaks')
            ->where('user_id', $user_id)
            ->inRandomOrder()
            ->firstOrFail();

        $payload = [
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'remarks' => '勤怠修正テスト',
            'breaks' => $attendance->breaks
                ->map(fn ($break, $index) => [
                    'break_start_at' => $index === 0 ? '12:00' : $break->break_start_at->format('H:i'),
                    'break_end_at' => $index === 0 ? '13:00' :  $break->break_end_at->format('H:i'),
                    'sequence' => $break->sequence,
                ])
                ->values()
                ->all(),
        ];

        // 申請を実行
        $response = $this->post(route('attendance.request', ['id' => $attendance->id]), $payload);

        $response->assertSessionDoesntHaveErrors([
            'clock_in_at',
            'clock_out_at',
            'remarks',
            'breaks.*.break_start_at',
            'breaks.*.break_end_at',
        ]);

        $adjustment = AttendanceAdjustment::where('attendance_id', $attendance->id)
            ->latest('id')
            ->firstOrFail();

        // 承認一覧画面に表示されている事を確認
        $response = $this->get('/stamp_correction_request/list');
        $response->assertStatus(200);

        $date = $attendance->work_date->format('Y/m/d');
        $remarks = $adjustment->remarks;

        $response->assertSeeInOrder([$user_name, $date, $remarks]);
    }


    public function test_attendance_request_approval() // 勤怠詳細情報修正 管理者承認と承認済確認テスト
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
        $user_name = auth()->user()->name;

        $attendance = Attendance::query()
            ->with('breaks')
            ->where('user_id', $user_id)
            ->inRandomOrder()
            ->firstOrFail();

        $payload = [
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'remarks' => '勤怠修正テスト',
            'breaks' => $attendance->breaks
                ->map(fn ($break, $index) => [
                    'break_start_at' => $index === 0 ? '12:00' : $break->break_start_at->format('H:i'),
                    'break_end_at' => $index === 0 ? '13:00' :  $break->break_end_at->format('H:i'),
                    'sequence' => $break->sequence,
                ])
                ->values()
                ->all(),
        ];

        // 申請を実行
        $response = $this->post(route('attendance.request', ['id' => $attendance->id]), $payload);

        $response->assertSessionDoesntHaveErrors([
            'clock_in_at',
            'clock_out_at',
            'remarks',
            'breaks.*.break_start_at',
            'breaks.*.break_end_at',
        ]);

        $adjustment = AttendanceAdjustment::where('attendance_id', $attendance->id)
            ->latest('id')
            ->firstOrFail();

        auth()->logout();

        // 管理者ログイン情報
        $email = 'admin@example.com';
        $password = 'password';

        // 管理者ログイン
        $response = $this->post('/admin/login', [
            'email' => $email,
            'password' => $password,
        ]);

        // 承認詳細画面を確認
        $response = $this->get(route('admin.approve', ['attendance_correction_request_id' => $adjustment->id]));
        $response->assertStatus(200);

        // 承認を実行
        $response = $this->patch(route('admin.approve_patch', ['attendance_correction_request_id' => $adjustment->id]));

        auth()->logout();

        // 再ログイン
        $response = $this->post('/login', [
            'email' => $email,
            'password' => $password,
        ]);

        // 承認一覧の承認済みに表示されている事を確認
        $response = $this->get('/stamp_correction_request/list?tab=approved');
        $response->assertStatus(200);

        $date = $attendance->work_date->format('Y/m/d');
        $remarks = $adjustment->remarks;

        $response->assertSeeInOrder([$user_name, $date, $remarks]);
    }


    public function test_attendance_request_detail() // 勤怠詳細情報修正 詳細画面遷移テスト
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
        $user_name = auth()->user()->name;

        $attendance = Attendance::query()
            ->with('breaks')
            ->where('user_id', $user_id)
            ->inRandomOrder()
            ->firstOrFail();

        $payload = [
            'clock_in_at' => '09:00',
            'clock_out_at' => '18:00',
            'remarks' => '勤怠修正テスト',
            'breaks' => $attendance->breaks
                ->map(fn ($break, $index) => [
                    'break_start_at' => $index === 0 ? '12:00' : $break->break_start_at->format('H:i'),
                    'break_end_at' => $index === 0 ? '13:00' :  $break->break_end_at->format('H:i'),
                    'sequence' => $break->sequence,
                ])
                ->values()
                ->all(),
        ];

        // 申請を実行
        $response = $this->post(route('attendance.request', ['id' => $attendance->id]), $payload);

        $response->assertSessionDoesntHaveErrors([
            'clock_in_at',
            'clock_out_at',
            'remarks',
            'breaks.*.break_start_at',
            'breaks.*.break_end_at',
        ]);

        $adjustment = AttendanceAdjustment::where('attendance_id', $attendance->id)
            ->latest('id')
            ->firstOrFail();

        // 承認一覧画面を確認
        $response = $this->get('/stamp_correction_request/list');
        $response->assertStatus(200);

        $expected_url = route('attendance.detail', ['id' => $attendance->id]);
        $response->assertSee('href="' . $expected_url . '"', false);

        // 勤怠詳細画面へ遷移できる事を確認
        $response = $this->get($expected_url);
        $response->assertStatus(200);

        $year = $attendance->work_date->format('Y年');
        $date = $attendance->work_date->format('n月j日');
        $remarks = $adjustment->remarks;

        $response->assertSeeInOrder([$user_name, $year, $date, $remarks]);
    }
}