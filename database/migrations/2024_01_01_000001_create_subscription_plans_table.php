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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Monthly, Yearly
            $table->string('slug')->unique(); // monthly, yearly
            $table->string('stripe_price_id')->unique();
            $table->decimal('price', 8, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('billing_period', ['month', 'year']);
            $table->text('description')->nullable();
            $table->json('features')->nullable(); // JSON array of features
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};