<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use App\Models\User;
use App\Services\StripeService;
use Exception;

class SubscriptionController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Get all subscription plans
     */
    public function getPlans()
    {
        try {
            $plans = SubscriptionPlan::active()->get();
            
            return response()->json([
                'success' => true,
                'data' => $plans
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subscription plans',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create checkout session for subscription
     */
    public function createCheckoutSession(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string',
                'plan_id' => 'required|exists:subscription_plans,id'
            ]);

            $user = User::where('username', $request->username)->firstOrFail();
            $plan = SubscriptionPlan::findOrFail($request->plan_id);

            // Check if user already has active subscription
            if ($user->hasActiveSubscription()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User already has an active subscription'
                ], 400);
            }

            $successUrl = $request->input('success_url', config('app.frontend_url') . '/subscription/success');
            $cancelUrl = $request->input('cancel_url', config('app.frontend_url') . '/subscription/cancel');

            $session = $this->stripeService->createCheckoutSession(
                $user, 
                $plan, 
                $successUrl, 
                $cancelUrl
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'checkout_url' => $session->url,
                    'session_id' => $session->id
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create checkout session',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's subscription status
     */
    public function getUserSubscription(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string'
            ]);

            $user = User::with(['activeSubscription.subscriptionPlan'])
                        ->where('username', $request->username)
                        ->firstOrFail();

            $subscription = $user->activeSubscription;

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => [
                        'username' => $user->username,
                        'email' => $user->email,
                        'subscription_status' => $user->subscription_status,
                        'subscription_ends_at' => $user->subscription_ends_at,
                        'days_remaining' => $user->subscription_days_remaining,
                        'is_premium' => $user->isPremiumMember()
                    ],
                    'subscription' => $subscription ? [
                        'id' => $subscription->id,
                        'plan' => $subscription->subscriptionPlan,
                        'status' => $subscription->status,
                        'current_period_start' => $subscription->current_period_start,
                        'current_period_end' => $subscription->current_period_end,
                        'days_remaining' => $subscription->days_remaining
                    ] : null
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user subscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string'
            ]);

            $user = User::where('username', $request->username)->firstOrFail();
            $subscription = $user->activeSubscription;

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription found'
                ], 400);
            }

            $this->stripeService->cancelSubscription($subscription->stripe_subscription_id);

            return response()->json([
                'success' => true,
                'message' => 'Subscription canceled successfully'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel subscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resume subscription
     */
    public function resumeSubscription(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string'
            ]);

            $user = User::where('username', $request->username)->firstOrFail();
            $subscription = UserSubscription::where('username', $user->username)
                                          ->where('status', 'canceled')
                                          ->latest()
                                          ->first();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No canceled subscription found'
                ], 400);
            }

            $this->stripeService->resumeSubscription($subscription->stripe_subscription_id);

            return response()->json([
                'success' => true,
                'message' => 'Subscription resumed successfully'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to resume subscription',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change subscription plan
     */
    public function changePlan(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string',
                'new_plan_id' => 'required|exists:subscription_plans,id'
            ]);

            $user = User::where('username', $request->username)->firstOrFail();
            $subscription = $user->activeSubscription;
            $newPlan = SubscriptionPlan::findOrFail($request->new_plan_id);

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription found'
                ], 400);
            }

            if ($subscription->subscription_plan_id == $newPlan->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already on this plan'
                ], 400);
            }

            $this->stripeService->changeSubscriptionPlan(
                $subscription->stripe_subscription_id, 
                $newPlan
            );

            return response()->json([
                'success' => true,
                'message' => 'Subscription plan changed successfully'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to change subscription plan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get upcoming invoice
     */
    public function getUpcomingInvoice(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string'
            ]);

            $user = User::where('username', $request->username)->firstOrFail();

            if (!$user->stripe_customer_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'User has no Stripe customer ID'
                ], 400);
            }

            $invoice = $this->stripeService->getUpcomingInvoice($user->stripe_customer_id);

            return response()->json([
                'success' => true,
                'data' => $invoice ? [
                    'amount_due' => $invoice->amount_due,
                    'currency' => $invoice->currency,
                    'period_start' => $invoice->period_start,
                    'period_end' => $invoice->period_end,
                ] : null
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch upcoming invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}