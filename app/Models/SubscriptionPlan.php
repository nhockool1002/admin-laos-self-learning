<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $table = 'subscription_plans';
    
    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    protected $fillable = [
        'id', 'name', 'description', 'price', 'currency',
        'billing_interval', 'stripe_product_id', 'features', 'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    public function userSubscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'plan_id');
    }

    public function courses()
    {
        return $this->hasMany(VideoCourse::class, 'required_plan_id');
    }

    public function games()
    {
        return $this->hasMany(FlashGame::class, 'required_plan_id');
    }

    public function badges()
    {
        return $this->hasMany(BadgeSystem::class, 'required_plan_id');
    }

    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }

    public function isActive()
    {
        return $this->is_active;
    }

    public function hasFeature($featureName)
    {
        return in_array($featureName, $this->features ?? []);
    }

    // Scope for active plans
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for monthly plans
    public function scopeMonthly($query)
    {
        return $query->where('billing_interval', 'month');
    }

    // Scope for yearly plans
    public function scopeYearly($query)
    {
        return $query->where('billing_interval', 'year');
    }
}