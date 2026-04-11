<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Attendance;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvDownloadController extends Controller
{
    public function downloadCsv(Request $request)
    {
        // dd($request);
        $id = $request->user_id;
        $date = Carbon::createFromFormat('Y/m/d', $request->month . '/01');

        $start_date = $date->copy()->startOfMonth();
        $end_date = $date->copy()->endOfMonth();
        $days = CarbonPeriod::create($start_date, $end_date);

        $query = Attendance::where('user_id', $id)
            ->whereBetween('work_date', [$start_date->toDateString(), $end_date->toDateString()])
            ->with('breaks')
            ->get()
            ->keyBy(fn ($a) => $a->work_date->format('Y-m-d'));

        # 休憩時間の取得
        $attendances = $query->map(function ($a) {
            $break_minutes = $a->breaks->sum(function ($b) {
                if (!$b->break_start_at || !$b->break_end_at) return 0;
                return $b->break_end_at->diffInMinutes($b->break_start_at);
            });

            # 休憩時間の算出
            $break_hours = intdiv($break_minutes, 60);
            $break_minutes = $break_minutes % 60;
            $a->break_hm = sprintf('%d:%02d', $break_hours, $break_minutes);

            if (!$a->clock_in_at || !$a->clock_out_at) {
                $a->work_minutes = 0;
                return $a;
            }

            # 勤務合計時間の算出
            $work_minutes = $a->clock_out_at->diffInMinutes($a->clock_in_at);
            $a->work_minutes = max(0, $work_minutes - $break_minutes);

            $WorkHours = intdiv($a->work_minutes, 60);
            $work_minutes = $a->work_minutes % 60;
            $a->work_hm = sprintf('%d:%02d', $WorkHours, $work_minutes);

            return $a;
        });

        # CSVファイル出力
        $response = new StreamedResponse(function () use ($attendances, $days) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['日付', '出勤', '退勤', '休憩', '合計']);

            foreach ($days as $day) {
                $key = $day->format('Y-m-d');
                $attendance = $attendances->get($key);
                fputcsv($handle, [
                    $day->format('Y/m/d'),
                    $attendance?->clock_in_at?->format('H:i') ?? '',
                    $attendance?->clock_out_at?->format('H:i') ?? '',
                    $attendance?->break_hm ?? '',
                    $attendance?->work_hm ?? '',
                ]);
            }

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attendance.csv"',
        ]);

        return $response;
    }
}
