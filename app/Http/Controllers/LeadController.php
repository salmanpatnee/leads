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

        $paginated = $query->orderBy('submitted_at', 'desc')->paginate(15)->withQueryString();

        $sites = Site::orderBy('site_name')->get(['id', 'site_name'])->map(function ($site) {
            return [
                'id' => $site->id,
                'name' => $site->site_name,
            ];
        })->toArray();

        return inertia('leads/Index', [
            'leads' => [
                'data' => $paginated->items(),
                'links' => $paginated->getUrlRange(1, $paginated->lastPage()),
                'meta' => [
                    'current_page' => $paginated->currentPage(),
                    'from' => $paginated->firstItem(),
                    'last_page' => $paginated->lastPage(),
                    'path' => $paginated->path(),
                    'per_page' => $paginated->perPage(),
                    'to' => $paginated->lastItem(),
                    'total' => $paginated->total(),
                ],
            ],
            'filters' => $request->only(['search', 'site_id', 'status', 'date_from', 'date_to']),
            'sites' => $sites,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Lead $lead)
    {
        $lead->load('site');

        // Extract email from form_data
        $email = $this->extractEmail($lead->form_data);

        // Find related leads from the same email
        $relatedLeads = [];
        if ($email) {
            $relatedLeads = Lead::where('id', '!=', $lead->id)
                ->where(function ($query) use ($email) {
                    $query->whereJsonContains('form_data', '"'.$email.'"');
                })
                ->with('site')
                ->orderBy('submitted_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'site_name' => $item->site->site_name,
                        'form_name' => $item->form_name,
                        'status' => $item->status,
                        'submitted_at' => $item->submitted_at,
                    ];
                });
        }

        return inertia('leads/Show', [
            'lead' => $lead,
            'email' => $email,
            'relatedLeads' => $relatedLeads,
        ]);
    }

    /**
     * Extract email from form data.
     */
    private function extractEmail(array $formData): ?string
    {
        $emailKeys = ['email', 'e-mail', 'email_address', 'contact_email', 'sender_email'];

        foreach ($emailKeys as $key) {
            if (isset($formData[$key]) && filter_var($formData[$key], FILTER_VALIDATE_EMAIL)) {
                return $formData[$key];
            }
        }

        // Try to find any valid email in the form data
        foreach ($formData as $value) {
            if (is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                return $value;
            }
        }

        return null;
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
