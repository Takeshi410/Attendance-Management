<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Database\Seeders\WorkPatternsTableSeeder;

class RegisterTest extends TestCase
{
    use DatabaseMigrations;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_username_required() // 名前未入力バリデーションテスト
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
            ]);

        $response->assertSessionDoesntHaveErrors(['email', 'password', 'password_confirmation']);
    }


    public function test_email_required() // メールアドレス未入力バリデーションテスト
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->post('/register', [
            'name' => 'test',
            'email' => '',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
            ]);

        $response->assertSessionDoesntHaveErrors(['name', 'password', 'password', 'password_confirmation']);
    }


    public function test_password_min() // パスワード文字数バリデーションテスト
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->post('/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'pass',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードは8文字以上で入力してください',
            ]);

        $response->assertSessionDoesntHaveErrors(['name', 'email']);
    }


    public function test_password_same() // 確認用パスワード未入力バリデーションテスト
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->post('/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'pass',
        ]);

        $response->assertSessionHasErrors([
            'password_confirmation' => 'パスワードと一致しません',
            ]);

        $response->assertSessionDoesntHaveErrors(['name', 'email', 'password']);
    }


    public function test_password_required() // パスワード未入力バリデーションテスト
    {
        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->post('/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
            ]);

        $response->assertSessionDoesntHaveErrors(['name', 'email']);
    }


    public function test_register_success() // 会員登録テスト
    {
        $this->seed(WorkPatternsTableSeeder::class);

        $response = $this->get('/register');
        $response->assertStatus(200);

        $response = $this->post('/register', [
            'name' => 'test',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionDoesntHaveErrors(['name', 'email', 'password', 'password_confirmation']);
        $response->assertStatus(302);
        $response->assertRedirect('/attendance');
    }
}
