<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(): View
    {
        return view('services.index', [
            'services' => Service::query()
                ->withCount('appointments')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:services,name'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
        ]);

        Service::query()->create([...$attributes, 'is_active' => $request->boolean('is_active')]);

        return redirect()->route('services.index')->with('success', 'Serviço cadastrado.');
    }

    public function update(Request $request, Service $service): RedirectResponse
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:services,name,'.$service->id],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:1440'],
        ]);

        $service->update([...$attributes, 'is_active' => $request->boolean('is_active')]);

        return redirect()->route('services.index')->with('success', 'Serviço atualizado.');
    }
}
