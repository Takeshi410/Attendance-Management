<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakModel extends Model
{
    use HasFactory;

    protected $table = 'breaks';

    protected $fillable = [
        'attendance_id',
        'sequence',
        'break_start_at',
        'break_end_at',
    ];

    protected $casts = [
        'break_start_at' => 'datetime',
        'break_end_at'   => 'datetime',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::Class);
    }

    public function breakAdjustment()
    {
        return $this->hasOne(BreakAdjustment::Class, 'break_id', 'id');
    }
}
