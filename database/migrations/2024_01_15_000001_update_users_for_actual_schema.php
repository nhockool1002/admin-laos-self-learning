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
        Schema::table('users', function (Blueprint $table) {
            // Drop the existing id column and add username as primary key
            $table->dropColumn('id');
            $table->string('username')->primary()->after('id');
            
            // Rename name to match your schema
            $table->renameColumn('name', 'username');
            
            // Add missing columns from your schema
            $table->timestamp('createdat')->nullable()->after('email_verified_at');
            $table->boolean('is_admin')->default(false)->after('password');
            
            // Add Stripe-related columns
            $table->string('stripe_customer_id')->nullable()->after('is_admin');
            $table->enum('subscription_status', ['free', 'trialing', 'active', 'past_due', 'canceled'])
                  ->default('free')->after('stripe_customer_id');
            $table->timestamp('subscription_ends_at')->nullable()->after('subscription_status');
            
            // Add indexes
            $table->index('stripe_customer_id');
            $table->index('subscription_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->id()->first();
            $table->renameColumn('username', 'name');
            $table->dropColumn([
                'createdat', 
                'is_admin', 
                'stripe_customer_id', 
                'subscription_status', 
                'subscription_ends_at'
            ]);
        });
    }
};