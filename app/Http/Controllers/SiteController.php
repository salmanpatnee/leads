<?php

namespace App\Http\Controllers;

use App\Http\Requests\Sites\StoreSiteRequest;
use App\Http\Requests\Sites\UpdateSiteRequest;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SiteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $query = Site::query();

        // Search by site_name or domain
        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('site_name', 'like', "%{$search}%")
                    ->orWhere('domain', 'like', "%{$search}%");
            });
        }

        // Filter by is_active status
        if (request()->has('is_active') && request('is_active') !== '') {
            $query->where('is_active', (bool) request('is_active'));
        }

        // Paginate results (default 15 per page)
        $sites = $query->latest()
            ->paginate(request('per_page', 15))
            ->withQueryString();

        return Inertia::render('sites/Index', [
            'sites' => $sites,
            'filters' => [
                'search' => request('search'),
                'is_active' => request('is_active'),
                'per_page' => request('per_page', 15),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('sites/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSiteRequest $request): RedirectResponse
    {
        $site = Site::create($request->validated());

        return redirect()->route('sites.show', $site)
            ->with('success', 'Site created successfully.')
            ->with('api_key', $site->api_key);
    }

    /**
     * Display the specified resource.
     */
    public function show(Site $site): Response
    {
        return Inertia::render('sites/Show', [
            'site' => $site,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Site $site): Response
    {
        return Inertia::render('sites/Edit', [
            'site' => $site,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSiteRequest $request, Site $site): RedirectResponse
    {
        $site->update($request->validated());

        return redirect()->route('sites.show', $site)
            ->with('success', 'Site updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Site $site): RedirectResponse
    {
        $site->delete();

        return redirect()->route('sites.index')
            ->with('success', 'Site deleted successfully.');
    }
}
