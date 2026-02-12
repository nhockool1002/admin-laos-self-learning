<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StripeService;
use App\Models\UserSubscription;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;
use Exception;

class WebhookController extends Controller
{
    protected $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }

    /**
     * Handle Stripe webhooks
     */
    public function handleStripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            \Log::error('Stripe webhook invalid payload: ' . $e->getMessage());
            return response('Invalid payload', 400);
        } catch (SignatureVerificationException $e) {
            // Invalid signature
            \Log::error('Stripe webhook signature verification failed: ' . $e->getMessage());
            return response('Invalid signature', 400);
        }

        // Handle the event
        try {
            switch ($event->type) {
                case 'customer.subscription.created':
                    $this->handleSubscriptionCreated($event->data->object);
                    break;

                case 'customer.subscription.updated':
                    $this->handleSubscriptionUpdated($event->data->object);
                    break;

                case 'customer.subscription.deleted':
                    $this->handleSubscriptionDeleted($event->data->object);
                    break;

                case 'invoice.payment_succeeded':
                    $this->handlePaymentSucceeded($event->data->object);
                    break;

                case 'invoice.payment_failed':
                    $this->handlePaymentFailed($event->data->object);
                    break;

                case 'customer.subscription.trial_will_end':
                    $this->handleTrialWillEnd($event->data->object);
                    break;

                default:
                    \Log::info('Unhandled Stripe webhook event: ' . $event->type);
            }

            return response('Webhook handled successfully', 200);

        } catch (Exception $e) {
            \Log::error('Error handling Stripe webhook: ' . $e->getMessage(), [
                'event_type' => $event->type,
                'event_id' => $event->id,
                'error' => $e->getTraceAsString()
            ]);
            
            return response('Webhook handling failed', 500);
        }
    }

    /**
     * Handle subscription created event
     */
    private function handleSubscriptionCreated($subscription)
    {
        \Log::info('Processing subscription created webhook', ['subscription_id' => $subscription->id]);
        
        $this->stripeService->handleSubscriptionCreated($subscription);
    }

    /**
     * Handle subscription updated event
     */
    private function handleSubscriptionUpdated($subscription)
    {
        \Log::info('Processing subscription updated webhook', ['subscription_id' => $subscription->id]);
        
        $userSubscription = UserSubscription::where('stripe_subscription_id', $subscription->id)->first();
        
        if ($userSubscription) {
            $userSubscription->update([
                'status' => $subscription->status,
                'current_period_start' => $subscription->current_period_start ? 
                    date('Y-m-d H:i:s', $subscription->current_period_start) : null,
                'current_period_end' => $subscription->current_period_end ? 
                    date('Y-m-d H:i:s', $subscription->current_period_end) : null,
                'trial_end' => $subscription->trial_end ? 
                    date('Y-m-d H:i:s', $subscription->trial_end) : null,
            ]);

            // Update user subscription status
            $userSubscription->user->update([
                'subscription_status' => $subscription->status === 'active' ? 'active' : 'inactive',
                'subscription_ends_at' => $subscription->current_period_end ? 
                    date('Y-m-d H:i:s', $subscription->current_period_end) : null,
            ]);
        }
    }

    /**
     * Handle subscription deleted event
     */
    private function handleSubscriptionDeleted($subscription)
    {
        \Log::info('Processing subscription deleted webhook', ['subscription_id' => $subscription->id]);
        
        $userSubscription = UserSubscription::where('stripe_subscription_id', $subscription->id)->first();
        
        if ($userSubscription) {
            $userSubscription->update([
                'status' => 'canceled',
                'ended_at' => now(),
            ]);

            $userSubscription->user->update([
                'subscription_status' => 'canceled',
            ]);
        }
    }

    /**
     * Handle payment succeeded event
     */
    private function handlePaymentSucceeded($invoice)
    {
        \Log::info('Processing payment succeeded webhook', ['invoice_id' => $invoice->id]);
        
        if ($invoice->subscription) {
            $userSubscription = UserSubscription::where('stripe_subscription_id', $invoice->subscription)->first();
            
            if ($userSubscription && $userSubscription->status !== 'active') {
                $userSubscription->update(['status' => 'active']);
                $userSubscription->user->update(['subscription_status' => 'active']);
            }
        }
    }

    /**
     * Handle payment failed event
     */
    private function handlePaymentFailed($invoice)
    {
        \Log::info('Processing payment failed webhook', ['invoice_id' => $invoice->id]);
        
        if ($invoice->subscription) {
            $userSubscription = UserSubscription::where('stripe_subscription_id', $invoice->subscription)->first();
            
            if ($userSubscription) {
                $userSubscription->update(['status' => 'past_due']);
                $userSubscription->user->update(['subscription_status' => 'inactive']);
            }
        }
    }

    /**
     * Handle trial will end event
     */
    private function handleTrialWillEnd($subscription)
    {
        \Log::info('Processing trial will end webhook', ['subscription_id' => $subscription->id]);
        
        // You can send notifications to user about trial ending
        $userSubscription = UserSubscription::where('stripe_subscription_id', $subscription->id)->first();
        
        if ($userSubscription) {
            // Send email notification or other actions
            \Log::info('Trial will end for user: ' . $userSubscription->username);
        }
    }
}