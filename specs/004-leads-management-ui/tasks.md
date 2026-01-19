# Tasks: Leads Management UI

**Feature**: Leads Management UI
**Spec**: [specs/004-leads-management-ui/spec.md](specs/004-leads-management-ui/spec.md)
**Status**: Pending

## Phase 1: Setup
*Goal: Initialize project structure and update data models.*

- [X] T001 [P] Create LeadStatus enum in `app/Enums/LeadStatus.php`
- [X] T002 Update Lead model with relationships, casts, and fillables in `app/Models/Lead.php`
- [X] T003 [P] Update Site model with leads relationship in `app/Models/Site.php`
- [X] T004 Create UpdateLeadStatusRequest in `app/Http/Requests/Lead/UpdateLeadStatusRequest.php`

## Phase 2: Foundational
*Goal: Create core backend controller and basic frontend routing.*

- [X] T005 [P] Create LeadController with empty methods in `app/Http/Controllers/LeadController.php`
- [X] T006 Register leads resource routes in `routes/web.php`
- [X] T007 [P] Create basic Index.vue page structure in `resources/js/pages/leads/Index.vue`
- [X] T008 [P] Create basic Show.vue page structure in `resources/js/pages/leads/Show.vue`
- [X] T009 Add Leads link to navigation sidebar in `resources/js/layouts/AppLayout.vue`

## Phase 3: Browse and Filter Leads
*Goal: [US1] Implement the paginated leads list with site and date filtering.*

- [X] T010 [US1] Implement index method with pagination and eager loading in `app/Http/Controllers/LeadController.php`
- [X] T011 [US1] Add site filtering logic to index method in `app/Http/Controllers/LeadController.php`
- [X] T012 [US1] Add date range filtering logic to index method in `app/Http/Controllers/LeadController.php`
- [X] T013 [P] [US1] Implement unit test for LeadController index filtering in `tests/Feature/LeadManagementTest.php`
- [X] T014 [US1] Implement Leads table with shadcn-vue in `resources/js/pages/leads/Index.vue`
- [X] T015 [US1] Implement Site dropdown filter in `resources/js/pages/leads/Index.vue`
- [X] T016 [US1] Implement Date range picker filter in `resources/js/pages/leads/Index.vue`
- [X] T017 [US1] Integrate pagination component in `resources/js/pages/leads/Index.vue`

## Phase 4: Search Leads
*Goal: [US2] Implement keyword search within JSON form data.*

- [X] T018 [US2] Add JSON search logic to LeadController index method in `app/Http/Controllers/LeadController.php`
- [X] T019 [P] [US2] Implement unit test for JSON search logic in `tests/Feature/LeadManagementTest.php`
- [X] T020 [US2] Add search input field to toolbar in `resources/js/pages/leads/Index.vue`
- [X] T021 [US2] Wire search input to Inertia router visit in `resources/js/pages/leads/Index.vue`

## Phase 5: View Lead Details
*Goal: [US3] Implement detailed view for individual lead submissions.*

- [X] T022 [US3] Implement show method to return lead data in `app/Http/Controllers/LeadController.php`
- [X] T023 [P] [US3] Implement unit test for LeadController show method in `tests/Feature/LeadManagementTest.php`
- [X] T024 [US3] Implement back navigation and layout in `resources/js/pages/leads/Show.vue`
- [X] T025 [US3] Display lead status and metadata in `resources/js/pages/leads/Show.vue`
- [X] T026 [US3] Render formatted form_data JSON in `resources/js/pages/leads/Show.vue`
- [X] T027 [US3] Add "View" button to leads table actions in `resources/js/pages/leads/Index.vue`

## Final Phase: Polish
*Goal: Ensure consistency and code quality.*

- [X] T028 Apply proper formatting with Pint to all PHP files
- [X] T029 Ensure strict types and consistent naming in Vue components
- [X] T030 Verify responsive design for mobile views
- [X] T031 [P] Run full test suite and fix any regressions

## Dependencies

- Phase 2 depends on Phase 1
- Phase 3 depends on Phase 2
- Phase 4 depends on Phase 3 (extends index logic)
- Phase 5 depends on Phase 2 (routes/controller exist)

## Parallel Execution Opportunities

- **Phase 1**: T001, T003, and T004 can be done in parallel.
- **Phase 2**: T005, T007, T008, T009 can be done in parallel.
- **Phase 3**: T013 (Tests) and T010/T011 (Backend) can be done in parallel with T014-T017 (Frontend).
- **Phase 4**: T019 (Tests) and T018 (Backend) can be done in parallel with T020-T021 (Frontend).
- **Phase 5**: T023 (Tests) and T022 (Backend) can be done in parallel with T024-T026 (Frontend).

## Implementation Strategy

1.  **Setup & Foundation**: Establish the data models and basic routing/controller structure first to unblock frontend work.
2.  **MVP (US1)**: Focus on getting the list view working with pagination and site filtering. This provides the core value.
3.  **Search (US2)**: Add search capability on top of the list view.
4.  **Details (US3)**: Implement the read-only detail view.
