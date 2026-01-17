# Implementation Tasks: Site Management System

**Feature**: Site Management System
**Spec**: [spec.md](spec.md)
**Plan**: [plan.md](plan.md)

## Phase 1: Setup
*Goal: Initialize project structure and configuration.*

- [X] T001 Create directory structure for API Controllers and Requests
- [X] T002 Configure API routing in `bootstrap/app.php` and create `routes/api.php`

## Phase 2: Foundational
*Goal: Create core database tables and models. Required for all user stories.*

- [X] T003 Create migration `create_sites_table` in `database/migrations`
- [X] T004 Create `Site` model in `app/Models/Site.php` with UUID generation logic
- [X] T005 Create `SiteFactory` in `database/factories/SiteFactory.php`
- [X] T006 Create `SiteManagementTest` in `tests/Feature/SiteManagementTest.php` with basic setup

## Phase 3: User Story 1 - Register New Site (P1)
*Goal: Enable administrators to register new sites and generate API keys.*
*Test: POST /api/sites creates a record and returns API key.*

- [X] T007 [US1] Create `StoreSiteRequest` in `app/Http/Requests/Sites/StoreSiteRequest.php`
- [X] T008 [US1] Create `SiteResource` in `app/Http/Resources/SiteResource.php`
- [X] T009 [US1] Implement `store` method in `app/Http/Controllers/Api/SiteController.php`
- [X] T010 [US1] Register POST route in `routes/api.php` wrapped in auth middleware
- [X] T011 [US1] Add registration test cases (success, duplicate domain) to `tests/Feature/SiteManagementTest.php`

## Phase 4: User Story 2 - Manage Site Status (P2)
*Goal: Enable administrators to view, update, and delete sites.*
*Test: PUT /api/sites/{site} updates status; GET returns list.*

- [X] T012 [P] [US2] Create `UpdateSiteRequest` in `app/Http/Requests/Sites/UpdateSiteRequest.php`
- [X] T013 [P] [US2] Implement `index` and `show` methods in `app/Http/Controllers/Api/SiteController.php`
- [X] T014 [US2] Implement `update` method in `app/Http/Controllers/Api/SiteController.php`
- [X] T015 [US2] Implement `destroy` method in `app/Http/Controllers/Api/SiteController.php`
- [X] T016 [US2] Register GET, PUT, DELETE routes in `routes/api.php`
- [X] T017 [US2] Add management test cases (update status, list, delete) to `tests/Feature/SiteManagementTest.php`

## Phase 5: Polish & Cross-Cutting
*Goal: Implement security middleware and ensure code quality.*

- [X] T018 [P] Implement `EnsureSiteApiKeyIsValid` middleware in `app/Http/Middleware/EnsureSiteApiKeyIsValid.php`
- [X] T019 Register middleware alias in `bootstrap/app.php` (if needed) or apply directly
- [X] T020 Run `pint` and `phpstan` to ensure code quality

## Dependencies
1. **Setup & Foundational** (T001-T006) must be completed first.
2. **US1** (T007-T011) depends on Foundational.
3. **US2** (T012-T017) depends on US1 (specifically Controller existence, though methods are independent).
4. **Polish** (T018-T020) can be done after Foundational, but best done last.

## Implementation Strategy
- **MVP**: Complete Phases 1, 2, and 3. This allows sites to be registered and API keys generated.
- **Full**: Complete Phase 4 and 5 to allow management and security.
