<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserSubscription;
use App\Models\PaymentHistory;
use Illuminate\Http\Request;
use Stripe\Webhook;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid payload in webhook', ['error' => $e->getMessage()]);
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Invalid signature in webhook', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        }

        Log::info('Stripe webhook received', ['type' => $event['type'], 'id' => $event['id']]);

        // Handle the event
        try {
            switch ($event['type']) {
                case 'customer.subscription.created':
                    $this->handleSubscriptionCreated($event['data']['object']);
                    break;
                case 'customer.subscription.updated':
                    $this->handleSubscriptionUpdated($event['data']['object']);
                    break;
                case 'customer.subscription.deleted':
                    $this->handleSubscriptionDeleted($event['data']['object']);
                    break;
                case 'invoice.payment_succeeded':
                    $this->handlePaymentSucceeded($event['data']['object']);
                    break;
                case 'invoice.payment_failed':
                    $this->handlePaymentFailed($event['data']['object']);
                    break;
                case 'customer.subscription.trial_will_end':
                    $this->handleTrialWillEnd($event['data']['object']);
                    break;
                default:
                    Log::info('Unhandled webhook event type', ['type' => $event['type']]);
            }
        } catch (\Exception $e) {
            Log::error('Error processing webhook', [
                'type' => $event['type'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response('Webhook handler failed', 500);
        }

        return response('Success', 200);
    }

    private function handleSubscriptionCreated($subscription)
    {
        $user = User::where('stripe_customer_id', $subscription['customer'])->first();
        if (!$user) {
            Log::warning('User not found for subscription created', ['customer_id' => $subscription['customer']]);
            return;
        }

        $planId = $subscription['items']['data'][0]['price']['id'];

        UserSubscription::create([
            'username' => $user->username,
            'stripe_customer_id' => $subscription['customer'],
            'stripe_subscription_id' => $subscription['id'],
            'plan_id' => $planId,
            'status' => $subscription['status'],
            'current_period_start' => $this->convertTimestamp($subscription['current_period_start']),
            'current_period_end' => $this->convertTimestamp($subscription['current_period_end']),
            'trial_end' => $subscription['trial_end'] ? $this->convertTimestamp($subscription['trial_end']) : null,
        ]);

        $user->update([
            'subscription_status' => $subscription['status'],
            'subscription_ends_at' => $this->convertTimestamp($subscription['current_period_end']),
        ]);

        Log::info('Subscription created', [
            'username' => $user->username,
            'subscription_id' => $subscription['id'],
            'plan_id' => $planId
        ]);
    }

    private function handleSubscriptionUpdated($subscription)
    {
        $userSub = UserSubscription::where('stripe_subscription_id', $subscription['id'])->first();
        if (!$userSub) {
            Log::warning('Subscription not found for update', ['subscription_id' => $subscription['id']]);
            return;
        }

        $planId = $subscription['items']['data'][0]['price']['id'];

        $userSub->update([
            'plan_id' => $planId,
            'status' => $subscription['status'],
            'current_period_start' => $this->convertTimestamp($subscription['current_period_start']),
            'current_period_end' => $this->convertTimestamp($subscription['current_period_end']),
            'trial_end' => $subscription['trial_end'] ? $this->convertTimestamp($subscription['trial_end']) : null,
            'canceled_at' => $subscription['canceled_at'] ? $this->convertTimestamp($subscription['canceled_at']) : null,
        ]);

        $userSub->user->update([
            'subscription_status' => $subscription['status'],
            'subscription_ends_at' => $this->convertTimestamp($subscription['current_period_end']),
        ]);

        Log::info('Subscription updated', [
            'username' => $userSub->username,
            'subscription_id' => $subscription['id'],
            'status' => $subscription['status'],
            'plan_id' => $planId
        ]);
    }

    private function handleSubscriptionDeleted($subscription)
    {
        $userSub = UserSubscription::where('stripe_subscription_id', $subscription['id'])->first();
        if (!$userSub) {
            Log::warning('Subscription not found for deletion', ['subscription_id' => $subscription['id']]);
            return;
        }

        $userSub->update([
            'status' => 'canceled',
            'ended_at' => now(),
        ]);

        $userSub->user->update([
            'subscription_status' => 'canceled',
        ]);

        Log::info('Subscription deleted', [
            'username' => $userSub->username,
            'subscription_id' => $subscription['id']
        ]);
    }

    private function handlePaymentSucceeded($invoice)
    {
        $user = User::where('stripe_customer_id', $invoice['customer'])->first();
        if (!$user) {
            Log::warning('User not found for payment succeeded', ['customer_id' => $invoice['customer']]);
            return;
        }

        $subscription = null;
        if ($invoice['subscription']) {
            $subscription = UserSubscription::where('stripe_subscription_id', $invoice['subscription'])->first();
        }

        PaymentHistory::create([
            'username' => $user->username,
            'subscription_id' => $subscription?->id,
            'stripe_payment_intent_id' => $invoice['payment_intent'],
            'stripe_invoice_id' => $invoice['id'],
            'amount' => $invoice['amount_paid'] / 100, // Convert from cents
            'currency' => strtoupper($invoice['currency']),
            'status' => 'succeeded',
            'payment_method' => 'card', // Default to card, could be enhanced
            'description' => $invoice['description'] ?? 'Subscription payment',
            'paid_at' => $this->convertTimestamp($invoice['status_transitions']['paid_at']),
        ]);

        Log::info('Payment succeeded', [
            'username' => $user->username,
            'invoice_id' => $invoice['id'],
            'amount' => $invoice['amount_paid'] / 100
        ]);
    }

    private function handlePaymentFailed($invoice)
    {
        $user = User::where('stripe_customer_id', $invoice['customer'])->first();
        if (!$user) {
            Log::warning('User not found for payment failed', ['customer_id' => $invoice['customer']]);
            return;
        }

        $subscription = null;
        if ($invoice['subscription']) {
            $subscription = UserSubscription::where('stripe_subscription_id', $invoice['subscription'])->first();
        }

        PaymentHistory::create([
            'username' => $user->username,
            'subscription_id' => $subscription?->id,
            'stripe_payment_intent_id' => $invoice['payment_intent'],
            'stripe_invoice_id' => $invoice['id'],
            'amount' => $invoice['amount_due'] / 100, // Convert from cents
            'currency' => strtoupper($invoice['currency']),
            'status' => 'failed',
            'payment_method' => 'card',
            'description' => $invoice['description'] ?? 'Failed subscription payment',
            'paid_at' => null,
        ]);

        Log::warning('Payment failed', [
            'username' => $user->username,
            'invoice_id' => $invoice['id'],
            'amount' => $invoice['amount_due'] / 100
        ]);

        // You might want to send an email notification here
        // Or update the user's subscription status if needed
    }

    private function handleTrialWillEnd($subscription)
    {
        $userSub = UserSubscription::where('stripe_subscription_id', $subscription['id'])->first();
        if (!$userSub) {
            Log::warning('Subscription not found for trial will end', ['subscription_id' => $subscription['id']]);
            return;
        }

        Log::info('Trial will end notification', [
            'username' => $userSub->username,
            'subscription_id' => $subscription['id'],
            'trial_end' => $subscription['trial_end']
        ]);

        // You might want to send an email notification here
        // to inform the user that their trial is about to end
    }

    private function convertTimestamp($timestamp)
    {
        return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
    }
}