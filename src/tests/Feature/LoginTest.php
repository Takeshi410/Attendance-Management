<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Database\Seeders\WorkPatternsTableSeeder;
use Database\Seeders\UsersTableSeeder;

class LoginTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function test_email_required() // メールアドレス未入力バリデーションテスト
    {
        $this->seed(WorkPatternsTableSeeder::class);
        $this->seed(UsersTableSeeder::class);

        // ログイン情報
        $email = '';
        $password = 'password';

        // ログイン
        $response = $this->get('/login');
        $response->assertStatus(200);

        $response = $this->post('/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
            ]);

        $response->assertSessionDoesntHaveErrors(['password']);
    }


    public function test_password_required() // パスワード未入力バリデーションテスト
    {
        // ログイン情報
        $email = 'general@example.com';
        $password = '';

        // ログイン
        $response = $this->get('/login');
        $response->assertStatus(200);

        $response = $this->post('/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
            ]);

        $response->assertSessionDoesntHaveErrors(['email']);
    }


    public function test_login_unregistered() // 未登録バリデーションテスト
    {
        // ログイン情報
        $email = 'test@test.com';
        $password = 'test_pass';

        // ログイン
        $response = $this->get('/login');
        $response->assertStatus(200);

        $response = $this->post('/login', [
            'email' => $email,
            'password' => $password,
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
            ]);
    }
}
