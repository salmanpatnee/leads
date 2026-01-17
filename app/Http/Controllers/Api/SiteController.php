<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sites\StoreSiteRequest;
use App\Http\Requests\Sites\UpdateSiteRequest;
use App\Http\Resources\SiteResource;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SiteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $sites = Site::latest()->get();

        return SiteResource::collection($sites);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSiteRequest $request): JsonResponse
    {
        $site = Site::create($request->validated());

        return (new SiteResource($site))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Site $site): SiteResource
    {
        return new SiteResource($site);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSiteRequest $request, Site $site): SiteResource
    {
        $site->update($request->validated());

        return new SiteResource($site);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Site $site): JsonResponse
    {
        $site->delete();

        return response()->json(null, 204);
    }
}
