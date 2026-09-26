<?php
namespace App\Http\Controllers;
use App\Models\Portfolio; use App\Models\Service;
class AdminDashboardController extends Controller { public function __invoke(){ return view('admin.dashboard',['portfolioCount'=>Portfolio::count(),'activeServiceCount'=>Service::where('is_active',true)->count(),'newestPortfolios'=>Portfolio::latest()->limit(5)->get()]); } }
