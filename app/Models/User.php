<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, Billable;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'username';

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

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'createdat',
        'is_admin',
        'stripe_customer_id',
        'subscription_status',
        'subscription_ends_at',
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
            'createdat' => 'datetime',
            'is_admin' => 'boolean',
            'subscription_ends_at' => 'datetime',
        ];
    }

    // Relationships for your existing schema
    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class, 'username', 'username');
    }

    public function badges()
    {
        return $this->hasMany(UserBadge::class, 'username', 'username');
    }

    public function progress()
    {
        return $this->hasMany(UserProgress::class, 'username', 'username');
    }

    public function planFeatures()
    {
        return $this->hasMany(UserPlanFeature::class, 'username', 'username');
    }

    // Subscription helper methods
    public function hasActiveSubscription(): bool
    {
        return in_array($this->subscription_status, ['active', 'trialing']);
    }

    public function canAccessPremiumContent(): bool
    {
        return $this->hasActiveSubscription();
    }

    public function canAccessCourse($courseId): bool
    {
        // Check if course requires premium access
        $course = VideoCourse::find($courseId);
        if (!$course || !$course->is_premium) {
            return true; // Free content
        }
        
        return $this->hasActiveSubscription();
    }

    public function canAccessGame($gameId): bool
    {
        $game = FlashGame::find($gameId);
        if (!$game || !$game->is_premium) {
            return true; // Free content
        }
        
        return $this->hasActiveSubscription();
    }

    public function canEarnBadge($badgeId): bool
    {
        $badge = BadgeSystem::find($badgeId);
        if (!$badge || !$badge->is_premium) {
            return true; // Free badge
        }
        
        return $this->hasActiveSubscription();
    }

    public function getCurrentSubscription()
    {
        return $this->subscriptions()
                    ->whereIn('status', ['active', 'trialing'])
                    ->with('plan')
                    ->first();
    }

    public function getActiveFeatures()
    {
        $subscription = $this->getCurrentSubscription();
        if (!$subscription) {
            return ['basic_features']; // Default free features
        }

        return $subscription->plan->features ?? [];
    }

    public function canUseFeature($featureName): bool
    {
        if (!$this->hasActiveSubscription()) {
            return in_array($featureName, ['basic_courses', 'progress_tracking']);
        }

        $activeFeatures = $this->getActiveFeatures();
        return in_array($featureName, $activeFeatures);
    }
}
