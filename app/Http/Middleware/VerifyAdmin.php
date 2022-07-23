<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class VerifyAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->hasHeader('X-Admin-Token')) {
            $value = $request->header('X-Admin-Token');
            $admin_token = User::find($value);
            if (isset($admin_token)) {
                return $next($request);
            } else {
                return response()->json(
                    [
                        'status' => false,
                        'message' => 'You do not have the appropriate role to access this, Please try again!',
                    ]
                );
            }
        } else {
            return response()->json(
                [
                    'status' => false,
                    'message' => 'You do not have the appropriate role to access this, Please try again!',
                ]
            );
        }
    }
}
