<?php

namespace Tests\Feature;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_leads_with_pagination(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create();
        Lead::factory()->count(20)->create(['site_id' => $site->id]);

        $response = $this->actingAs($user)->get('/leads');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('leads/Index')
            ->has('leads.data', 15) // Default pagination is 15
            ->has('sites')
        );
    }

    public function test_can_filter_leads_by_site(): void
    {
        $user = User::factory()->create();
        $site1 = Site::factory()->create();
        $site2 = Site::factory()->create();

        Lead::factory()->count(5)->create(['site_id' => $site1->id]);
        Lead::factory()->count(3)->create(['site_id' => $site2->id]);

        $response = $this->actingAs($user)->get("/leads?site_id={$site1->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('leads/Index')
            ->has('leads.data', 5)
            ->where('filters.site_id', (string) $site1->id)
        );
    }

    public function test_can_filter_leads_by_status(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create();

        Lead::factory()->count(3)->create([
            'site_id' => $site->id,
            'status' => LeadStatus::New,
        ]);
        Lead::factory()->count(2)->create([
            'site_id' => $site->id,
            'status' => LeadStatus::Contacted,
        ]);

        $response = $this->actingAs($user)->get('/leads?status=contacted');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('leads/Index')
            ->has('leads.data', 2)
            ->where('filters.status', 'contacted')
        );
    }

    public function test_can_search_leads_in_form_data(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create();

        Lead::factory()->create([
            'site_id' => $site->id,
            'form_data' => ['name' => 'John Doe', 'email' => 'john@example.com'],
        ]);
        Lead::factory()->create([
            'site_id' => $site->id,
            'form_data' => ['name' => 'Jane Smith', 'email' => 'jane@example.com'],
        ]);

        $response = $this->actingAs($user)->get('/leads?search=John');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('leads/Index')
            ->has('leads.data', 1)
            ->where('filters.search', 'John')
        );
    }

    public function test_can_view_lead_detail(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create();
        $lead = Lead::factory()->create(['site_id' => $site->id]);

        $response = $this->actingAs($user)->get("/leads/{$lead->id}");

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('leads/Show')
            ->where('lead.id', $lead->id)
        );
    }
}
