<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'username',
        'subscription_plan_id',
        'stripe_subscription_id',
        'stripe_customer_id',
        'status',
        'current_period_start',
        'current_period_end',
        'trial_end',
        'canceled_at',
        'ended_at'
    ];

    protected $casts = [
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'trial_end' => 'datetime',
        'canceled_at' => 'datetime',
        'ended_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('current_period_end', '<', now());
    }

    public function isActive()
    {
        return $this->status === 'active' && 
               $this->current_period_end && 
               $this->current_period_end->isFuture();
    }

    public function isExpired()
    {
        return $this->current_period_end && 
               $this->current_period_end->isPast();
    }

    public function getDaysRemainingAttribute()
    {
        if (!$this->current_period_end) {
            return 0;
        }
        
        return max(0, $this->current_period_end->diffInDays(now()));
    }
}