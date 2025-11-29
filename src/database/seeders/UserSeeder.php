<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = now();
        
        // 管理者アカウントを作成
        User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 1,
            'email_verified_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 一般ユーザのダミーデータ
        $users = [
            [
                'name' => '佐藤 太郎',
                'email' => 'sato.taro@example.com',
                'password' => Hash::make('password'),
                'role' => 0,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '鈴木 花子',
                'email' => 'suzuki.hanako@example.com',
                'password' => Hash::make('password'),
                'role' => 0,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '高橋 健',
                'email' => 'takahashi.ken@example.com',
                'password' => Hash::make('password'),
                'role' => 0,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '田中 美咲',
                'email' => 'tanaka.misaki@example.com',
                'password' => Hash::make('password'),
                'role' => 0,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => '伊藤 翔',
                'email' => 'ito.sho@example.com',
                'password' => Hash::make('password'),
                'role' => 0,
                'email_verified_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        User::insert($users);
    }
}

