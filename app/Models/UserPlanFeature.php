<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UserPlanFeature extends Model
{
    use HasUuids;

    protected $table = 'user_plan_features';

    protected $fillable = [
        'username', 'subscription_id', 'feature_name', 'usage_count',
        'usage_limit', 'reset_period', 'last_reset_at'
    ];

    protected $casts = [
        'usage_count' => 'integer',
        'usage_limit' => 'integer',
        'last_reset_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }

    public function subscription()
    {
        return $this->belongsTo(UserSubscription::class, 'subscription_id');
    }

    public function canUse(): bool
    {
        if ($this->usage_limit === null) {
            return true; // Unlimited usage
        }

        $this->resetIfNeeded();
        
        return $this->usage_count < $this->usage_limit;
    }

    public function incrementUsage(): bool
    {
        if (!$this->canUse()) {
            return false;
        }

        $this->increment('usage_count');
        return true;
    }

    public function getRemainingUsageAttribute(): ?int
    {
        if ($this->usage_limit === null) {
            return null; // Unlimited
        }

        $this->resetIfNeeded();
        
        return max(0, $this->usage_limit - $this->usage_count);
    }

    public function getUsagePercentageAttribute(): float
    {
        if ($this->usage_limit === null) {
            return 0; // Unlimited usage
        }

        $this->resetIfNeeded();
        
        return ($this->usage_count / $this->usage_limit) * 100;
    }

    public function resetIfNeeded(): void
    {
        if (!$this->shouldReset()) {
            return;
        }

        $this->update([
            'usage_count' => 0,
            'last_reset_at' => now(),
        ]);
    }

    protected function shouldReset(): bool
    {
        if ($this->reset_period === 'never') {
            return false;
        }

        $resetInterval = match($this->reset_period) {
            'daily' => now()->subDay(),
            'weekly' => now()->subWeek(),
            'monthly' => now()->subMonth(),
            default => null,
        };

        return $resetInterval && $this->last_reset_at < $resetInterval;
    }

    // Scopes
    public function scopeByUser($query, $username)
    {
        return $query->where('username', $username);
    }

    public function scopeByFeature($query, $featureName)
    {
        return $query->where('feature_name', $featureName);
    }

    public function scopeUnlimited($query)
    {
        return $query->whereNull('usage_limit');
    }

    public function scopeLimited($query)
    {
        return $query->whereNotNull('usage_limit');
    }
}