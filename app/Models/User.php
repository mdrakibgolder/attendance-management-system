<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'employment_code',
        'department_id',
        'manager_id',
        'is_active',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function managedLeaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'manager_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(User::class, 'manager_id');
    }

    public function attendanceOverrides(): HasMany
    {
        return $this->hasMany(AttendanceOverride::class, 'overridden_by');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('Admin');
    }

    public function isManager(): bool
    {
        return $this->hasRole('Manager');
    }

    public function isEmployee(): bool
    {
        return $this->hasRole('Employee');
    }
}
