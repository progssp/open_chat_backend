<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use Closure;

class Authenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        if ($request->cookie("token")) {
            Log::info("cookie found in test: " . $request->cookie("token"));
            $request->headers->set(
                "Authorization",
                "Bearer " . $request->cookie("token"),
            );
        } elseif ($request->header("Authorization")) {
        } else {
            Log::info("no cookie found from test");
            $this->redirectTo($request);
            // return response()->json(['status'=>false,'msg'=>'not an authenticated user!']);
        }
        $this->authenticate($request, $guards);

        return $next($request);
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        // print_r($_SERVER);
        // die();
        if (!$request->expectsJson()) {
            return route("login");
        }
    }
}
