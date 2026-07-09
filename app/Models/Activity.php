<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'sub_project_id',
        'activity_type_id',
        'activity_date',
        'start_time',
        'end_time',
        'duration',
        'description',
        'status',
        'rejection_reason',
        'submitted_at',
    ];

    protected $casts = [
        'activity_date' => 'date',
        'submitted_at' => 'datetime',
        'duration' => 'decimal:2',
    ];

    public function scopeCurrentMonth($query)
    {
        return $query
            ->whereMonth('activity_date', now())
            ->whereYear('activity_date', now());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function subProject(): BelongsTo
    {
        return $this->belongsTo(SubProject::class);
    }

    public function activityType(): BelongsTo
    {
        return $this->belongsTo(ActivityType::class);
    }

    public function monthlyReport()
    {
        return $this->belongsTo(MonthlyReport::class);
    }
}
