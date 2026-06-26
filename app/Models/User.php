<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use App\Casts\BinaryUuid;
use App\Models\Concerns\HasBinaryUuid;



class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasBinaryUuid;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'avatar', 'is_active'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'uuid' => BinaryUuid::class,
    ];


    public function createdPurchaseInvoices()
    {
        return $this->hasMany(PurchaseInvoice::class, 'created_by');
    }

    public function createdSalesInvoices()
    {
        return $this->hasMany(SalesInvoice::class, 'created_by');
    }

    public function createdBudgets()
    {
        return $this->hasMany(Budget::class, 'created_by');
    }

    public function createdExpenses()
    {
        return $this->hasMany(Expense::class, 'created_by');
    }

    public function createdPayments()
    {
        return $this->hasMany(Payment::class, 'created_by');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }
}
