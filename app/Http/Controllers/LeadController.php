<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Site;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Lead::query()->with('site');

        // Apply filters
        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('submitted_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('submitted_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('form_data', 'LIKE', '%'.$searchTerm.'%')
                    ->orWhere('form_name', 'LIKE', '%'.$searchTerm.'%');
            });
        }

        $leads = $query->orderBy('submitted_at', 'desc')->paginate(15)->withQueryString();

        $sites = Site::orderBy('site_name')->get(['id', 'site_name'])->map(function ($site) {
            return [
                'id' => $site->id,
                'name' => $site->site_name,
            ];
        })->toArray();

        return inertia('leads/Index', [
            'leads' => $leads,
            'filters' => $request->only(['search', 'site_id', 'status', 'date_from', 'date_to']),
            'sites' => $sites,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Lead $lead)
    {
        return inertia('leads/Show', [
            'lead' => $lead->load('site'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:new,contacted,converted',
        ]);

        $lead->update([
            'status' => $validated['status'],
        ]);

        return redirect()->back()->with('success', 'Lead status updated successfully.');
    }
}
