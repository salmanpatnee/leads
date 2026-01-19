# Implementation Plan: Leads Ingestion API

**Branch**: `003-leads-ingestion-api` | **Date**: 2026-01-19 | **Spec**: [specs/003-leads-ingestion-api/spec.md](spec.md)
**Input**: Feature specification from `specs/003-leads-ingestion-api/spec.md`

## Summary

Implement a secure, rate-limited REST API endpoint (`POST /api/leads`) to ingest lead data from external WordPress sites. The system will validate API keys, store arbitrary form data as JSON, and enforce a rate limit of 60 requests per minute per site. Clarifications have established a 1MB payload limit and standard application logging for auth failures.

## Technical Context

**Language/Version**: PHP 8.3.19
**Primary Dependencies**: Laravel 12 (Framework), Pest 4 (Testing)
**Storage**: MySQL (Leads table with JSON column)
**Testing**: Pest (Feature tests for API, Unit tests for validation)
**Target Platform**: Web Server (Laravel Herd/Production)
**Project Type**: Laravel Monolith (API + Web)
**Performance Goals**: <500ms P95 response time
**Constraints**: 60 requests/minute/site rate limit, 1MB max payload
**Scale/Scope**: API Endpoint + Database Migration + Model + Validation

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] **API-First Design**: The feature is purely an API endpoint (`POST /api/leads`).
- [x] **Flexible Data Storage**: Uses JSON column for `form_data` to support dynamic CF7 fields.
- [x] **Test-First Development**: Pest tests will be written for all scenarios (Auth, Validation, Success, Rate Limit).
- [x] **Laravel Conventions**: Uses Form Requests, Eloquent Models, and standard Routing.
- [x] **Security by Default**: Enforces `X-API-Key` authentication, Rate Limiting, and 1MB Payload Limit.
- [x] **Simplicity Over Features**: Minimal implementation focused on ingestion only.

## Project Structure

### Documentation (this feature)

```text
specs/003-leads-ingestion-api/
├── plan.md              # This file
├── research.md          # Technology choices and rationale
├── data-model.md        # Database schema and Eloquent model definition
├── quickstart.md        # Guide for testing the API
├── contracts/           # OpenAPI specification
│   └── leads-api.yaml
└── tasks.md             # Implementation tasks
```

### Source Code (repository root)

```text
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       └── LeadController.php      # Invokable controller
│   ├── Middleware/
│   │   └── EnsureSiteApiKeyIsValid.php # Middleware for API Key auth
│   └── Requests/
│       └── StoreLeadRequest.php        # Validation logic
├── Models/
│   └── Lead.php                        # Eloquent model

database/
├── migrations/
│   └── YYYY_MM_DD_HHMMSS_create_leads_table.php
└── factories/
    └── LeadFactory.php

routes/
└── api.php                             # Route definition
```

**Structure Decision**: Standard Laravel MVC structure with API-specific controller namespace.

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| None      | N/A        | N/A                                 |