<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 一般ユーザのみ取得
        $users = User::where('role',0)->get();

        // 今日の日付
        $today = Carbon::today();

        // 直近５日分の勤怠を作る
        for ($i = 1; $i <= 5; $i++) {

            // 今日からi日前の日付
            $date = $today->copy()->subDays($i);

            foreach ($users as $user) {

                // 出勤・退勤
                $start = $date->format('Y-m-d') . ' 09:00:00';
                $end   = $date->format('Y-m-d') . ' 18:00:00';

                // 勤怠
                $attendance = Attendance::create([
                    'user_id'    => $user->id,
                    'date'       => $date->format('Y-m-d'),
                    'work_start' => $start,
                    'work_end'   => $end,
                    'status'     => 3,
                    'comment'    => 'テスト勤務',
                ]);

                // 休憩
                Rest::create([
                    'attendance_id' => $attendance->id,
                    'rest_start'    => $date->format('Y-m-d') . ' 13:00:00',
                    'rest_end'      => $date->format('Y-m-d') . ' 14:00:00',
                    'rest_time_total' => 60,
                ]);
            }
        }
    }
}
