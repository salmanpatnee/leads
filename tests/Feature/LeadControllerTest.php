<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_access_leads_index_without_filters(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/leads');

        $response->assertStatus(200);
    }

    public function test_can_access_leads_with_site_created(): void
    {
        $user = User::factory()->create();
        $site = Site::factory()->create();

        $response = $this->actingAs($user)->get('/leads');

        $response->assertOk();
    }
}
