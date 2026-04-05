<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkPattern extends Model
{
    use HasFactory;

    protected $fillable = [
        'pattern_name',
        'start_time',
        'end_time',
        'break_minutes',
    ];

    public function users()
    {
        return $this->hasMany(User::Class);
    }
}
