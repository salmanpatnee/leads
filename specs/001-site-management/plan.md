# Implementation Plan: Site Management System

**Branch**: `001-site-management` | **Date**: 2026-01-17 | **Spec**: [specs/001-site-management/spec.md](specs/001-site-management/spec.md)
**Input**: Feature specification from `specs/001-site-management/spec.md`

## Summary

Implement the backend for the Site Management System, including the `sites` database table, Eloquent model with automatic UUID API key generation, and Inertia-ready web controllers that return Inertia responses (instead of JSON). Vue.js UI components will be built in a future phase. Include a custom middleware to validate `X-API-Key` headers for future lead ingestion endpoints.

## Technical Context

**Language/Version**: PHP 8.3
**Primary Dependencies**: Laravel 12, ramsey/uuid (standard)
**Storage**: MySQL
**Testing**: Pest (PHPUnit 12)
**Target Platform**: Linux server (standard Laravel deployment)
**Project Type**: Web Application (Backend API focus)
**Performance Goals**: Standard web response times (<200ms)
**Constraints**: Backend Only (Inertia-ready controllers, Vue UI to be built later)
**Scale/Scope**: <100 sites expected initially

## Constitution Check

*GATE: Passed. Complies with API-First Design, Flexible Data Storage (via future leads), Test-First Development, and Laravel Conventions.*

## Project Structure

### Documentation (this feature)

```text
specs/001-site-management/
├── plan.md              # This file
├── research.md          # Research findings
├── data-model.md        # Database schema
├── quickstart.md        # Usage guide
├── contracts/           # OpenAPI specs
│   └── openapi.yaml
└── tasks.md             # Implementation tasks
```

### Source Code

```text
app/
├── Models/
│   └── Site.php
├── Http/
│   ├── Controllers/
│   │   └── SiteController.php
│   ├── Middleware/
│   │   └── EnsureSiteApiKeyIsValid.php
│   └── Requests/
│       └── Sites/
│           ├── StoreSiteRequest.php
│           └── UpdateSiteRequest.php
database/
├── migrations/
│   └── xxxx_xx_xx_xxxxxx_create_sites_table.php
tests/
├── Feature/
│   └── SiteManagementTest.php
```

**Structure Decision**: Standard Laravel structure with web controllers using Inertia::render() instead of JSON responses. Controllers moved from `Api/` namespace to root `Controllers/` namespace. API Resources removed as Inertia passes data directly. Vue UI components will be created in a future phase in `resources/js/Pages/Sites/`.

## Complexity Tracking

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| None | | |