<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'is_admin',
        'stripe_customer_id',
        'subscription_status',
        'subscription_ends_at'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'subscription_ends_at' => 'datetime',
        ];
    }

    // Primary key setting
    protected $primaryKey = 'username';
    public $incrementing = false;
    protected $keyType = 'string';

    // Subscription relationships
    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'username', 'username');
    }

    public function activeSubscription()
    {
        return $this->hasOne(UserSubscription::class, 'username', 'username')
                    ->where('status', 'active')
                    ->where('current_period_end', '>', now());
    }

    // Subscription helper methods
    public function hasActiveSubscription()
    {
        return $this->subscription_status === 'active' && 
               $this->subscription_ends_at && 
               $this->subscription_ends_at->isFuture();
    }

    public function isPremiumMember()
    {
        return $this->hasActiveSubscription();
    }

    public function getSubscriptionDaysRemainingAttribute()
    {
        if (!$this->subscription_ends_at) {
            return 0;
        }
        
        return max(0, $this->subscription_ends_at->diffInDays(now()));
    }
}
