<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Users;
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
            $admin_token = Users::select('_id')->get();
            $admin_token = data_get($admin_token, '*._id');
            if (in_array($value, $admin_token)) {
                return $next($request);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'You do not have the appropriate role to access this, Please try again!'
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'You do not have the appropriate role to access this, Please try again!'
            ]);
        }
    }
}
