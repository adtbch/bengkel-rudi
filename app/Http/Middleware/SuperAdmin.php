<?php
namespace App\Http\Middleware;
use Closure; use Illuminate\Http\Request;
class SuperAdmin { public function handle(Request $request, Closure $next){ abort_unless(auth('admin')->user()?->role==='SUPER_ADMIN',403); return $next($request); } }
