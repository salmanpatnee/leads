<?php

use App\Models\Site;
use App\Models\User;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('can create a new site with valid data', function (): void {
    $siteData = [
        'site_name' => 'Test WordPress Site',
        'domain' => 'example.com',
    ];

    $response = $this->post('/sites', $siteData);

    $response->assertRedirect();
    $response->assertSessionHas('success');
    $response->assertSessionHas('api_key');

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

    $response = $this->post('/sites', $siteData);

    $response->assertSessionHasErrors(['domain']);
});

test('site_name is required', function (): void {
    $response = $this->post('/sites', [
        'domain' => 'test.com',
    ]);

    $response->assertSessionHasErrors(['site_name']);
});

test('domain is required', function (): void {
    $response = $this->post('/sites', [
        'site_name' => 'Test Site',
    ]);

    $response->assertSessionHasErrors(['domain']);
});

test('domain must be a valid format', function (string $invalidDomain): void {
    $response = $this->post('/sites', [
        'site_name' => 'Test Site',
        'domain' => $invalidDomain,
    ]);

    $response->assertSessionHasErrors(['domain']);
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
    $response = $this->post('/sites', [
        'site_name' => 'Test Site',
        'domain' => $validDomain,
    ]);

    $response->assertRedirect();
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
    $response = $this->post('/sites', [
        'site_name' => 'Test Site',
        'domain' => 'EXAMPLE.COM',
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('sites', [
        'domain' => 'example.com',
    ]);
});

test('domain is extracted from full URL', function (string $input, string $expected): void {
    $response = $this->post('/sites', [
        'site_name' => 'Test Site',
        'domain' => $input,
    ]);

    $response->assertRedirect();

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

test('can view sites index page', function (): void {
    Site::factory()->count(3)->create();

    $response = $this->get('/sites');

    $response->assertInertia(fn ($page) => $page
        ->component('Sites/Index')
        ->has('sites', 3)
    );
});

test('can view site show page', function (): void {
    $site = Site::factory()->create([
        'site_name' => 'Specific Site',
        'domain' => 'specific.com',
    ]);

    $response = $this->get("/sites/{$site->id}");

    $response->assertInertia(fn ($page) => $page
        ->component('Sites/Show')
        ->has('site')
        ->where('site.id', $site->id)
        ->where('site.site_name', 'Specific Site')
        ->where('site.domain', 'specific.com')
    );
});

test('can view create site page', function (): void {
    $response = $this->get('/sites/create');

    $response->assertInertia(fn ($page) => $page
        ->component('Sites/Create')
    );
});

test('can view edit site page', function (): void {
    $site = Site::factory()->create();

    $response = $this->get("/sites/{$site->id}/edit");

    $response->assertInertia(fn ($page) => $page
        ->component('Sites/Edit')
        ->has('site')
        ->where('site.id', $site->id)
    );
});

test('can update a site', function (): void {
    $site = Site::factory()->create([
        'site_name' => 'Old Name',
        'domain' => 'old.com',
        'is_active' => true,
    ]);

    $response = $this->put("/sites/{$site->id}", [
        'site_name' => 'Updated Name',
        'is_active' => false,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('sites', [
        'id' => $site->id,
        'site_name' => 'Updated Name',
        'is_active' => false,
    ]);
});

test('can update site status to inactive', function (): void {
    $site = Site::factory()->create(['is_active' => true]);

    $response = $this->put("/sites/{$site->id}", [
        'is_active' => false,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('sites', [
        'id' => $site->id,
        'is_active' => false,
    ]);
});

test('cannot update site with invalid domain format', function (): void {
    $site = Site::factory()->create();

    $response = $this->put("/sites/{$site->id}", [
        'domain' => 'invaliddomain',
    ]);

    $response->assertSessionHasErrors(['domain']);
});

test('can delete a site', function (): void {
    $site = Site::factory()->create();

    $response = $this->delete("/sites/{$site->id}");

    $response->assertRedirect('/sites');
    $response->assertSessionHas('success');

    $this->assertDatabaseMissing('sites', [
        'id' => $site->id,
    ]);
});

test('returns 404 when trying to show non-existent site', function (): void {
    $response = $this->get('/sites/99999');

    $response->assertNotFound();
});

test('returns 404 when trying to update non-existent site', function (): void {
    $response = $this->put('/sites/99999', [
        'site_name' => 'Updated',
    ]);

    $response->assertNotFound();
});

test('returns 404 when trying to delete non-existent site', function (): void {
    $response = $this->delete('/sites/99999');

    $response->assertNotFound();
});

test('unauthenticated users cannot access sites', function (): void {
    auth()->logout();

    $this->get('/sites')->assertRedirect('/login');
    $this->get('/sites/create')->assertRedirect('/login');
    $this->post('/sites', [])->assertRedirect('/login');
});
