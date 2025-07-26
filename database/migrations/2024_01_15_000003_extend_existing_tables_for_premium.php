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
        // Extend video_courses table for premium content
        Schema::table('video_courses', function (Blueprint $table) {
            $table->boolean('is_premium')->default(false)->after('category');
            $table->string('required_plan_id')->nullable()->after('is_premium');
            $table->index('is_premium');
            
            $table->foreign('required_plan_id')->references('id')->on('subscription_plans');
        });

        // Extend flash_games table for premium content
        Schema::table('flash_games', function (Blueprint $table) {
            $table->boolean('is_premium')->default(false)->after('thumbnail');
            $table->string('required_plan_id')->nullable()->after('is_premium');
            $table->index('is_premium');
            
            $table->foreign('required_plan_id')->references('id')->on('subscription_plans');
        });

        // Extend badges_system table for premium badges
        Schema::table('badges_system', function (Blueprint $table) {
            $table->boolean('is_premium')->default(false)->after('condition');
            $table->string('required_plan_id')->nullable()->after('is_premium');
            $table->index('is_premium');
            
            $table->foreign('required_plan_id')->references('id')->on('subscription_plans');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('video_courses', function (Blueprint $table) {
            $table->dropForeign(['required_plan_id']);
            $table->dropColumn(['is_premium', 'required_plan_id']);
        });

        Schema::table('flash_games', function (Blueprint $table) {
            $table->dropForeign(['required_plan_id']);
            $table->dropColumn(['is_premium', 'required_plan_id']);
        });

        Schema::table('badges_system', function (Blueprint $table) {
            $table->dropForeign(['required_plan_id']);
            $table->dropColumn(['is_premium', 'required_plan_id']);
        });
    }
};