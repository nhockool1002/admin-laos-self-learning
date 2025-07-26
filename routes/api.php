<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\WebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Subscription Management Routes
Route::prefix('v1/subscriptions')->group(function () {
    // Get subscription plans
    Route::get('/plans', [SubscriptionController::class, 'getPlans']);
    
    // Create checkout session
    Route::post('/checkout', [SubscriptionController::class, 'createCheckoutSession']);
    
    // Get user subscription status
    Route::get('/user/{username}', [SubscriptionController::class, 'getUserSubscription']);
    
    // Cancel subscription
    Route::post('/cancel', [SubscriptionController::class, 'cancelSubscription']);
    
    // Resume subscription
    Route::post('/resume', [SubscriptionController::class, 'resumeSubscription']);
    
    // Change subscription plan
    Route::post('/change-plan', [SubscriptionController::class, 'changePlan']);
    
    // Get upcoming invoice
    Route::get('/invoice/{username}', [SubscriptionController::class, 'getUpcomingInvoice']);
});

// Stripe Webhooks (no CSRF protection needed)
Route::post('/webhooks/stripe', [WebhookController::class, 'handleStripeWebhook'])
     ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Premium Content Routes (require active subscription)
Route::middleware([\App\Http\Middleware\CheckSubscription::class])->group(function () {
    // Premium video courses
    Route::get('/v1/premium/courses', function () {
        return response()->json([
            'success' => true,
            'message' => 'Welcome to premium content!',
            'data' => ['premium_courses' => 'Available to premium members only']
        ]);
    });
    
    // Premium lessons
    Route::get('/v1/premium/lessons/{id}', function ($id) {
        return response()->json([
            'success' => true,
            'message' => 'Premium lesson access granted',
            'data' => ['lesson_id' => $id, 'premium_content' => true]
        ]);
    });
});