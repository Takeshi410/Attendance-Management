<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AttendanceAdjustment;

class CorrectionRequestController extends Controller
{
    public function correction(Request $request) {
        $tab = $request->query('tab', 'recommend');
        $class = $request->user_class;

        if ($class === 'admin'){
            $query = AttendanceAdjustment::query()
            ->with('attendance')
            ->with('attendance.user');
        } else {
            $query = AttendanceAdjustment::query()
                ->whereHas('attendance', function ($q) {
                    $q->where('user_id', auth()->id());
                })
                ->with('attendance.user');
        }

        if ($tab === 'approved') {
            $query->where('is_approval', true);
        } elseif ($tab === 'recommend') {
            $query->where('is_approval', false);
        }

        $corrections = $query->get()->sortByDesc('id');

        return view('correction', compact('corrections', 'tab'));
    }
}
