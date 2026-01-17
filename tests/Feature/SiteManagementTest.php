<?php

use App\Models\Site;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

test('can create a new site with valid data', function (): void {
    $siteData = [
        'site_name' => 'Test WordPress Site',
        'domain' => 'example.com',
    ];

    $response = $this->postJson('/api/sites', $siteData);

    $response->assertCreated()
        ->assertJsonStructure([
            'data' => [
                'id',
                'site_name',
                'domain',
                'api_key',
                'is_active',
                'created_at',
                'updated_at',
            ],
        ]);

    $this->assertDatabaseHas('sites', [
        'site_name' => 'Test WordPress Site',
        'domain' => 'example.com',
        'is_active' => true,
    ]);

    $site = Site::where('domain', 'example.com')->first();
    expect($site->api_key)->not->toBeEmpty();
});

test('cannot create a site with duplicate domain', function (): void {
    Site::factory()->create(['domain' => 'existing.com']);

    $siteData = [
        'site_name' => 'Another Site',
        'domain' => 'existing.com',
    ];

    $response = $this->postJson('/api/sites', $siteData);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['domain']);
});

test('site_name is required', function (): void {
    $response = $this->postJson('/api/sites', [
        'domain' => 'test.com',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['site_name']);
});

test('domain is required', function (): void {
    $response = $this->postJson('/api/sites', [
        'site_name' => 'Test Site',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['domain']);
});

test('domain must be a valid format', function (string $invalidDomain): void {
    $response = $this->postJson('/api/sites', [
        'site_name' => 'Test Site',
        'domain' => $invalidDomain,
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['domain']);
})->with([
    'no TLD' => 'dissertationproposal',
    'only TLD' => '.com',
    'trailing dot' => 'example.com.',
    'leading dot' => '.example.com',
    'double dot' => 'example..com',
    'starts with hyphen' => '-example.com',
    'ends with hyphen' => 'example-.com',
    'invalid characters' => 'exam ple.com',
    'underscore' => 'exam_ple.com',
]);

test('domain accepts valid formats', function (string $validDomain): void {
    $response = $this->postJson('/api/sites', [
        'site_name' => 'Test Site',
        'domain' => $validDomain,
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('sites', ['domain' => strtolower($validDomain)]);
})->with([
    'simple domain' => 'example.com',
    'subdomain' => 'blog.example.com',
    'country TLD' => 'example.co.uk',
    'deep subdomain' => 'api.v2.example.org',
    'hyphenated' => 'my-site.com',
    'numbers' => 'site123.com',
    'long TLD' => 'example.museum',
]);

test('domain is normalized to lowercase', function (): void {
    $response = $this->postJson('/api/sites', [
        'site_name' => 'Test Site',
        'domain' => 'EXAMPLE.COM',
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('sites', [
        'domain' => 'example.com',
    ]);
});

test('domain is extracted from full URL', function (string $input, string $expected): void {
    $response = $this->postJson('/api/sites', [
        'site_name' => 'Test Site',
        'domain' => $input,
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('sites', [
        'domain' => $expected,
    ]);
})->with([
    'https with www and path' => ['https://www.theassignmenthelp.co.nz/', 'theassignmenthelp.co.nz'],
    'https with www' => ['https://www.example.com', 'example.com'],
    'http with www' => ['http://www.example.com', 'example.com'],
    'https without www' => ['https://example.com', 'example.com'],
    'with trailing slash' => ['https://example.com/', 'example.com'],
    'with path' => ['https://example.com/some/path', 'example.com'],
    'www without protocol' => ['www.example.com', 'example.com'],
    'bare domain' => ['example.com', 'example.com'],
]);

test('can list all sites', function (): void {
    Site::factory()->count(3)->create();

    $response = $this->getJson('/api/sites');

    $response->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'site_name',
                    'domain',
                    'api_key',
                    'is_active',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
});

test('can retrieve a specific site', function (): void {
    $site = Site::factory()->create([
        'site_name' => 'Specific Site',
        'domain' => 'specific.com',
    ]);

    $response = $this->getJson("/api/sites/{$site->id}");

    $response->assertOk()
        ->assertJson([
            'data' => [
                'id' => $site->id,
                'site_name' => 'Specific Site',
                'domain' => 'specific.com',
            ],
        ]);
});

test('can update a site', function (): void {
    $site = Site::factory()->create([
        'site_name' => 'Old Name',
        'domain' => 'old.com',
        'is_active' => true,
    ]);

    $response = $this->putJson("/api/sites/{$site->id}", [
        'site_name' => 'Updated Name',
        'is_active' => false,
    ]);

    $response->assertOk()
        ->assertJson([
            'data' => [
                'id' => $site->id,
                'site_name' => 'Updated Name',
                'is_active' => false,
            ],
        ]);

    $this->assertDatabaseHas('sites', [
        'id' => $site->id,
        'site_name' => 'Updated Name',
        'is_active' => false,
    ]);
});

test('can update site status to inactive', function (): void {
    $site = Site::factory()->create(['is_active' => true]);

    $response = $this->putJson("/api/sites/{$site->id}", [
        'is_active' => false,
    ]);

    $response->assertOk();

    $this->assertDatabaseHas('sites', [
        'id' => $site->id,
        'is_active' => false,
    ]);
});

test('cannot update site with invalid domain format', function (): void {
    $site = Site::factory()->create();

    $response = $this->putJson("/api/sites/{$site->id}", [
        'domain' => 'invaliddomain',
    ]);

    $response->assertUnprocessable()
        ->assertJsonValidationErrors(['domain']);
});

test('can delete a site', function (): void {
    $site = Site::factory()->create();

    $response = $this->deleteJson("/api/sites/{$site->id}");

    $response->assertNoContent();

    $this->assertDatabaseMissing('sites', [
        'id' => $site->id,
    ]);
});

test('returns 404 when trying to show non-existent site', function (): void {
    $response = $this->getJson('/api/sites/99999');

    $response->assertNotFound();
});

test('returns 404 when trying to update non-existent site', function (): void {
    $response = $this->putJson('/api/sites/99999', [
        'site_name' => 'Updated',
    ]);

    $response->assertNotFound();
});

test('returns 404 when trying to delete non-existent site', function (): void {
    $response = $this->deleteJson('/api/sites/99999');

    $response->assertNotFound();
});
