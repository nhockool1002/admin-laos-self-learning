<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UserSubscription extends Model
{
    use HasUuids;

    protected $table = 'user_subscriptions';

    protected $fillable = [
        'username', 'stripe_customer_id', 'stripe_subscription_id',
        'plan_id', 'status', 'current_period_start', 'current_period_end',
        'trial_end', 'canceled_at', 'ended_at'
    ];

    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'trial_end' => 'datetime',
        'canceled_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function paymentHistory()
    {
        return $this->hasMany(PaymentHistory::class, 'subscription_id');
    }

    public function planFeatures()
    {
        return $this->hasMany(UserPlanFeature::class, 'subscription_id');
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['active', 'trialing']);
    }

    public function isTrialing(): bool
    {
        return $this->status === 'trialing';
    }

    public function isCanceled(): bool
    {
        return $this->status === 'canceled';
    }

    public function isPastDue(): bool
    {
        return $this->status === 'past_due';
    }

    public function hasEnded(): bool
    {
        return !is_null($this->ended_at);
    }

    public function daysUntilRenewal(): int
    {
        if (!$this->current_period_end) {
            return 0;
        }

        return now()->diffInDays($this->current_period_end);
    }

    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'green',
            'trialing' => 'blue',
            'past_due' => 'yellow',
            'canceled' => 'red',
            'unpaid' => 'red',
            default => 'gray'
        };
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['active', 'trialing']);
    }

    public function scopeInactive($query)
    {
        return $query->whereIn('status', ['canceled', 'unpaid', 'ended']);
    }

    public function scopeByUser($query, $username)
    {
        return $query->where('username', $username);
    }
}