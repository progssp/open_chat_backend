<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;

class TokenAuthenticate
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
        if($request->cookie('token') != null){
            $token_dec = Controller::decrypt($request->cookie('token'));
            $user = User::where('token',$token_dec)->where('token_valid_till','>=',date('Y-m-d H:i:s'))->first();
            if($user != null){
                $request->merge(['user' => $user ]);
                $request->setUserResolver(function () use ($user) {
                    return $user;
                });
                return $next($request);
            }
            else{
                return response()->json(['status'=>false,'msg'=>'login expired']);
            }
        }
        else{
            return response()->json(['status'=>false,'msg'=>'login invalidated']);
        }
    }
}
