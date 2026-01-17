# Feature Specification: Site Management System

**Feature Branch**: `001-site-management`
**Created**: Saturday, January 17, 2026
**Status**: Draft
**Input**: User description: "The system should stores and creates a site record with all required fields (id,site_name,domain,api_key,is_active,timestamps), automatically generates a secure and unique api_key at the time of creation, enforces a uniqueness constraint on the domain field to prevent duplicate site entries, sets is_active to true by default upon creation, and allows the site to be safely toggled between active and inactive states without deleting the record."

## Clarifications

### Session 2026-01-17

- Q: Scope of implementation (UI vs Backend)? → A: **Backend Only** - API endpoints and controllers only; UI components will be built in a future feature.
- Q: API key delivery method? → A: **JSON response on creation (201)**.
- Q: API key format? → A: **UUID v4 (36 chars)**.
- Q: Domain casing handling? → A: **Lowercase Normalization** (always store and compare in lowercase).
- Q: Endpoint protection? → A: **Web Authentication** (Protected by Laravel's standard session-based auth).

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Register New Site (Priority: P1)

As an administrator, I want to register a new WordPress site in the system by providing its name and domain, so that it can be assigned a unique API key for sending leads.

**Why this priority**: This is the core functionality required to onboard sites and enable data collection.

**Independent Test**: Can be tested by sending a creation request (via API tool or test script) and verifying the record exists in the database with a generated API key.

**Acceptance Scenarios**:

1. **Given** valid site name and unique domain, **When** administrator submits creation request, **Then** a new site record is created with active status and a unique API key is returned in the response.
2. **Given** a domain that already exists in the system, **When** administrator attempts to create a site with that domain, **Then** the system rejects the request with a "Duplicate Domain" error.
3. **Given** missing site name or domain, **When** creation is attempted, **Then** the system rejects the request with validation errors.

---

### User Story 2 - Manage Site Status and Lifecycle (Priority: P2)

As an administrator, I want to view, toggle status, and delete sites, so that I can fully manage the lifecycle of registered sites.

**Why this priority**: Essential for managing site lifecycle, blocking unauthorized sites, and removing erroneous entries.

**Independent Test**: Can be tested by changing status or deleting a site via API and verifying the database state.

**Acceptance Scenarios**:

1. **Given** an active site, **When** administrator toggles status to inactive, **Then** the site record's `is_active` field updates to `false` and the record is preserved.
2. **Given** an inactive site, **When** administrator toggles status to active, **Then** the site record's `is_active` field updates to `true`.
3. **Given** a site with no associated leads, **When** administrator requests deletion, **Then** the site record is permanently removed from the database.
4. **Given** a site with associated leads (future state), **When** administrator requests deletion, **Then** the system MUST prevent deletion to preserve data integrity.

### Edge Cases

- **Domain Casing**: Domains entered with different casing (e.g., `example.com` vs `EXAMPLE.COM`) are normalized to lowercase before storage and uniqueness checks.
- **API Key Collision**: The system uses UUID v4 to ensure a collision probability that is effectively zero.
- **Deletion Integrity**: Deletion should be blocked if the site has dependent data (leads). Since the leads table does not exist yet, this constraint applies to future integration.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST allow creation of a Site record requiring `site_name` and `domain`.
- **FR-002**: System MUST automatically generate a cryptographically secure, unique `api_key` (UUID v4 format) for each new Site and return it in the creation response.
- **FR-003**: System MUST enforce a unique constraint on the `domain` field to prevent duplicates.
- **FR-004**: System MUST normalize the `domain` field to lowercase before storage and validation.
- **FR-005**: System MUST set the `is_active` flag to `true` by default upon Site creation.
- **FR-006**: System MUST allow updating the `is_active` status (true/false) for an existing Site.
- **FR-007**: System MUST not allow deletion of Site records via the toggle functionality (soft delete or status change only).
- **FR-008**: System MUST record `created_at` and `updated_at` timestamps for all Site operations.
- **FR-009**: **(Constraint)** Implementation MUST be Backend Only. No frontend UI (Inertia/Vue) components are to be created in this phase.
- **FR-010**: System MUST protect all site management endpoints using standard web authentication middleware.
- **FR-011**: System MUST allow permanent deletion of Site records via a specific DELETE endpoint.
- **FR-012**: System MUST prevent deletion of Site records if they have associated leads (to be enforced when leads feature is implemented).

### Key Entities *(include if feature involves data)*

- **Site**: Represents a client WordPress website.
    - `id`: Unique identifier (Primary Key)
    - `site_name`: Human-readable name of the site
    - `domain`: The website's domain (Unique)
    - `api_key`: Secure token for API authentication (Unique)
    - `is_active`: Boolean flag for site status
    - `timestamps`: Creation and modification times

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% of successfully created sites are assigned a unique API key immediately.
- **SC-002**: System rejects 100% of duplicate domain registration attempts.
- **SC-003**: Site status changes are reflected in the database immediately (real-time consistency).