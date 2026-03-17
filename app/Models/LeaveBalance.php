<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    protected $fillable = [
        'user_id',
        'leave_type_id',
        'year',
        'total_quota',
        'used',
    ];

    // Relasi: Leave Balance ini milik siapa?
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relasi: Leave Balance ini untuk tipe cuti apa?
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }
}