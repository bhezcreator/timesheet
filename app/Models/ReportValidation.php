<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportValidation extends Model
{
    protected $fillable = [
        'monthly_report_id',
        'validator_id',
        'decision',
        'comment',
        'validated_at'
    ];

    protected $casts = [

        'validated_at' => 'datetime'

    ];

    public function report()
    {
        return $this->belongsTo(MonthlyReport::class, 'monthly_report_id');
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validator_id');
    }
}
