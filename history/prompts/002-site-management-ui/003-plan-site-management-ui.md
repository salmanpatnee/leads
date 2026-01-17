---
id: 003
stage: plan
feature: 002-site-management-ui
title: Plan Site Management UI
date: 2026-01-17
---

# PROMPT

/sp.plan build the site management UI using Inertia.js and shadcn-vue, including adding a “Sites” menu item to the sidebar, creating a clean and maintainable file structure with `Index.vue` for listing sites `Show.vue` for viewing site details and a single `Create.vue` file reused for both creating and editing sites, update the edit method in the controller accordingly to use the Create.vue, implementing the list page to display columns for name, domain, status, and API key with a copy-to-clipboard action, adding action buttons on the list page for editing viewing, and deleting sites (with deletion handled directly from the list view), use shadcn-vue modal for deleting, building create and edit forms with proper client-side and server-side validation, wiring all pages to Laravel controllers via Inertia responses, enforcing admin-only access, and ensuring consistent use of reusable shadcn-vue components for tables, forms, buttons, dialogs, and feedback states while keeping the UI aligned with existing layouts, navigation patterns, and best practices.

# RESPONSE

I have completed the planning phase for the Site Management UI feature.

### Artifacts Generated:
- **Research**: `specs/002-site-management-ui/research.md` (Sidebar integration, Wayfinder forms, pagination).
- **Data Model**: `specs/002-site-management-ui/data-model.md` (Site entity documentation).
- **Quickstart**: `specs/002-site-management-ui/quickstart.md` (Setup and testing instructions).
- **Implementation Plan**: `specs/002-site-management-ui/plan.md` (Phased roadmap for UI development).

### Key Decisions:
1. **Reusable Form**: A single `Create.vue` will handle both creation and editing by detecting the presence of a `site` prop.
2. **Sidebar**: Added "Sites" to `mainNavItems` in `AppSidebar.vue` using the `Globe` icon.
3. **Forms**: Leveraging `laravel/wayfinder` for type-safe forms and consistent validation handling.
4. **Pagination**: Custom `Pagination.vue` component to handle large site lists.
5. **Deletion**: Integrated shadcn-vue `Dialog` for secure confirmation before deleting sites.

### Branch: `002-site-management-ui`
### Next Steps: Execute Phase 2 (Core UI Components) and Phase 3 (Site List).