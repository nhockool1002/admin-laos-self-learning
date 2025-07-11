<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Với sessionStorage, chúng ta sẽ để client-side JavaScript xử lý authentication
        // Middleware này chỉ kiểm tra cơ bản và cho phép request đi qua
        // Việc kiểm tra chi tiết sẽ được thực hiện ở client-side
        
        return $next($request);
    }
}
