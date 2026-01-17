# WordPress Contact Form 7 Leads System Constitution

## Core Principles

### I. API-First Design
All lead ingestion flows through a secure REST API endpoint. The API must be simple, reliable, and well-documented for WordPress integration. API key authentication per site ensures security and traceability.

### II. Flexible Data Storage
Form submissions are stored as flexible JSON to accommodate any CF7 form structure. Never assume specific field names exist - the system must handle arbitrary form data gracefully.

### III. Test-First Development
Every feature must have comprehensive Pest tests before implementation is complete. Tests cover happy paths, error paths, and edge cases. Run `php artisan test --compact` after each change.

### IV. Laravel Conventions
Follow Laravel 12 patterns strictly: Eloquent relationships with type hints, Form Request validation, Inertia.js for frontend, and artisan commands for scaffolding. Use `php artisan make:` commands for all new files.

### V. Security by Default
- API keys must be unique, random, and securely generated
- Rate limiting on API endpoints (60 requests/minute/site)
- Never expose sensitive data in API responses
- Validate and sanitize all incoming form data

### VI. Simplicity Over Features
Start with the minimum viable implementation. Avoid over-engineering. The system should be straightforward to understand and maintain.

## Data Model Constraints

- Sites must have unique domains and API keys
- Leads must always reference a valid site
- Lead status follows a defined enum: new → contacted → converted
- form_data JSON can contain any structure from CF7

## API Contract

- POST `/api/leads` with `X-API-Key` header
- Returns 201 on success with `{ success: true, lead_id: N }`
- Returns 401 for invalid/missing API key
- Returns 422 for validation errors
- Returns 429 for rate limit exceeded

## Testing Requirements

- API endpoint tests for all response codes
- Sites CRUD tests with validation
- Leads management tests with status transitions
- Analytics data aggregation tests

## Governance

This constitution guides all development decisions. Amendments require documentation and clear justification.

**Version**: 1.0.0 | **Ratified**: 2026-01-17 | **Last Amended**: 2026-01-17
