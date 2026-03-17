<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    protected $fillable = [
        'name',
        'default_quota',
    ];

    // Relasi: 1 Tipe Cuti memiliki banyak Leave Balances (milik berbagai user)
    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    // Relasi: 1 Tipe Cuti digunakan di banyak Leave Requests
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }
}