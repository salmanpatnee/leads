<?php

use App\Models\Lead;
use App\Models\Site;

beforeEach(function (): void {
    $this->site = Site::factory()->create();
});

test('can submit a new lead with valid data', function (): void {
    $leadData = [
        'form_name' => 'Contact Form 1',
        'form_data' => [
            'your-name' => 'John Doe',
            'your-email' => 'john@example.com',
            'your-message' => 'Hello, I would like more information.',
        ],
        'submitted_at' => now()->toIso8601String(),
    ];

    $response = $this->postJson('/api/leads', $leadData, [
        'X-API-Key' => $this->site->api_key,
    ]);

    $response->assertCreated();
    $response->assertJson([
        'success' => true,
    ]);
    $response->assertJsonStructure([
        'success',
        'lead_id',
    ]);

    $this->assertDatabaseHas('leads', [
        'site_id' => $this->site->id,
        'form_name' => 'Contact Form 1',
        'status' => 'new',
    ]);

    $lead = Lead::latest()->first();
    expect($lead->form_data)->toBe($leadData['form_data']);
    expect($lead->ip_address)->not->toBeNull();
    expect($lead->user_agent)->not->toBeNull();
});

test('form_name is required', function (): void {
    $response = $this->postJson('/api/leads', [
        'form_data' => [
            'your-name' => 'John Doe',
        ],
    ], [
        'X-API-Key' => $this->site->api_key,
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['form_name']);
});

test('form_data is required', function (): void {
    $response = $this->postJson('/api/leads', [
        'form_name' => 'Contact Form 1',
    ], [
        'X-API-Key' => $this->site->api_key,
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['form_data']);
});

test('form_data must be an array', function (): void {
    $response = $this->postJson('/api/leads', [
        'form_name' => 'Contact Form 1',
        'form_data' => 'not an array',
    ], [
        'X-API-Key' => $this->site->api_key,
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['form_data']);
});

test('submitted_at is optional', function (): void {
    $leadData = [
        'form_name' => 'Contact Form 1',
        'form_data' => [
            'your-name' => 'John Doe',
        ],
    ];

    $response = $this->postJson('/api/leads', $leadData, [
        'X-API-Key' => $this->site->api_key,
    ]);

    $response->assertCreated();
    $this->assertDatabaseHas('leads', [
        'site_id' => $this->site->id,
        'form_name' => 'Contact Form 1',
    ]);
});

test('submitted_at must be a valid date', function (): void {
    $response = $this->postJson('/api/leads', [
        'form_name' => 'Contact Form 1',
        'form_data' => [
            'your-name' => 'John Doe',
        ],
        'submitted_at' => 'invalid-date',
    ], [
        'X-API-Key' => $this->site->api_key,
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['submitted_at']);
});

test('returns 401 when API key is missing', function (): void {
    $leadData = [
        'form_name' => 'Contact Form 1',
        'form_data' => [
            'your-name' => 'John Doe',
        ],
    ];

    $response = $this->postJson('/api/leads', $leadData);

    $response->assertUnauthorized();
    $response->assertJson([
        'message' => 'API key is required.',
    ]);
});

test('returns 401 when API key is invalid', function (): void {
    $leadData = [
        'form_name' => 'Contact Form 1',
        'form_data' => [
            'your-name' => 'John Doe',
        ],
    ];

    $response = $this->postJson('/api/leads', $leadData, [
        'X-API-Key' => 'invalid-api-key',
    ]);

    $response->assertUnauthorized();
    $response->assertJson([
        'message' => 'Invalid or inactive API key.',
    ]);
});

test('returns 401 when site is inactive', function (): void {
    $inactiveSite = Site::factory()->inactive()->create();

    $leadData = [
        'form_name' => 'Contact Form 1',
        'form_data' => [
            'your-name' => 'John Doe',
        ],
    ];

    $response = $this->postJson('/api/leads', $leadData, [
        'X-API-Key' => $inactiveSite->api_key,
    ]);

    $response->assertUnauthorized();
    $response->assertJson([
        'message' => 'Invalid or inactive API key.',
    ]);
});

test('captures IP address and user agent', function (): void {
    $leadData = [
        'form_name' => 'Contact Form 1',
        'form_data' => [
            'your-name' => 'John Doe',
        ],
    ];

    $response = $this->postJson('/api/leads', $leadData, [
        'X-API-Key' => $this->site->api_key,
    ]);

    $response->assertCreated();

    $lead = Lead::latest()->first();
    expect($lead->ip_address)->toBe('127.0.0.1');
    expect($lead->user_agent)->toBe('Symfony');
});

test('returns 429 when rate limit is exceeded', function (): void {
    $leadData = [
        'form_name' => 'Contact Form 1',
        'form_data' => [
            'your-name' => 'John Doe',
        ],
    ];

    // Hit the rate limit by making more than 60 requests
    for ($i = 0; $i < 61; $i++) {
        $this->postJson('/api/leads', $leadData, [
            'X-API-Key' => $this->site->api_key,
        ]);
    }

    // The 62nd request should be rate limited
    $response = $this->postJson('/api/leads', $leadData, [
        'X-API-Key' => $this->site->api_key,
    ]);

    $response->assertStatus(429);
    $response->assertJson([
        'message' => 'Too Many Attempts.',
    ]);
});

test('returns 413 when payload exceeds 1MB', function (): void {
    // Create a payload larger than 1MB
    $largeFormData = [];
    $size = 0;

    // Add entries until we exceed 1MB
    while ($size <= 1024 * 1024) { // 1MB in bytes
        $largeFormData['field_'.count($largeFormData)] = str_repeat('a', 1000);
        $size = strlen(json_encode([
            'form_name' => 'Contact Form 1',
            'form_data' => $largeFormData,
        ]));
    }

    $response = $this->postJson('/api/leads', [
        'form_name' => 'Contact Form 1',
        'form_data' => $largeFormData,
    ], [
        'X-API-Key' => $this->site->api_key,
    ]);

    $response->assertStatus(413);
    $response->assertSee('Payload Too Large: Request body exceeds 1MB limit.');
});
