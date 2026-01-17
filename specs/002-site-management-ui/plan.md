# Implementation Plan: Site Management UI

## Technical Context

**Language/Version**: PHP 8.3, TypeScript, Vue 3
**Primary Dependencies**: Laravel 12, Inertia.js v2, Tailwind CSS, shadcn-vue, lucide-vue-next, laravel/wayfinder
**Storage**: MySQL (Sites table)
**Project Type**: web

## Constitution Check

- **API-First**: UI handles Site creation and API key display for integration.
- **Flexible Data**: N/A (Admin UI for management).
- **Test-First**: Updates to `SiteManagementTest.php` to cover new UI features.
- **Laravel Conventions**: Standard controllers, Form Requests, Inertia responses.
- **Security**: Admin-only access (FR-010).
- **Simplicity**: Single reusable form for Create/Edit.

## Gate Evaluation

- [x] Feature Spec exists and is clear.
- [x] Research resolved all unknowns.
- [x] Data model is defined.
- [x] API contracts (Controller) already exist but will be refined.

## Phases

### Phase 0: Outline & Research (COMPLETE)
- [x] Research sidebar integration.
- [x] Research wayfinder forms.
- [x] Research pagination.
- [x] Generate `research.md`.

### Phase 1: Design & Contracts (COMPLETE)
- [x] Document `data-model.md`.
- [x] Document `quickstart.md`.
- [x] Refine `SiteController` for pagination and search.

### Phase 2: Core UI Components
- [ ] Implement `Pagination.vue` component.
- [ ] Implement `Form` reusable component logic.
- [ ] Add "Sites" to sidebar in `AppSidebar.vue`.

### Phase 3: Site List (Index)
- [ ] Create `resources/js/pages/sites/Index.vue`.
- [ ] Implement search and filter functionality.
- [ ] Add copy-to-clipboard for API key in the list (if requested) or just show.
- [ ] Implement Delete confirmation modal.

### Phase 4: Create/Edit & Details
- [ ] Create `resources/js/pages/sites/Create.vue` (reusable form).
- [ ] Create `resources/js/pages/sites/Show.vue` (details & API key).
- [ ] Update `SiteController` to use `Sites/Create` for edit.

### Phase 5: Verification
- [ ] Update and run `SiteManagementTest.php`.
- [ ] Verify UI responsiveness and consistency.
- [ ] Final PINT formatting.