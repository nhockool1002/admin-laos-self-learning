<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Monthly Plan
        SubscriptionPlan::create([
            'name' => 'Premium Monthly',
            'slug' => 'monthly',
            'stripe_price_id' => 'price_monthly_premium', // Replace with your actual Stripe price ID
            'price' => 9.99,
            'currency' => 'USD',
            'billing_period' => 'month',
            'description' => 'Premium access with monthly billing',
            'features' => [
                'Access to all premium content',
                'Ad-free experience',
                'Priority support',
                'Download for offline viewing',
                'Access to exclusive courses'
            ],
            'is_active' => true
        ]);

        // Yearly Plan
        SubscriptionPlan::create([
            'name' => 'Premium Yearly',
            'slug' => 'yearly',
            'stripe_price_id' => 'price_yearly_premium', // Replace with your actual Stripe price ID
            'price' => 99.99,
            'currency' => 'USD',
            'billing_period' => 'year',
            'description' => 'Premium access with yearly billing (2 months free)',
            'features' => [
                'Access to all premium content',
                'Ad-free experience',
                'Priority support',
                'Download for offline viewing',
                'Access to exclusive courses',
                '2 months free compared to monthly plan'
            ],
            'is_active' => true
        ]);
    }
}