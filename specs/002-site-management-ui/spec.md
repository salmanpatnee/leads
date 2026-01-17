# Feature Specification: Site Management UI

**Feature Branch**: `002-site-management-ui`
**Created**: Saturday, January 17, 2026
**Status**: Draft
**Input**: User description: "create UI for site management feature that includes adding a dedicated “Sites” menu item in the application sidebar to access site management, displaying a list view of all sites with visible columns for name, domain, and status, securely showing each site’s API key with a copy-to-clipboard action, providing an option to create a new site via a dedicated form page, and allowing administrators to edit existing sites, ensuring the UI follows consistent layout patterns, role-based access for admins, clear validation feedback, and a user-friendly management experience. Map according to @app\Http\Controllers\SiteController.php"

## Clarifications

### Session 2026-01-17

- Q: How should site deletion be exposed in the UI? → A: **Add Delete button to both List and Edit pages, requiring a confirmation modal.**
- Q: How should the API Key be displayed on the Details page? → A: **Always visible** (Simplest implementation, persistent access).
- Q: Which layout pattern should the Sites UI follow? → A: **Standard Admin Layout** (Sidebar + Topbar) consistent with existing pages.
- Q: What searching and filtering capabilities should the Sites Index have? → A: **Basic Search & Filter** (Search by name/domain and filter by status).
- Q: How should pagination be handled for the Sites list? → A: **Standard Pagination** (Classic pagination with page numbers/navigation).

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Access and View Sites List (Priority: P1)

As an administrator, I want to access a dedicated "Sites" section from the sidebar and view a list of all registered sites, so that I can see an overview of managed domains and their statuses.

**Why this priority**: Primary entry point for the feature; without this, users cannot access site management functions.

**Independent Test**: Can be tested by logging in as admin, clicking the "Sites" sidebar link, and verifying the table displays existing sites with correct columns (Name, Domain, Status).

**Acceptance Scenarios**:

1. **Given** an authenticated administrator, **When** they view the application sidebar, **Then** they see a "Sites" menu item.
2. **Given** the "Sites" menu item, **When** clicked, **Then** the user is navigated to the Sites Index page (`/sites`).
3. **Given** the Sites Index page, **When** loaded, **Then** it displays a list of sites with columns: Name, Domain, Status, and Actions (Edit, Delete).
4. **Given** multiple sites exist, **When** the user types in the search box, **Then** the list filters to match the Name or Domain.
5. **Given** more than 15 sites, **When** viewing the list, **Then** pagination controls are visible and functional.
6. **Given** no sites exist, **When** the page loads, **Then** an empty state message is displayed.

---

### User Story 2 - Register New Site (Priority: P1)

As an administrator, I want to create a new site via a dedicated form, so that I can register new WordPress instances for lead collection.

**Why this priority**: Essential for onboarding new sites; core CRUD functionality.

**Independent Test**: Can be tested by clicking "Create Site", filling the form, submitting, and verifying redirection to the details page with a success message.

**Acceptance Scenarios**:

1. **Given** the Sites Index page, **When** the user clicks "Create Site", **Then** they are navigated to the Create Site page (`/sites/create`).
2. **Given** the Create Site form, **When** valid Name and Domain are submitted, **Then** the site is created, and the user is redirected to the Site Details page.
3. **Given** the Create Site form, **When** invalid data (e.g., empty fields, duplicate domain) is submitted, **Then** validation errors are displayed inline matching server responses.

---

### User Story 3 - View Details and API Key (Priority: P1)

As an administrator, I want to view site details and securely access the API key, so that I can configure the remote WordPress site.

**Why this priority**: The API key is critical for the integration and is generated only upon creation; users need a way to retrieve it.

**Independent Test**: Can be tested by viewing a site's details and using the "Copy" button for the API Key.

**Acceptance Scenarios**:

1. **Given** the Site Details page (`/sites/{id}`), **When** loaded, **Then** it displays full site information including Name, Domain, Status, and the API Key (which is always visible).
2. **Given** the API Key display, **When** the user clicks the "Copy" button, **Then** the key is copied to the system clipboard and a "Copied" toast/tooltip appears.
3. **Given** a newly created site, **When** redirected from creation, **Then** a success message is shown.

---

### User Story 4 - Edit Site Information (Priority: P2)

As an administrator, I want to edit site details (Name, Domain, Status), so that I can correct errors or update site configurations.

**Why this priority**: Maintenance of site records.

**Independent Test**: Can be tested by navigating to the Edit page, changing values, submitting, and verifying updates on the Details page.

**Acceptance Scenarios**:

1. **Given** the Site Details or Index page, **When** the user clicks "Edit", **Then** they are navigated to the Edit Site page (`/sites/{id}/edit`).
2. **Given** the Edit form, **When** the user updates the Name, Domain, or toggles Status and submits, **Then** the changes are saved and they are redirected to the Details page with a success message.
3. **Given** the Edit form, **When** invalid data is provided, **Then** validation errors are displayed.

---

### User Story 5 - Delete Site (Priority: P3)

As an administrator, I want to delete a site that is no longer needed, so that I can keep the registry clean.

**Why this priority**: Lifecycle completeness.

**Independent Test**: Click Delete on a site, confirm the modal, and verify the site is removed from the list.

**Acceptance Scenarios**:

1. **Given** the Sites Index or Edit page, **When** the user clicks "Delete", **Then** a confirmation modal appears warning about the action.
2. **Given** the confirmation modal, **When** the user confirms, **Then** the site is deleted, and the user is redirected to the Index page with a success message.
3. **Given** the confirmation modal, **When** the user cancels, **Then** the modal closes and no data is changed.

### Edge Cases

- **Clipboard Permission Denied**: If the browser denies clipboard access, the system should gracefully handle the "Copy" action (e.g., select the text or show a manual copy instruction).
- **Concurrent Edits**: If two admins edit a site simultaneously, the last write wins (standard behavior, no specific locking required for this iteration).
- **Deleted Site**: If a user attempts to view/edit a site that was just deleted by another admin, the system should display a 404 Not Found page or redirect to Index with an error.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST display a "Sites" link in the main navigation sidebar for authenticated administrators.
- **FR-002**: System MUST render the Sites Index page displaying a table of sites with columns: `site_name`, `domain`, `is_active` (status), and action buttons (View, Edit, Delete).
- **FR-003**: System MUST provide a "Create Site" button on the Index page that links to the creation form.
- FR-004: System MUST render a Create Site form with fields for `site_name` and `domain`.
- **FR-005**: System MUST display system validation errors (e.g., "The domain has already been registered") inline on forms.
- **FR-006**: System MUST render a Site Details page displaying `site_name`, `domain`, `api_key`, `is_active`, and timestamps.
- **FR-007**: System MUST provide a "Copy to Clipboard" feature for the `api_key` on the Details page.
- **FR-008**: System MUST render an Edit Site form pre-filled with existing data, allowing updates to `site_name`, `domain`, and `is_active`.
- **FR-009**: UI MUST follow the application's existing design system (using layout components, buttons, and table styles consistent with other modules).
- **FR-010**: System MUST restrict access to all UI routes to authenticated users.
- **FR-011**: System MUST provide a Delete button on both the Index list rows and the Edit form.
- **FR-012**: System MUST require confirmation via a modal/dialog before processing a site deletion.
- **FR-013**: System MUST display the API key in clear text on the Details page (no default masking).
- **FR-014**: UI MUST use the standard application layout including sidebar navigation and top bar.
- **FR-015**: System MUST provide search functionality on the Index page to filter the list by `site_name` or `domain`.
- **FR-016**: System MUST provide status filtering on the Index page to toggle between All, Active, and Inactive sites.
- **FR-017**: System MUST provide pagination for the Sites list when the number of records exceeds the default page limit.

### Key Entities *(include if feature involves data)*

- **Site**:
    - `site_name`: String, displayed in lists and headers.
    - `domain`: String, displayed as a link or text.
    - `api_key`: String (UUID), displayed with copy functionality.
    - `is_active`: Boolean, displayed as a status badge (Active/Inactive) and toggleable in Edit form.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Administrators can successfully create a new site and retrieve its API key within 1 minute.
- **SC-002**: 100% of validation errors returned by the system are visible to the user in the UI.
- **SC-003**: The "Sites" menu item is visible on all pages when logged in as administrator.
- **SC-004**: Users can successfully copy the API key to clipboard with a single click.
- **SC-005**: Zero accidental deletions occur (enforced by confirmation modal).
