<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Subscription;
use Stripe\PaymentMethod;
use Stripe\SetupIntent;
use Stripe\Invoice;
use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Exception;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Create or retrieve Stripe customer
     */
    public function createOrGetCustomer(User $user)
    {
        if ($user->stripe_customer_id) {
            try {
                return Customer::retrieve($user->stripe_customer_id);
            } catch (Exception $e) {
                // Customer doesn't exist, create new one
            }
        }

        $customer = Customer::create([
            'email' => $user->email,
            'name' => $user->username,
            'metadata' => [
                'username' => $user->username,
            ],
        ]);

        $user->update(['stripe_customer_id' => $customer->id]);

        return $customer;
    }

    /**
     * Create subscription checkout session
     */
    public function createCheckoutSession(User $user, SubscriptionPlan $plan, $successUrl, $cancelUrl)
    {
        $customer = $this->createOrGetCustomer($user);

        $session = \Stripe\Checkout\Session::create([
            'customer' => $customer->id,
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price' => $plan->stripe_price_id,
                    'quantity' => 1,
                ],
            ],
            'mode' => 'subscription',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'username' => $user->username,
                'plan_id' => $plan->id,
            ],
        ]);

        return $session;
    }

    /**
     * Handle successful subscription creation
     */
    public function handleSubscriptionCreated($stripeSubscription)
    {
        $customer = Customer::retrieve($stripeSubscription->customer);
        $user = User::where('email', $customer->email)->first();

        if (!$user) {
            throw new Exception('User not found for subscription');
        }

        // Find the subscription plan
        $planId = null;
        foreach ($stripeSubscription->items->data as $item) {
            $plan = SubscriptionPlan::where('stripe_price_id', $item->price->id)->first();
            if ($plan) {
                $planId = $plan->id;
                break;
            }
        }

        if (!$planId) {
            throw new Exception('Subscription plan not found');
        }

        // Create or update user subscription
        $userSubscription = UserSubscription::updateOrCreate(
            [
                'username' => $user->username,
                'stripe_subscription_id' => $stripeSubscription->id,
            ],
            [
                'subscription_plan_id' => $planId,
                'stripe_customer_id' => $customer->id,
                'status' => $stripeSubscription->status,
                'current_period_start' => $stripeSubscription->current_period_start ? 
                    date('Y-m-d H:i:s', $stripeSubscription->current_period_start) : null,
                'current_period_end' => $stripeSubscription->current_period_end ? 
                    date('Y-m-d H:i:s', $stripeSubscription->current_period_end) : null,
                'trial_end' => $stripeSubscription->trial_end ? 
                    date('Y-m-d H:i:s', $stripeSubscription->trial_end) : null,
            ]
        );

        // Update user subscription status
        $user->update([
            'subscription_status' => $stripeSubscription->status === 'active' ? 'active' : 'inactive',
            'subscription_ends_at' => $stripeSubscription->current_period_end ? 
                date('Y-m-d H:i:s', $stripeSubscription->current_period_end) : null,
        ]);

        return $userSubscription;
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription($stripeSubscriptionId)
    {
        $subscription = Subscription::retrieve($stripeSubscriptionId);
        $canceledSubscription = $subscription->cancel();

        // Update local database
        $userSubscription = UserSubscription::where('stripe_subscription_id', $stripeSubscriptionId)->first();
        if ($userSubscription) {
            $userSubscription->update([
                'status' => 'canceled',
                'canceled_at' => now(),
                'ended_at' => $canceledSubscription->ended_at ? 
                    date('Y-m-d H:i:s', $canceledSubscription->ended_at) : now(),
            ]);

            $userSubscription->user->update([
                'subscription_status' => 'canceled',
            ]);
        }

        return $canceledSubscription;
    }

    /**
     * Resume subscription
     */
    public function resumeSubscription($stripeSubscriptionId)
    {
        $subscription = Subscription::retrieve($stripeSubscriptionId);
        $resumedSubscription = Subscription::update($stripeSubscriptionId, [
            'cancel_at_period_end' => false,
        ]);

        // Update local database
        $userSubscription = UserSubscription::where('stripe_subscription_id', $stripeSubscriptionId)->first();
        if ($userSubscription) {
            $userSubscription->update([
                'status' => 'active',
                'canceled_at' => null,
                'ended_at' => null,
            ]);

            $userSubscription->user->update([
                'subscription_status' => 'active',
            ]);
        }

        return $resumedSubscription;
    }

    /**
     * Change subscription plan
     */
    public function changeSubscriptionPlan($stripeSubscriptionId, SubscriptionPlan $newPlan)
    {
        $subscription = Subscription::retrieve($stripeSubscriptionId);
        
        $updatedSubscription = Subscription::update($stripeSubscriptionId, [
            'items' => [
                [
                    'id' => $subscription->items->data[0]->id,
                    'price' => $newPlan->stripe_price_id,
                ],
            ],
            'proration_behavior' => 'always_invoice',
        ]);

        // Update local database
        $userSubscription = UserSubscription::where('stripe_subscription_id', $stripeSubscriptionId)->first();
        if ($userSubscription) {
            $userSubscription->update([
                'subscription_plan_id' => $newPlan->id,
            ]);
        }

        return $updatedSubscription;
    }

    /**
     * Get upcoming invoice
     */
    public function getUpcomingInvoice($customerId)
    {
        try {
            return Invoice::upcoming(['customer' => $customerId]);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Sync subscription from Stripe
     */
    public function syncSubscription($stripeSubscriptionId)
    {
        $stripeSubscription = Subscription::retrieve($stripeSubscriptionId);
        return $this->handleSubscriptionCreated($stripeSubscription);
    }
}