<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;


    protected $fillable = [
        'work_date',
        'user_id',
        'clock_in_at',
        'clock_out_at',
    ];

    protected $casts = [
        'work_date' => 'date',
        'clock_in_at' => 'datetime',
        'clock_out_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::Class);
    }

    public function breaks()
    {
        return $this->hasMany(BreakModel::Class);
    }

    public function latestBreak()
    {
        return $this->hasOne(BreakModel::class)->latestOfMany('break_start_at');
    }

    public function attendanceAdjustments()
    {
        return $this->hasMany(AttendanceAdjustment::Class);
    }

    public function latestAttendanceAdjustment()
    {
        return $this->hasOne(AttendanceAdjustment::class)
        ->latestOfMany('id');
    }
}
