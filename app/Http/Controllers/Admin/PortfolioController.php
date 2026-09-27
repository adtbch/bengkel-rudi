<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use App\Models\Service;
use App\Support\UniqueSlug;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PortfolioController extends Controller
{
    public function index()
    {
        return view('admin.portfolios', [
            'portfolios' => Portfolio::with(['service', 'images'])->latest()->get(),
            'services' => Service::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = UniqueSlug::make($data['title'], Portfolio::class);
        Portfolio::create($data);

        return redirect('/admin/portfolio');
    }

    public function update(Request $request, Portfolio $portfolio)
    {
        $data = $this->validated($request);
        $data['slug'] = UniqueSlug::make($data['title'], Portfolio::class, $portfolio->id);
        $portfolio->update($data);

        return redirect('/admin/portfolio');
    }

    public function toggle(Portfolio $portfolio)
    {
        $portfolio->update(['is_published' => !$portfolio->is_published]);

        return redirect('/admin/portfolio');
    }

    public function destroy(Portfolio $portfolio)
    {
        if ($portfolio->images()->exists()) {
            return back()->withErrors(['portfolio' => 'Portfolio masih memiliki gambar.']);
        }

        $portfolio->delete();

        return redirect('/admin/portfolio');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => 'required|string|max:160',
            'service_id' => 'required|exists:services,id',
            'vehicle_type' => ['required', Rule::in(['CAR', 'MOTOR'])],
            'is_published' => 'nullable|boolean',
        ]);
        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }
}
