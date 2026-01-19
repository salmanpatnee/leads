# Tasks: Site Management UI

**Input**: Design documents from `/specs/002-site-management-ui/`
**Prerequisites**: plan.md (required), spec.md (required for user stories), research.md, data-model.md, quickstart.md

**Tests**: Tests are explicitly requested in the feature specification and constitution ("Every feature must have comprehensive Pest tests").

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and basic structure

- [X] T001 Generate Wayfinder actions for site routes using `php artisan wayfinder:generate`
- [X] T002 [P] Create directory for site management pages in `resources/js/pages/sites`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

- [X] T003 Refine `SiteController` to handle search, filter, and pagination in `app/Http/Controllers/SiteController.php`
- [X] T004 Create reusable `Pagination.vue` component in `resources/js/components/ui/pagination/Pagination.vue`
- [X] T005 [P] Create reusable `Form.vue` component for Site CRUD in `resources/js/pages/sites/Form.vue`

---

## Phase 3: User Story 1 - Access and View Sites List (Priority: P1) 🎯 MVP

**Goal**: Access "Sites" section from sidebar and view a list of all registered sites.

**Independent Test**: Login as admin, click "Sites" in sidebar, and verify table displays sites with Name, Domain, Status, and Actions.

### Tests for User Story 1

- [X] T006 [P] [US1] Update index test to include search and pagination assertions in `tests/Feature/SiteManagementTest.php`

### Implementation for User Story 1

- [X] T007 [P] [US1] Add "Sites" menu item to sidebar in `resources/js/components/AppSidebar.vue`
- [X] T008 [US1] Implement Sites Index page with search, filter, and table in `resources/js/pages/sites/Index.vue`
- [X] T009 [US1] Integrate `Pagination.vue` into `resources/js/pages/sites/Index.vue`

**Checkpoint**: User Story 1 is functional: sidebar link works, and the list page shows all sites with search/pagination.

---

## Phase 4: User Story 2 - Register New Site (Priority: P1)

**Goal**: Create a new site via a dedicated form.

**Independent Test**: Click "Create Site", fill form, submit, and verify redirection to details with success message.

### Tests for User Story 2

- [X] T010 [P] [US2] Verify create page rendering test in `tests/Feature/SiteManagementTest.php`

### Implementation for User Story 2

- [X] T011 [US2] Implement Create Site page using reusable form in `resources/js/pages/sites/Create.vue`
- [X] T012 [US2] Add "Create Site" button to Index page in `resources/js/pages/sites/Index.vue`

**Checkpoint**: User Story 2 is functional: administrators can register new sites.

---

## Phase 5: User Story 3 - View Details and API Key (Priority: P1)

**Goal**: View site details and securely access/copy the API key.

**Independent Test**: View site details, click "Copy" for API Key, and verify clipboard content and success feedback.

### Tests for User Story 3

- [X] T013 [P] [US3] Verify show page rendering and API key visibility test in `tests/Feature/SiteManagementTest.php`

### Implementation for User Story 3

- [X] T014 [US3] Implement Site Details page in `resources/js/pages/sites/Show.vue`
- [X] T015 [US3] Implement copy-to-clipboard logic for API key in `resources/js/pages/sites/Show.vue`

**Checkpoint**: User Story 3 is functional: administrators can retrieve API keys for site integration.

---

## Phase 6: User Story 4 - Edit Site Information (Priority: P2)

**Goal**: Edit site details (Name, Domain, Status).

**Independent Test**: Navigate to Edit page, change values, submit, and verify updates on Details page.

### Tests for User Story 4

- [X] T016 [P] [US4] Verify edit page rendering and update logic test in `tests/Feature/SiteManagementTest.php`

### Implementation for User Story 4

- [X] T017 [US4] Update `SiteController@edit` to use `Sites/Create` with site data in `app/Http/Controllers/SiteController.php`
- [X] T018 [US4] Add "Edit" action buttons to Index page in `resources/js/pages/sites/Index.vue`
- [X] T019 [US4] Add "Edit" button to Details page in `resources/js/pages/sites/Show.vue`

**Checkpoint**: User Story 4 is functional: administrators can maintain site records.

---

## Phase 7: User Story 5 - Delete Site (Priority: P3)

**Goal**: Delete a site with a confirmation modal.

**Independent Test**: Click Delete, confirm modal, and verify site is removed from the list with success message.

### Tests for User Story 5

- [X] T020 [P] [US5] Verify delete confirmation and logic test in `tests/Feature/SiteManagementTest.php`

### Implementation for User Story 5

- [X] T021 [US5] Implement deletion confirmation dialog in `resources/js/pages/sites/Index.vue`
- [X] T022 [US5] Add deletion action to the Edit/Create form in `resources/js/pages/sites/Form.vue`

**Checkpoint**: All user stories are functional.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [X] T023 Run `vendor/bin/pint` to format PHP code
- [X] T024 [P] Verify UI responsiveness across mobile and desktop viewports
- [X] T025 [P] Run all Site management tests and ensure they pass using `php artisan test --filter=SiteManagement`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies.
- **Foundational (Phase 2)**: Depends on Phase 1 - BLOCKS all user stories.
- **User Stories (Phase 3+)**: All depend on Phase 2 completion.
  - US1 (P1) -> US2 (P1) -> US3 (P1) -> US4 (P2) -> US5 (P3)
- **Polish (Final Phase)**: Depends on all user stories being complete.

### Parallel Opportunities

- T002 (Directory creation) and T005 (Form base) can run in parallel with controller refinement.
- All test tasks (T006, T010, T013, T016, T020) can be drafted in parallel before implementation.
- Different User Stories can be worked on in parallel once the Foundational Phase 2 is complete.

---

## Parallel Example: User Story 1

```bash
# Prepare tests and UI structure
Task: "Update index test to include search and pagination assertions in tests/Feature/SiteManagementTest.php"
Task: "Add 'Sites' menu item to sidebar in resources/js/components/AppSidebar.vue"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1 & 2 (Foundation).
2. Complete Phase 3 (User Story 1 - Index).
3. **STOP and VALIDATE**: Test Site Index independently.

### Incremental Delivery

1. Foundation -> MVP (Index) -> Create -> Show -> Edit -> Delete.
2. Each phase adds a distinct CRUD operation.

---

## Notes

- Use shadcn-vue components (Dialog, Button, Input, etc.) consistently.
- Use `laravel/wayfinder` for all route generation and form submissions.
- Ensure all redirects include success session messages for feedback.
