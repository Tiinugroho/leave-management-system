<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
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
        ];
    }

    // Relasi: 1 User memiliki banyak Leave Balances
    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    // Relasi: 1 User mengajukan banyak Leave Requests
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'user_id');
    }

    // Relasi: 1 Admin merespons banyak Leave Requests
    public function respondedRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'responded_by');
    }

    // Relasi: 1 User/Admin menghapus (soft delete) banyak Leave Requests
    public function deletedRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'deleted_by');
    }
}