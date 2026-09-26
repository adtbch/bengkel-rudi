<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request;
class AdminJwt { public function handle(Request $request, Closure $next){
 try { $token=$request->cookie('admin_token'); if(!$token) throw new \RuntimeException; auth('admin')->setToken($token); $user=auth('admin')->user(); if(!$user || !$user->is_active || !in_array($user->role,['ADMIN','SUPER_ADMIN'],true)) throw new \RuntimeException; }
 catch(\Throwable){ return redirect('/admin/login')->withCookie(cookie()->forget('admin_token')); }
 return $next($request);
} }
