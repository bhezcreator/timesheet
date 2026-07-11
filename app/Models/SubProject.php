<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubProject extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'description',
        'status'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}
