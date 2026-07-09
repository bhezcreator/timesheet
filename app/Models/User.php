<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    /*     public function projects()
    {
        return $this->belongsToMany(Project::class);
    } */

    public function subProjects()
    {
        return $this->belongsToMany(SubProject::class)
            ->withTimestamps();
    }
    /*     public function subProjects()
    {
        return $this->belongsToMany(SubProject::class);
    } */

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function monthlyReports()
    {
        return $this->hasMany(MonthlyReport::class);
    }

    public function validatedReports()
    {
        return $this->hasMany(ReportValidation::class, 'validator_id');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class)
            ->withPivot([
                'role',
                'assigned_at',
                'ended_at'
            ])
            ->withTimestamps();
    }
}
