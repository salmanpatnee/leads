# Research: Site Management UI

## Decision: Sidebar Navigation
- **Choice**: Add "Sites" to `mainNavItems` in `resources/js/components/AppSidebar.vue`.
- **Icon**: `Globe` from `lucide-vue-next`.
- **Rationale**: Consistent with the existing sidebar structure and uses available icons.

## Decision: Form Implementation
- **Choice**: Use `laravel/wayfinder` with `v-bind="SiteController.store.form()"` and `v-bind="SiteController.update.form()"`.
- **Rationale**: Follows the pattern established in `Profile.vue` for type-safe forms and easy validation handling.

## Decision: Reusable Form Component
- **Choice**: Create `resources/js/pages/sites/Form.vue` and use it in both `Create.vue` and `Edit.vue` (or just use `Create.vue` as requested).
- **Update**: The user requested a single `Create.vue` file reused for both creating and editing. I will implement this by passing a `site` prop and checking if it's present to determine create/edit mode.

## Decision: Pagination
- **Choice**: Implement a simple `Pagination.vue` component in `resources/js/components/ui/pagination` or similar. Since shadcn-vue doesn't have it by default in this project, I'll build a standard one using Tailwind.
- **Rationale**: Required for SC-017 and handling many sites.

## Decision: Copy to Clipboard
- **Choice**: Use a custom composable or inline function using `navigator.clipboard.writeText`. Show a "Copied!" toast using shadcn-vue (if available) or a simple state.
- **Rationale**: Modern, secure browser API.

## Decision: Site Deletion
- **Choice**: Use `Dialog` from shadcn-vue in `Index.vue` and `Edit.vue` for confirmation.
- **Rationale**: SC-005 requires a confirmation modal.

## Alternatives Considered
- **Standard Blade Views**: Rejected because the project uses Inertia.js.
- **Vuelidate for Client-side Validation**: Rejected in favor of Laravel's server-side validation handled by Wayfinder/Inertia, which is simpler and more consistent with the project's "Laravel Way" principle.
