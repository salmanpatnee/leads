<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadControllerSimpleTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_leads_as_json(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create();
        Lead::factory()->count(5)->create(['site_id' => $site->id]);

        $response = $this->actingAs($user)->getJson('/leads');

        $response->assertStatus(200);
    }
}
