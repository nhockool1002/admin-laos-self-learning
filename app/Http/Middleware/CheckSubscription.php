<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $username = $request->input('username') ?? $request->route('username');
        
        if (!$username) {
            return response()->json([
                'success' => false,
                'message' => 'Username is required'
            ], 400);
        }

        $user = User::where('username', $username)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        if (!$user->isPremiumMember()) {
            return response()->json([
                'success' => false,
                'message' => 'Premium subscription required',
                'subscription_status' => $user->subscription_status,
                'subscription_ends_at' => $user->subscription_ends_at
            ], 403);
        }

        // Add user to request for use in controller
        $request->merge(['authenticated_user' => $user]);

        return $next($request);
    }
}