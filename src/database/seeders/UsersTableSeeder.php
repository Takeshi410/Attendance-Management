<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $param = [
        'name' => '山田 太郎',
        'email' => 'admin@example.com',
        'password' => Hash::make('password'),
        'work_pattern' => 1,
        'is_admin' => true,
        'email_verified_at' => Carbon::now(),
        'remember_token' => Str::random(10),
        ];
        DB::table('users')->insert($param);

        $param = [
        'name' => '鈴木 二郎',
        'email' => 'general@example.com',
        'password' => Hash::make('password'),
        'work_pattern' => 1,
        'is_admin' => false,
        'email_verified_at' => Carbon::now(),
        'remember_token' => Str::random(10),
        ];
        DB::table('users')->insert($param);

        User::factory()->count(4)->create();

    }
}
