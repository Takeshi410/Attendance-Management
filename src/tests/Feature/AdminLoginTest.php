<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Database\Seeders\WorkPatternsTableSeeder;
use Database\Seeders\UsersTableSeeder;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_admin_email_required() // メールアドレス未入力バリデーションテスト（管理者）
    {
        $this->seed(WorkPatternsTableSeeder::class);
        $this->seed(UsersTableSeeder::class);

        $response = $this->get('/admin/login');
        $response->assertStatus(200);

        // ログイン情報
        $email = '';
        $password = 'password';

        // ログイン
        $response = $this->post('/admin/login', [
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
            ]);

        $response->assertSessionDoesntHaveErrors(['password']);


    }


    public function test_admin_password_required() // パスワード未入力バリデーションテスト（管理者）
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);

        // ログイン情報
        $email = 'admin@example.com';
        $password = '';

        // ログイン
        $response = $this->post('/admin/login', [
            'email' => $email,
            'password' => $password ,
            'password_confirmation' => $password ,
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
            ]);

        $response->assertSessionDoesntHaveErrors(['email']);
    }


    public function test_admin_unregistered() // 未登録バリデーションテスト（管理者）
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);

        // ログイン情報
        $email = 'test@example.com';
        $password = 'pass_test';

        // ログイン
        $response = $this->post('/admin/login', [
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }


}
