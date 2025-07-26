<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class SubscriptionController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $this->middleware('auth');
    }

    /**
     * Display subscription plans
     */
    public function plans()
    {
        $plans = SubscriptionPlan::active()->orderBy('price', 'asc')->get();
        $user = auth()->user();
        $currentSubscription = $user->getCurrentSubscription();
        
        return view('subscription.plans', compact('plans', 'user', 'currentSubscription'));
    }

    /**
     * Create Stripe Checkout Session
     */
    public function createCheckoutSession(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id'
        ]);

        $user = auth()->user();
        $planId = $request->input('plan_id');
        $plan = SubscriptionPlan::findOrFail($planId);

        // Check if user already has an active subscription
        if ($user->hasActiveSubscription()) {
            return redirect()->route('subscription.manage')
                ->with('error', 'Bạn đã có gói đăng ký đang hoạt động. Vui lòng hủy gói hiện tại trước khi đăng ký gói mới.');
        }

        try {
            // Create or retrieve Stripe customer
            if (!$user->stripe_customer_id) {
                $customer = \Stripe\Customer::create([
                    'email' => $user->email,
                    'name' => $user->username,
                    'metadata' => [
                        'username' => $user->username,
                    ],
                ]);
                $user->update(['stripe_customer_id' => $customer->id]);
            }

            // Create Checkout Session
            $session = Session::create([
                'payment_method_types' => ['card'],
                'customer' => $user->stripe_customer_id,
                'line_items' => [[
                    'price' => $plan->id, // This is the Stripe Price ID
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'allow_promotion_codes' => true,
                'billing_address_collection' => 'auto',
                'success_url' => route('subscription.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('subscription.cancel'),
                'metadata' => [
                    'username' => $user->username,
                    'plan_id' => $plan->id,
                ],
            ]);

            return redirect($session->url);

        } catch (\Exception $e) {
            return redirect()->route('subscription.plans')
                ->with('error', 'Có lỗi xảy ra khi tạo phiên thanh toán: ' . $e->getMessage());
        }
    }

    /**
     * Handle successful subscription
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');
        
        if ($sessionId) {
            try {
                $session = \Stripe\Checkout\Session::retrieve($sessionId);
                $user = auth()->user();
                
                return view('subscription.success', compact('session', 'user'));
            } catch (\Exception $e) {
                return view('subscription.success')->with('error', 'Không thể xác minh phiên thanh toán.');
            }
        }

        return view('subscription.success');
    }

    /**
     * Handle canceled subscription
     */
    public function cancel()
    {
        return view('subscription.cancel');
    }

    /**
     * Subscription management page
     */
    public function manage()
    {
        $user = auth()->user();
        $currentSubscription = $user->getCurrentSubscription();
        $paymentHistory = $user->subscriptions()
            ->with('paymentHistory.subscription.plan')
            ->get()
            ->pluck('paymentHistory')
            ->flatten()
            ->sortByDesc('created_at')
            ->take(10);

        return view('subscription.manage', compact('user', 'currentSubscription', 'paymentHistory'));
    }

    /**
     * Create Billing Portal Session
     */
    public function portal()
    {
        $user = auth()->user();
        
        if (!$user->stripe_customer_id) {
            return redirect()->route('subscription.plans')
                ->with('error', 'Bạn cần có gói đăng ký để truy cập cổng thanh toán.');
        }

        try {
            $session = \Stripe\BillingPortal\Session::create([
                'customer' => $user->stripe_customer_id,
                'return_url' => route('subscription.manage'),
            ]);

            return redirect($session->url);

        } catch (\Exception $e) {
            return redirect()->route('subscription.manage')
                ->with('error', 'Không thể tạo phiên cổng thanh toán: ' . $e->getMessage());
        }
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(Request $request)
    {
        $user = auth()->user();
        $subscription = $user->getCurrentSubscription();

        if (!$subscription) {
            return redirect()->route('subscription.manage')
                ->with('error', 'Không tìm thấy gói đăng ký để hủy.');
        }

        try {
            // Cancel subscription at period end
            \Stripe\Subscription::update(
                $subscription->stripe_subscription_id,
                ['cancel_at_period_end' => true]
            );

            $subscription->update(['canceled_at' => now()]);

            return redirect()->route('subscription.manage')
                ->with('success', 'Gói đăng ký của bạn sẽ được hủy vào cuối kỳ thanh toán hiện tại.');

        } catch (\Exception $e) {
            return redirect()->route('subscription.manage')
                ->with('error', 'Có lỗi xảy ra khi hủy gói đăng ký: ' . $e->getMessage());
        }
    }

    /**
     * Resume canceled subscription
     */
    public function resumeSubscription(Request $request)
    {
        $user = auth()->user();
        $subscription = $user->getCurrentSubscription();

        if (!$subscription || !$subscription->canceled_at) {
            return redirect()->route('subscription.manage')
                ->with('error', 'Không có gói đăng ký nào để khôi phục.');
        }

        try {
            // Resume subscription
            \Stripe\Subscription::update(
                $subscription->stripe_subscription_id,
                ['cancel_at_period_end' => false]
            );

            $subscription->update(['canceled_at' => null]);

            return redirect()->route('subscription.manage')
                ->with('success', 'Gói đăng ký của bạn đã được khôi phục thành công.');

        } catch (\Exception $e) {
            return redirect()->route('subscription.manage')
                ->with('error', 'Có lỗi xảy ra khi khôi phục gói đăng ký: ' . $e->getMessage());
        }
    }

    /**
     * Change subscription plan
     */
    public function changePlan(Request $request)
    {
        $request->validate([
            'new_plan_id' => 'required|exists:subscription_plans,id'
        ]);

        $user = auth()->user();
        $currentSubscription = $user->getCurrentSubscription();
        $newPlan = SubscriptionPlan::findOrFail($request->new_plan_id);

        if (!$currentSubscription) {
            return redirect()->route('subscription.plans')
                ->with('error', 'Bạn cần có gói đăng ký hiện tại để thay đổi.');
        }

        try {
            // Update subscription plan in Stripe
            $stripeSubscription = \Stripe\Subscription::retrieve($currentSubscription->stripe_subscription_id);
            
            \Stripe\Subscription::update(
                $currentSubscription->stripe_subscription_id,
                [
                    'items' => [
                        [
                            'id' => $stripeSubscription->items->data[0]->id,
                            'price' => $newPlan->id,
                        ],
                    ],
                    'proration_behavior' => 'always_invoice',
                ]
            );

            $currentSubscription->update(['plan_id' => $newPlan->id]);

            return redirect()->route('subscription.manage')
                ->with('success', 'Gói đăng ký của bạn đã được cập nhật thành công.');

        } catch (\Exception $e) {
            return redirect()->route('subscription.manage')
                ->with('error', 'Có lỗi xảy ra khi thay đổi gói: ' . $e->getMessage());
        }
    }
}