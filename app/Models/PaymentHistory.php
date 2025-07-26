<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PaymentHistory extends Model
{
    use HasUuids;

    protected $table = 'payment_history';

    protected $fillable = [
        'username', 'subscription_id', 'stripe_payment_intent_id', 
        'stripe_invoice_id', 'amount', 'currency', 'status', 
        'payment_method', 'description', 'paid_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }

    public function subscription()
    {
        return $this->belongsTo(UserSubscription::class, 'subscription_id');
    }

    public function getFormattedAmountAttribute()
    {
        return '$' . number_format($this->amount, 2);
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'succeeded';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'succeeded' => 'green',
            'pending' => 'yellow',
            'failed' => 'red',
            'canceled' => 'gray',
            'refunded' => 'blue',
            default => 'gray'
        };
    }

    // Scopes
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'succeeded');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeByUser($query, $username)
    {
        return $query->where('username', $username);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('paid_at', now()->month)
                    ->whereYear('paid_at', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('paid_at', now()->year);
    }
}