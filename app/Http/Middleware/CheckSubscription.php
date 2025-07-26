<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $feature
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $feature = null)
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'Bạn cần đăng nhập để truy cập nội dung này.');
        }

        if (!$user->hasActiveSubscription()) {
            return redirect()->route('subscription.plans')
                ->with('error', 'Nội dung này yêu cầu gói đăng ký đang hoạt động.');
        }

        // Check specific feature access if needed
        if ($feature && !$this->userCanAccessFeature($user, $feature)) {
            return redirect()->route('subscription.plans')
                ->with('error', 'Gói đăng ký hiện tại của bạn không bao gồm tính năng này.');
        }

        return $next($request);
    }

    /**
     * Check if user can access a specific feature
     *
     * @param  \App\Models\User  $user
     * @param  string  $feature
     * @return bool
     */
    private function userCanAccessFeature($user, $feature)
    {
        $subscription = $user->getCurrentSubscription();
        if (!$subscription) {
            return false;
        }

        $planFeatures = $subscription->plan->features ?? [];
        return in_array($feature, $planFeatures);
    }
}