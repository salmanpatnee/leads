# Research & Technical Decisions

## 1. UI Component Library
**Decision**: Use `shadcn-vue` components (`Table`, `Button`, `Input`, `Select`, etc.) with standard `v-for` iteration for the data table.
**Rationale**: 
- Matches the existing implementation in `resources/js/pages/sites/Index.vue`.
- Ensures visual consistency across the "Sites" and "Leads" modules.
- Reduces complexity compared to setting up `@tanstack/vue-table` definitions when simple server-side pagination is already provided by Laravel.
**Alternatives Considered**:
- `@tanstack/vue-table`: It is installed in `package.json`, but looking at `sites/Index.vue`, the project prefers direct usage of shadcn Table components with Inertia's `LaravelPagination` prop. We will stick to this pattern.

## 2. JSON Search Strategy
**Decision**: Use `whereJsonContains` or `whereRaw` (depending on DB driver support for case-insensitive search if needed) to search within `form_data`.
**Rationale**:
- The requirements specify searching "specific text within their form submissions".
- `form_data` is a JSON column.
- For MySQL (production target), `JSON_EXTRACT` or arrow syntax `->` in `where` clauses is efficient enough for the expected volume.
- **Clarification**: The spec mentions searching "email" or "name". We will implement a search that checks if the string exists as a value in the JSON object.
**Implementation Detail**: `Lead::where('form_data', 'like', "%$search%")` might be the simplest naive approach that works for string matching inside JSON without knowing exact keys, but standard JSON searching is safer. However, since keys are dynamic/unknown, a full-text search on the JSON column or a cast-to-text search `whereRaw('LOWER(form_data) LIKE ?', ["%...%"])` is likely needed to find values across any key.

## 3. Date Filtering
**Decision**: Use a simple "Start Date" and "End Date" query parameters (`filter[date_from]`, `filter[date_to]`).
**Rationale**: Standard pattern for Laravel APIs and Inertia.
**UI**: Use a generic date picker or two date inputs if a unified range picker isn't available in the current component set. `shadcn-vue` usually has a `Calendar` or `Popover` based date picker. We will check for existing date components or build a simple one.

## 4. Icons
**Decision**: Use `lucide-vue-next`.
**Rationale**: Already used in `sites/Index.vue` and installed in `package.json`.
