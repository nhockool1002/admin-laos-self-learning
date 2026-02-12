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
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans');
            $table->string('stripe_subscription_id')->unique();
            $table->string('stripe_customer_id');
            $table->enum('status', ['active', 'inactive', 'canceled', 'past_due', 'unpaid']);
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('trial_end')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->foreign('username')->references('username')->on('users')->onDelete('cascade');
            $table->index(['username', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};