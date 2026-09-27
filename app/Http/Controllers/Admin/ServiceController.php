<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Support\UniqueSlug;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        return view('admin.services', ['services' => Service::orderBy('sort_order')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = UniqueSlug::make($data['name'], Service::class);
        Service::create($data);

        return redirect('/admin/layanan');
    }

    public function update(Request $request, Service $service)
    {
        $data = $this->validated($request);
        $data['slug'] = UniqueSlug::make($data['name'], Service::class, $service->id);
        $service->update($data);

        return redirect('/admin/layanan');
    }

    public function toggle(Service $service)
    {
        $service->update(['is_active' => !$service->is_active]);

        return redirect('/admin/layanan');
    }

    public function destroy(Service $service)
    {
        if ($service->portfolios()->exists()) {
            return back()->withErrors(['service' => 'Layanan masih digunakan portfolio.']);
        }

        $service->delete();

        return redirect('/admin/layanan');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'description' => 'required|string|min:10|max:5000',
            'min_price' => 'nullable|integer|min:0',
            'max_price' => 'nullable|integer|min:0|gte:min_price',
            'features' => 'required|string|max:5000',
            'is_active' => 'nullable|boolean',
            'sort_order' => 'required|integer|min:0',
        ]);
        $data['features'] = array_values(array_filter(array_map('trim', preg_split('/\R/', $data['features']))));
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
