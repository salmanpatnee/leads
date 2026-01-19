<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;
use Illuminate\Http\JsonResponse;

class LeadController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(StoreLeadRequest $request): JsonResponse
    {
        try {
            $lead = Lead::create([
                'site_id' => $request->input('site')->id,
                'form_name' => $request->input('form_name'),
                'form_data' => $request->input('form_data'),
                'status' => 'new',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'submitted_at' => $request->input('submitted_at', now()),
            ]);

            return response()->json([
                'success' => true,
                'lead_id' => $lead->id,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to store lead.',
            ], 500);
        }
    }
}
