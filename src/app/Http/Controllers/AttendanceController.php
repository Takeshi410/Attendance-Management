<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Attendance;
use App\Models\User;
use App\Models\BreakModel;
use App\Models\AttendanceAdjustment;
use App\Models\BreakAdjustment;
use App\Http\Requests\DetailRequest;

class AttendanceController extends Controller
{
    public function index()
    {
        $user_id = auth()->id();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user_id)
            ->whereDate('work_date', $today)
            ->with('latestBreak')
            ->first();

    return view('attendance', compact('attendance'));
    }


    public function clockIn()
    {
        $user_id = auth()->id();
        $now = Carbon::now();
        $date = $now->format('Y-m-d');
        $time = $now->format('H:i');

        $attendance = Attendance::Create([
        'work_date' => $date,
        'user_id' => $user_id,
        'clock_in_at' => $time,
        ]);

        return redirect()->route('attendance.index');
    }


    public function clockOut(Request $request)
    {
        $now = Carbon::now();
        $time = $now->format('H:i');

        Attendance::find($request->attendance_id)->update([
            'clock_out_at' => $time,
        ]);

        return redirect()->route('attendance.index');
    }


    public function breakStart(Request $request)
    {
        $attendance_id = $request->attendance_id;
        $now = Carbon::now();
        $time = $now->format('H:i');

        $max_sequence = BreakModel::where('attendance_id', $attendance_id)->max('sequence');
        $next_sequence= ($max_sequence ?? 0) + 1;

        BreakModel::Create([
            'attendance_id' => $attendance_id,
            'sequence' => $nextSeq,
            'break_start_at' => $time,
        ]);
        return redirect()->route('attendance.index');
    }


    public function breakEnd(Request $request)
    {
        $now = Carbon::now();
        $time = $now->format('H:i');

        $max_sequence = BreakModel::find($request->break_id)->update([
            'break_end_at' => $time,
        ]);
        return redirect()->route('attendance.index');
    }


    public function list(Request $request)
    {
        $user_id = auth()->id();

        if ($request->isMethod('post')) {
            $date = Carbon::createFromFormat('Y/m/d', $request->month . '/01');
        } else {
            $date = Carbon::today();
        }


        $start_date = $date->copy()->startOfMonth();
        $end_date = $date->copy()->endOfMonth();
        $days = CarbonPeriod::create($start_date, $end_date);
        $month = $start_date->copy()->format('Y/m');
        $last_month = $start_date->copy()->subMonth()->format('Y/m');
        $next_month = $start_date->copy()->addMonth()->format('Y/m');


        $query = Attendance::where('user_id', $user_id)
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

        $week = ['日', '月', '火', '水', '木', '金', '土'];

        return view('list', compact('month', 'last_month', 'next_month', 'days', 'attendances', 'week'));
    }


    public function detail($attendance_id){
        $attendance = Attendance::with([
            'user',
            'breaks.breakAdjustment',
            'latestAttendanceAdjustment' => function ($query) {
                $query->where('is_admin', false);
            },
        ])->findOrFail($attendance_id);

        return view('detail', compact('attendance'));
    }


    public function request(DetailRequest $request, $attendance_id){
        $detail = $request->only('clock_in_at', 'clock_out_at', 'remarks','breaks');

        $adjustment = AttendanceAdjustment::create([
            'attendance_id' => $attendance_id,
            'after_clock_in_at' => $detail['clock_in_at'],
            'after_clock_out_at' => $detail['clock_out_at'],
            'remarks' => $detail['remarks'],
            'is_approval' => false,
            'is_admin' => false,
        ]);

        if(!empty($detail['breaks'])) {
            $adjustment->breakAdjustments()->createMany(
                collect($detail['breaks'])->map(function ($b) {
                    return [
                        'break_id' => $b['break_id'],
                        'after_break_start_at' => $b['break_start_at'],
                        'after_break_end_at' => $b['break_end_at'],
                    ];
                })->toArray()
            );
        }

        return redirect()->route('attendance.detail', ['attendance_id' => $request->attendance_id]);
    }
}
