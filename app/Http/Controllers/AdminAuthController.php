<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
class AdminAuthController extends Controller {
 public function show(Request $request){ if(auth('admin')->check()) return redirect('/admin'); return view('admin.login'); }
 public function login(Request $request){
  $data=$request->validate(['email'=>['required','string','email','max:255','not_regex:/[\r\n]/'],'password'=>['required','string','max:255']]);
  $token=auth('admin')->attempt(['email'=>$data['email'],'password'=>$data['password'],'is_active'=>true]);
  if(!$token) return back()->withErrors(['email'=>'Email atau password tidak valid.'])->onlyInput('email');
  return redirect('/admin')->withCookie(cookie('admin_token',$token,auth('admin')->factory()->getTTL(),'/',null,true,true,false,'lax'));
 }
 public function logout(){ try { auth('admin')->logout(true); } catch (\Throwable) {} return redirect('/admin/login')->withCookie(Cookie::forget('admin_token')); }
}
