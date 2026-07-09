<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyReport extends Model
{
    protected $fillable = [
        'user_id',
        'month',
        'year',
        'report_date',
        'objectives',
        'achievements',
        'next_actions',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'report_date' => 'date',
        'submitted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function validation()
    {
        return $this->hasOne(ReportValidation::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}
