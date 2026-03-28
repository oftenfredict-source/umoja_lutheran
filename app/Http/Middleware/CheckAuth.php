<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAuth
{
    /**
     * Handle an incoming request.
     * Checks authentication for both staff and guest guards
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check both staff and guest guards
        $staff = auth()->guard('staff')->user();
        $guest = auth()->guard('guest')->user();

        \Log::channel('daily')->info('--- Auth Check Middleware ---', [
            'staff_logged_in' => $staff ? $staff->id : 'NO',
            'guest_logged_in' => $guest ? $guest->id : 'NO',
            'session_id' => $request->session()->getId(),
            'url' => $request->fullUrl(),
        ]);

        if (!$staff && !$guest) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
