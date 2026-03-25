<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'attendance_date',
        'check_in_at',
        'check_out_at',
        'check_in_ip',
        'check_out_ip',
        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',
        'status',
        'is_late',
        'late_minutes',
        'total_work_minutes',
        'checked_in_by',
        'checked_out_by',
        'manager_approved_by',
        'manager_approved_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'attendance_date' => 'date',
            'check_in_at' => 'datetime',
            'check_out_at' => 'datetime',
            'manager_approved_at' => 'datetime',
            'is_late' => 'boolean',
            'check_in_latitude' => 'float',
            'check_in_longitude' => 'float',
            'check_out_latitude' => 'float',
            'check_out_longitude' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function checkedOutBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }

    public function managerApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_approved_by');
    }

    public function overrides(): HasMany
    {
        return $this->hasMany(AttendanceOverride::class);
    }
}
