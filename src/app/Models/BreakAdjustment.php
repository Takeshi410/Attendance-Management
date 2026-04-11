<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
    'attendance_adjustment_id',
    'break_id',
    'after_break_start_at',
    'after_break_end_at',
    ];

    protected $casts = [
        'after_break_start_at' => 'datetime',
        'after_break_end_at'   => 'datetime',
    ];

    public function break()
    {
        return $this->belongsTo(BreakModel::Class);
    }

    public function attendanceAdjustment()
    {
        return $this->belongsTo(AttendanceAdjustment::Class);
    }
}
