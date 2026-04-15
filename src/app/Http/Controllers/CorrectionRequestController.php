<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\BreakModel;
use App\Models\BreakAdjustment;
use App\Models\AttendanceAdjustment;

class CorrectionRequestController extends Controller
{
    public function index(Request $request) {
        $tab = $request->query('tab', 'recommend');
        $role = $request->attributes->get('user_role');

        if ($role === 'admin'){
            $query = AttendanceAdjustment::query()
            ->with('attendance')
            ->with('attendance.user')
            ->where('is_admin', false);
        } elseif ($role === 'general') {
            $query = AttendanceAdjustment::query()
                ->whereHas('attendance', function ($q) {
                    $q->where('user_id', auth()->id());
                })
                ->with('attendance.user')
                ->where('is_admin', false);;
        } else {
            return redirect('/login');
        }

        if ($tab === 'approved') {
            $query->where('is_approval', true);
        } elseif ($tab === 'recommend') {
            $query->where('is_approval', false);
        }

        $corrections = $query->get()->sortByDesc('id');

        return view('correction', compact('corrections', 'tab', 'role'));
    }


    public function approve($attendance_correction_request_id) {
        $adjustment = AttendanceAdjustment::with([
            'breakAdjustments',
            'attendance.user',
        ])->findOrFail($attendance_correction_request_id);

        return view('admin.approve', compact('adjustment'));
    }


    public function patch($attendance_correction_request_id) {
        $id = $attendance_correction_request_id;

        $attendance_adjustment = AttendanceAdjustment::find($id);

        if ($attendance_adjustment->is_approval) {
            return;
        }

        $attendance_id = $attendance_adjustment->attendance_id;

        Attendance::find($attendance_id)->update([
            'clock_in_at' => $attendance_adjustment->after_clock_in_at,
            'clock_out_at' => $attendance_adjustment->after_clock_out_at,
        ]);

        $break_adjustments = BreakAdjustment::where('attendance_adjustment_id', $id)->get();

        foreach ($break_adjustments as $break_adjustment) {
            if (!is_null($break_adjustment->break_id)) {
                BreakModel::whereKey($break_adjustment->break_id)->update([
                    'break_start_at' => $break_adjustment->after_break_start_at,
                    'break_end_at' => $break_adjustment->after_break_end_at,
                ]);
                continue;
            }

            $next_sequence = (BreakModel::where('attendance_id', $attendance_id)->max('sequence') ?? 0) + 1;

            $break = BreakModel::create([
                'attendance_id' => $attendance_id,
                'sequence' => $next_sequence,
                'break_start_at' => $break_adjustment->after_break_start_at,
                'break_end_at' => $break_adjustment->after_break_end_at,
            ]);

            $break_adjustment->update([
                'break_id' => $break->id,
            ]);
        }

        AttendanceAdjustment::find($id)->update([
            'is_approval' => true,
        ]);

        return redirect()->route('admin.approve', ['attendance_correction_request_id' => $id]);
    }
}
