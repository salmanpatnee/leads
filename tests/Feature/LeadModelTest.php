<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_query_leads_from_database(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create();
        $lead = Lead::factory()->create(['site_id' => $site->id]);

        $this->actingAs($user);

        $leads = Lead::all();

        $this->assertCount(1, $leads);
        $this->assertEquals($lead->id, $leads->first()->id);
    }
}
