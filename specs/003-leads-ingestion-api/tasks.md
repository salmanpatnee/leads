# Tasks: Leads Ingestion API

**Feature**: Leads Ingestion API (`003-leads-ingestion-api`)
**Status**: Pending
**Spec**: [spec.md](spec.md) | **Plan**: [plan.md](plan.md)

## Phase 1: Setup
*Goal: Initialize project structure and generate boilerplate files.*

- [ ] T001 Create `Lead` model, migration, and factory `php artisan make:model Lead -m -f`
- [ ] T002 Create API Controller `php artisan make:controller Api/LeadController --invokable`
- [ ] T003 Create Form Request `php artisan make:request StoreLeadRequest`
- [ ] T004 Create Auth Middleware `php artisan make:middleware EnsureSiteApiKeyIsValid`
- [ ] T005 Create Feature Test `php artisan make:test Api/StoreLeadTest`

## Phase 2: Foundational
*Goal: Implement core data structures and shared logic required by all stories.*

- [ ] T006 [P] Define `leads` table schema in `database/migrations/*_create_leads_table.php`
- [ ] T007 [P] Configure `Lead` model (fillable, casts, relationships) in `app/Models/Lead.php`
- [ ] T008 [P] Define `LeadFactory` state in `database/factories/LeadFactory.php`
- [ ] T009 Run migrations `php artisan migrate`
- [ ] T010 [P] Implement `handle` method in `app/Http/Middleware/EnsureSiteApiKeyIsValid.php` to validate `X-API-Key` against `Site` model

## Phase 3: User Story 1 - Submit New Lead (P1)
*Goal: Enable external systems to submit leads via API.*
*Test Criteria: Valid POST request creates a database record.*

- [ ] T011 [P] [US1] Define basic validation rules (form_name, form_data) in `app/Http/Requests/StoreLeadRequest.php`
- [ ] T012 [P] [US1] Register `POST /api/leads` route in `routes/api.php`
- [ ] T013 [US1] Implement `__invoke` in `app/Http/Controllers/Api/LeadController.php` to store lead (ensure HTTP 500 on DB failure)
- [ ] T014 [US1] Create happy path test (assert 201 Created & DB record) in `tests/Feature/Api/StoreLeadTest.php`

## Phase 4: User Story 3 - Authentication & Rate Limiting (P1)
*Goal: Secure the endpoint and prevent abuse.*
*Test Criteria: Requests without valid key or exceeding rate limit are rejected.*

- [ ] T015 [US3] Apply `EnsureSiteApiKeyIsValid` middleware to the route in `routes/api.php`
- [ ] T016 [US3] Add logging for failed auth attempts in `app/Http/Middleware/EnsureSiteApiKeyIsValid.php`
- [ ] T017 [US3] Configure Rate Limiting (60/min/site) in `bootstrap/app.php` or `AppServiceProvider.php`
- [ ] T018 [US3] Apply throttle middleware to `POST /api/leads` in `routes/api.php`
- [ ] T019 [US3] Add tests for Auth failure (401) and Rate Limiting (429) in `tests/Feature/Api/StoreLeadTest.php`

## Phase 5: User Story 2 - Validation & Error Handling (P2)
*Goal: Ensure data integrity and handle edge cases.*
*Test Criteria: Invalid payloads and large files are rejected.*

- [ ] T020 [P] [US2] Add 1MB payload size check/validation in `app/Http/Requests/StoreLeadRequest.php`
- [ ] T021 [P] [US2] Refine validation rules for JSON structure in `app/Http/Requests/StoreLeadRequest.php`
- [ ] T022 [US2] Add negative tests (validation errors, 1MB limit, malformed JSON) in `tests/Feature/Api/StoreLeadTest.php`

## Phase 6: Polish
*Goal: Final code quality checks and standardization.*

- [ ] T023 Run `vendor/bin/pint` to format code
- [ ] T024 Run `php artisan test --filter=StoreLeadTest` to ensure all tests pass
- [ ] T025 Verify `contracts/leads-api.yaml` matches implementation

## Dependencies

- **US1** depends on **Foundational** (Lead model, DB)
- **US3** depends on **Foundational** (Middleware) and **US1** (Route existence)
- **US2** depends on **US1** (Request class existence)

## Implementation Strategy
- **MVP**: Complete Phase 1, 2, and 3. This allows lead ingestion (unsecured).
- **Secure MVP**: Complete Phase 4. Adds Security.
- **Robustness**: Complete Phase 5. Adds Data Integrity.
