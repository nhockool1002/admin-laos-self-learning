<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Note: These IDs should match your Stripe Price IDs in production
        $plans = [
            [
                'id' => 'price_basic_monthly', // Replace with actual Stripe Price ID
                'name' => 'Gói Cơ Bản - Tháng',
                'description' => 'Truy cập khóa học cơ bản và tính năng theo dõi tiến độ',
                'price' => 99000, // 99,000 VND
                'currency' => 'VND',
                'billing_interval' => 'month',
                'stripe_product_id' => 'prod_basic', // Replace with actual Stripe Product ID
                'features' => [
                    'basic_courses',
                    'progress_tracking',
                    'basic_badges',
                    'email_support'
                ],
                'is_active' => true,
            ],
            [
                'id' => 'price_premium_monthly', // Replace with actual Stripe Price ID
                'name' => 'Gói Premium - Tháng',
                'description' => 'Truy cập đầy đủ tất cả khóa học và tính năng premium',
                'price' => 199000, // 199,000 VND
                'currency' => 'VND',
                'billing_interval' => 'month',
                'stripe_product_id' => 'prod_premium', // Replace with actual Stripe Product ID
                'features' => [
                    'all_courses',
                    'progress_tracking',
                    'all_badges',
                    'video_downloads',
                    'priority_support',
                    'premium_games',
                    'advanced_analytics',
                    'certificate_generation'
                ],
                'is_active' => true,
            ],
            [
                'id' => 'price_premium_yearly', // Replace with actual Stripe Price ID
                'name' => 'Gói Premium - Năm',
                'description' => 'Truy cập đầy đủ với giảm giá 20%',
                'price' => 1900000, // 1,900,000 VND (save 490,000 VND)
                'currency' => 'VND',
                'billing_interval' => 'year',
                'stripe_product_id' => 'prod_premium', // Same product, different price
                'features' => [
                    'all_courses',
                    'progress_tracking',
                    'all_badges',
                    'video_downloads',
                    'priority_support',
                    'premium_games',
                    'advanced_analytics',
                    'certificate_generation'
                ],
                'is_active' => true,
            ],
            [
                'id' => 'price_enterprise_monthly', // Replace with actual Stripe Price ID
                'name' => 'Gói Doanh Nghiệp - Tháng',
                'description' => 'Giải pháp cho doanh nghiệp với tính năng quản lý nhóm',
                'price' => 499000, // 499,000 VND
                'currency' => 'VND',
                'billing_interval' => 'month',
                'stripe_product_id' => 'prod_enterprise', // Replace with actual Stripe Product ID
                'features' => [
                    'all_courses',
                    'progress_tracking',
                    'all_badges',
                    'video_downloads',
                    'priority_support',
                    'premium_games',
                    'advanced_analytics',
                    'certificate_generation',
                    'team_management',
                    'bulk_user_creation',
                    'custom_branding',
                    'api_access',
                    'dedicated_support'
                ],
                'is_active' => true,
            ],
        ];

        foreach ($plans as $planData) {
            SubscriptionPlan::updateOrCreate(
                ['id' => $planData['id']], 
                $planData
            );
        }

        $this->command->info('Subscription plans seeded successfully!');
    }
}