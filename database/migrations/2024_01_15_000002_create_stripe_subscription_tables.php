<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Subscription Plans Table
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->string('id')->primary(); // Stripe Price ID
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('billing_interval', ['month', 'year']);
            $table->string('stripe_product_id'); // Stripe Product ID
            $table->json('features')->nullable(); // List of features included in this plan
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. User Subscriptions Table
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('username');
            $table->string('stripe_customer_id');
            $table->string('stripe_subscription_id')->unique()->nullable();
            $table->string('plan_id');
            $table->enum('status', [
                'incomplete', 'incomplete_expired', 'trialing', 'active', 
                'past_due', 'canceled', 'unpaid'
            ]);
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('trial_end')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->foreign('username')->references('username')->on('users')->onDelete('cascade');
            $table->foreign('plan_id')->references('id')->on('subscription_plans');
            
            $table->index('username');
            $table->index('stripe_customer_id');
            $table->index('status');
        });

        // 3. Payment History Table
        Schema::create('payment_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('username');
            $table->uuid('subscription_id')->nullable();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->string('stripe_invoice_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'succeeded', 'failed', 'canceled', 'refunded']);
            $table->string('payment_method')->nullable(); // card, bank_transfer, etc.
            $table->text('description')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('username')->references('username')->on('users')->onDelete('cascade');
            $table->foreign('subscription_id')->references('id')->on('user_subscriptions');
            
            $table->index('username');
            $table->index('status');
            $table->index('paid_at');
        });

        // 4. Content Access Control Table
        Schema::create('content_access', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('content_type', ['course', 'lesson', 'game', 'badge']);
            $table->string('content_id'); // course_id, lesson_id, game_id, badge_id
            $table->string('required_plan_id')->nullable(); // NULL means free content
            $table->boolean('is_premium')->default(false);
            $table->timestamps();

            $table->foreign('required_plan_id')->references('id')->on('subscription_plans');
            
            $table->unique(['content_type', 'content_id']);
            $table->index(['content_type', 'content_id']);
            $table->index('is_premium');
        });

        // 5. User Plan Features Table (Track feature usage)
        Schema::create('user_plan_features', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('username');
            $table->uuid('subscription_id')->nullable();
            $table->string('feature_name'); // e.g., 'video_downloads', 'premium_courses', 'badges_unlimited'
            $table->integer('usage_count')->default(0);
            $table->integer('usage_limit')->nullable(); // NULL means unlimited
            $table->enum('reset_period', ['daily', 'weekly', 'monthly', 'never'])->default('monthly');
            $table->timestamp('last_reset_at')->useCurrent();
            $table->timestamps();

            $table->foreign('username')->references('username')->on('users')->onDelete('cascade');
            $table->foreign('subscription_id')->references('id')->on('user_subscriptions');
            
            $table->unique(['username', 'feature_name']);
            $table->index('username');
            $table->index('subscription_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_plan_features');
        Schema::dropIfExists('content_access');
        Schema::dropIfExists('payment_history');
        Schema::dropIfExists('user_subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};