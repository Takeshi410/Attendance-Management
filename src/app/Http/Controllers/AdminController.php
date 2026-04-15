<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\Attendance;
use App\Models\User;
use App\Models\BreakModel;
use App\Models\AttendanceAdjustment;
use App\Http\Requests\DetailRequest;

class AdminController extends Controller
{
    public function list(Request $request)
    {
        $user_id = auth()->id();

        if ($request->isMethod('post')) {
            $date = Carbon::createFromFormat('Y/m/d', $request->date);
        } else {
            $date = Carbon::today();
        }

        $last_date = $date->copy()->subDay()->format('Y/m/d');
        $next_date = $date->copy()->addDay()->format('Y/m/d');

        $query = Attendance::where('work_date', $date->format('Y/m/d'))
            ->with(['breaks','user'])
            ->get();

        # 休憩時間の取得
        $attendances = $query->map(function ($a) {
            $break_total_minutes = $a->breaks->sum(function ($b) {
                if (!$b->break_start_at || !$b->break_end_at) return 0;
                return $b->break_end_at->diffInMinutes($b->break_start_at);
            });

            # 休憩時間の算出
            $break_hours = intdiv($break_total_minutes, 60);
            $break_minutes = $break_total_minutes % 60;
            $a->break_hm = sprintf('%d:%02d', $break_hours, $break_minutes);

            if (!$a->clock_in_at || !$a->clock_out_at) {
                $a->work_minutes = 0;
                return $a;
            }

            # 勤務合計時間の算出
            $work_total_minutes = $a->clock_out_at->diffInMinutes($a->clock_in_at);
            $a->work_time = max(0, $work_total_minutes - $break_total_minutes);

            $WorkHours = intdiv($a->work_time, 60);
            $work_minutes = $a->work_time % 60;
            $a->work_hm = sprintf('%d:%02d', $WorkHours, $work_minutes);

            return $a;
        });

        return view('admin.list', compact('date', 'last_date', 'next_date', 'attendances'));
    }

    public function detail($id){
        $attendance = Attendance::with([
            'user',
            'breaks',
            'latestAttendanceAdjustment.breakAdjustments',
        ])->findOrFail($id);

        $breaks = $attendance->latestAttendanceAdjustment
            ? $attendance->latestAttendanceAdjustment->breakAdjustments->map(function ($b) {
                return [
                    'id' => $b->id,
                    'sequence' => $b->sequence,
                    'break_start_at' => optional($b->after_break_start_at)->format('H:i'),
                    'break_end_at' => optional($b->after_break_end_at)->format('H:i'),
                ];
            })
            : $attendance->breaks->map(function ($b) {
                return [
                    'id' => $b->id,
                    'sequence' => $b->sequence,
                    'break_start_at' => optional($b->break_start_at)->format('H:i'),
                    'break_end_at' => optional($b->break_end_at)->format('H:i'),
                ];
            });

        return view('admin.detail', compact('attendance', 'breaks'));
    }


    public function correction(DetailRequest $request, $id) {
        $detail = $request->only('clock_in_at', 'clock_out_at', 'remarks', 'breaks', 'new_break');

        $adjustment = AttendanceAdjustment::create([
            'attendance_id' => $id,
            'after_clock_in_at' => $detail['clock_in_at'],
            'after_clock_out_at' => $detail['clock_out_at'],
            'remarks' => $detail['remarks'],
            'is_approval' => false,
            'is_admin' => true,
        ]);

        Attendance::find($id)->update([
            'clock_in_at' => $detail['clock_in_at'],
            'clock_out_at' => $detail['clock_out_at'],
        ]);


        if(!empty($detail['breaks'])) {
            $adjustment->breakAdjustments()->createMany(
                collect($detail['breaks'])->map(function ($b) {
                    return [
                        'break_id' => $b['break_id'],
                        'sequence' => $b['sequence'],
                        'after_break_start_at' => $b['break_start_at'],
                        'after_break_end_at' => $b['break_end_at'],
                    ];
                })->toArray()
            );

            foreach ($detail['breaks'] as $break) {
                BreakModel::find($break['break_id'])->update([
                'break_start_at' => $break['break_start_at'],
                'break_end_at' => $break['break_end_at'],
                ]);
            };
        }

        $new_break_start = $detail['new_break']['break_start_at'] ?? null;
        $new_break_end = $detail['new_break']['break_end_at'] ?? null;

        if($new_break_start !== null || $new_break_end !== null) {
            $break_adjustment = $adjustment->breakAdjustments()->create([
                'sequence' => $detail['new_break']['sequence'],
                'after_break_start_at' => $new_break_start,
                'after_break_end_at' => $new_break_end,
                ]);

            $next_sequence = (BreakModel::where('attendance_id', $id)->max('sequence') ?? 0) + 1;

            $break = BreakModel::create([
                'attendance_id' => $id,
                'sequence' => $next_sequence,
                'break_start_at' => $new_break_start,
                'break_end_at' => $new_break_end,
            ]);

            $break_adjustment->update([
                'break_id' => $break->id,
            ]);

        }

        return redirect()->route('admin.detail', ['id' => $id]);
    }


    public function staffList() {
        $members = User::where('is_admin', false)->get();

        return view('admin.staff_list', compact('members'));
    }


    public function staffDetail(Request $request, $id) {

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

        $user = User::find($id);

        $query = Attendance::where('user_id', $id)
            ->whereBetween('work_date', [$start_date->toDateString(), $end_date->toDateString()])
            ->with('breaks')
            ->get()
            ->keyBy(fn ($a) => $a->work_date->format('Y-m-d'));

        # 休憩時間の取得
        $attendances = $query->map(function ($a) {
            $break_total_minutes = $a->breaks->sum(function ($b) {
                if (!$b->break_start_at || !$b->break_end_at) return 0;
                return $b->break_end_at->diffInMinutes($b->break_start_at);
            });

            # 休憩時間の算出
            $break_hours = intdiv($break_total_minutes, 60);
            $break_minutes = $break_total_minutes % 60;
            $a->break_hm = sprintf('%d:%02d', $break_hours, $break_minutes);

            if (!$a->clock_in_at || !$a->clock_out_at) {
                $a->work_minutes = 0;
                return $a;
            }

            # 勤務合計時間の算出
            $work_total_minutes = $a->clock_out_at->diffInMinutes($a->clock_in_at);
            $a->work_time = max(0, $work_total_minutes - $break_total_minutes);

            $WorkHours = intdiv($a->work_time, 60);
            $work_minutes = $a->work_time % 60;
            $a->work_hm = sprintf('%d:%02d', $WorkHours, $work_minutes);

            return $a;
        });

        $week = ['日', '月', '火', '水', '木', '金', '土'];

        return view('admin.staff_attendance_list', compact('month', 'last_month', 'next_month', 'days', 'attendances', 'user', 'week'));
    }
}
