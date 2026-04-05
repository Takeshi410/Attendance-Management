<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_id',
        'after_clock_in_at',
        'after_clock_out_at',
        'remarks',
        'is_approval',
        'is_admin'
    ];

    protected $casts = [
        'after_clock_in_at' => 'datetime',
        'after_clock_out_at' => 'datetime',
        'is_approval' => 'boolean',
        'is_admin' => 'boolean',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::Class);
    }

    public function breakAdjustments()
    {
        return $this->hasMany(BreakAdjustment::Class);
    }
}
