# Feature Specification: Leads Management UI

## 1. Overview

### Problem Statement
Users currently lack a centralized interface to view, manage, and analyze the leads collected from various WordPress sites. While the system ingests data via API, there is no visual dashboard to browse these submissions, making it difficult to monitor activity, find specific leads, or export data for reporting.

### Solution
Implement a dedicated Leads Management UI within the existing dashboard. This interface will provide a paginated, sortable list of leads with robust filtering (by site and date) and search capabilities (within form data). It will also include a detailed view for inspecting individual lead submissions and an export function to download lead data as CSV.

### Success Criteria
- **Usability**: Admins can locate a specific lead within 3 clicks or one search query.
- **Performance**: Users see results instantly (leads list loads within 1 second for standard page sizes).
- **Consistency**: The UI matches the existing visual style of the Site Management section, ensuring a seamless user experience.

## Clarifications

### Session 2026-01-19
- Q: Should the leads UI allow updating the lead status (e.g., New to Contacted)? → A: Read-only
- Q: Should the Detailed View be a separate page or a modal? → A: Separate Page
- Q: How should the search function handle the JSON form data? → A: Specific Fields (email, name)
- Q: Should exporting functionality be included? → A: Out of scope (Ignore for now)
- Q: What should be displayed in the "Summary" column of the list? → A: Summary View (1-2 primary fields like Name/Email)

## 2. User Scenarios

### 2.1. Browse and Filter Leads
**As an** Administrator
**I want to** view a list of recent leads and filter them by specific sites or date ranges
**So that** I can monitor performance for a particular campaign or time period.

**Acceptance Criteria:**
- Sidebar contains a "Leads" link.
- Clicking "Leads" displays a paginated list of all leads, sorted by submission date (newest first).
- A filter bar is present with:
    - "Site" dropdown (listing all active sites).
    - "Date Range" picker (start and end date).
- Changing filters updates the list asynchronously (or via reload) to show only matching records.

### 2.2. Search Leads
**As an** Administrator
**I want to** search for leads containing specific text within their form submissions
**So that** I can find inquiries from a specific person or topic (e.g., searching for an email address or name).

**Acceptance Criteria:**
- A search input is available in the toolbar.
- Entering text searches across the `form_data` JSON content.
- Results are displayed in the same list format.

### 2.3. View Lead Details
**As an** Administrator
**I want to** view the full details of a specific lead
**So that** I can read the entire form submission and see technical metadata.

**Acceptance Criteria:**
- Each row in the list has a "View" action (or is clickable).
- Clicking a lead navigates to a separate Detailed View page.
- Detailed View displays:
    - **Site Information**: Name, Domain.
    - **Submission Metadata**: Date/Time, IP Address, User Agent.
    - **Form Data**: A readable display of all key-value pairs from the submitted form.

## 3. Functional Requirements

### 3.1. Navigation & Layout
- Add "Leads" to the main sidebar navigation.
- Use the existing application layout (headers, padding, card styles) consistent with "Site Management".

### 3.2. Leads List View
- **Columns**:
    - **Status** (New/Contacted/Converted): Read-only display of the current status.
    - **Submission Date**: Formatted relative (e.g., "2 hours ago") or absolute date-time.
    - **Site**: Name of the source site.
    - **Form Name**: Identifier of the form.
    - **Summary**: Brief excerpt or key fields (e.g., "email" if present) from `form_data`.
    - **Actions**: "View" button.
- **Pagination**: Standard pagination controls (Next, Previous, Page numbers). Default page size: 15 or 20.
- **Sorting**: Default sort is `submitted_at` descending.

### 3.3. Filtering & Search
- **Site Filter**: Dropdown populated from the `sites` table. Option for "All Sites".
- **Date Filter**: Date range picker. Defaults to "All Time" or current month.
- **Search**: Text search query targeting common fields (e.g., "email", "name", "first_name", "last_name") within the `form_data` JSON column.

### 3.4. Lead Details
- **Route**: Dedicated page (e.g., `/leads/{id}`).
- Display `lead.status` (Read-only).
- Display `site.name` and `site.domain` clearly.
- Display `lead.ip_address` and `lead.user_agent`.
- Render `lead.form_data` (JSON) in a user-friendly Key-Value format (e.g., Label: Value).
- Allow navigation back to the list.

## 4. Technical Constraints & Assumptions
- **Framework**: Use existing Laravel + Vue + Inertia stack.
- **Styling**: Tailwind CSS, utilizing existing UI components (Buttons, Tables, Cards) to match "Site Management".
- **Database**: 
    - Use `leads` table.
    - Use `sites` table for relationship data.
    - `form_data` is stored as JSON.
- **Performance**: Search operations on JSON columns should be optimized where possible, but exact indexing strategy is implementation detail.
- **Permissions**: Only authenticated users (Admins) can access this module.

## 5. Open Questions
- None at this stage.