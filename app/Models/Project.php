<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'manager_id',
        'start_date',
        'end_date',
        'status'
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot([
                'role',
                'assigned_at',
                'ended_at'
            ])
            ->withTimestamps();
    }

    public function subProjects()
    {
        return $this->hasMany(SubProject::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}
