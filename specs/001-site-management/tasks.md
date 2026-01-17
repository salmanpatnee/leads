# Implementation Tasks: Site Management System

**Feature**: Site Management System
**Spec**: [spec.md](spec.md)
**Plan**: [plan.md](plan.md)

## Phase 1: Setup
*Goal: Initialize project structure and configuration.*

- [X] T001 Create directory structure for Web Controllers and Requests
- [X] T002 Ensure web routing is configured (routes already exist in `routes/web.php`)

## Phase 2: Foundational
*Goal: Create core database tables and models. Required for all user stories.*

- [X] T003 Create migration `create_sites_table` in `database/migrations`
- [X] T004 Create `Site` model in `app/Models/Site.php` with UUID generation logic
- [X] T005 Create `SiteFactory` in `database/factories/SiteFactory.php`
- [X] T006 Create `SiteManagementTest` in `tests/Feature/SiteManagementTest.php` with basic setup

## Phase 3: User Story 1 - Register New Site (P1)
*Goal: Enable administrators to register new sites and generate API keys.*
*Test: POST /sites creates a record and returns Inertia response.*

- [X] T007 [US1] Create `StoreSiteRequest` in `app/Http/Requests/Sites/StoreSiteRequest.php`
- [ ] T008 [US1] Implement `create` and `store` methods in `app/Http/Controllers/SiteController.php` using Inertia
- [ ] T009 [US1] Register GET /sites/create and POST /sites routes in `routes/web.php` with auth middleware
- [ ] T010 [US1] Add registration test cases (success, duplicate domain) to `tests/Feature/SiteManagementTest.php`

## Phase 4: User Story 2 - Manage Site Status (P2)
*Goal: Enable administrators to view, update, and delete sites.*
*Test: PUT /sites/{site} updates status; GET /sites returns Inertia response with list.*

- [X] T011 [US2] Create `UpdateSiteRequest` in `app/Http/Requests/Sites/UpdateSiteRequest.php`
- [ ] T012 [US2] Implement `index` and `show` methods in `app/Http/Controllers/SiteController.php` using Inertia
- [ ] T013 [US2] Implement `edit` and `update` methods in `app/Http/Controllers/SiteController.php` using Inertia
- [ ] T014 [US2] Implement `destroy` method in `app/Http/Controllers/SiteController.php`
- [ ] T015 [US2] Register resource routes in `routes/web.php` (GET /sites, GET /sites/{site}, GET /sites/{site}/edit, PUT /sites/{site}, DELETE /sites/{site})
- [ ] T016 [US2] Add management test cases (update status, list, delete) to `tests/Feature/SiteManagementTest.php`

## Phase 5: Polish & Cross-Cutting
*Goal: Implement security middleware and ensure code quality.*

- [X] T017 [P] Implement `EnsureSiteApiKeyIsValid` middleware in `app/Http/Middleware/EnsureSiteApiKeyIsValid.php`
- [X] T018 Register middleware alias in `bootstrap/app.php` (if needed) or apply directly
- [ ] T019 Run `pint` to ensure code quality

## Dependencies
1. **Setup & Foundational** (T001-T006) must be completed first.
2. **US1** (T007-T010) depends on Foundational.
3. **US2** (T011-T016) depends on US1 (specifically Controller existence, though methods are independent).
4. **Polish** (T017-T019) can be done after Foundational, but best done last.

## Implementation Strategy
- **MVP**: Complete Phases 1, 2, and 3. This allows sites to be registered with Inertia responses (backend ready for UI).
- **Full**: Complete Phases 4 and 5 to allow full management and security.
- **Future**: Build Vue.js components in `resources/js/Pages/Sites/` to create the full UI.
