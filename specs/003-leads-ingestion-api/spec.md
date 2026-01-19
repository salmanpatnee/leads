# Feature Specification: Leads Ingestion API

**Feature Branch**: `003-leads-ingestion-api`
**Created**: 2026-01-19
**Status**: Draft
**Input**: User description: "Create a Laravel-powered API responsible for storing lead records submitted from external WordPress sites..."

## Clarifications

### Session 2026-01-19
- Q: What is the maximum allowed size for the JSON payload? → A: 1MB (Standard)
- Q: Where should failed authentication attempts be logged? → A: Standard Application Log

## User Scenarios & Testing

### User Story 1 - Submit New Lead (Priority: P1)

As an external WordPress site, I want to submit a new lead via a secure API endpoint so that the data is centralized in the Leads System.

**Why this priority**: This is the core function of the system. Without ingestion, there is no data to manage.

**Independent Test**: Can be tested using `curl` or Postman by sending a POST request with a valid API Key and payload.

**Acceptance Scenarios**:

1. **Given** a valid API Key for an active Site and a valid JSON payload, **When** the external system sends a POST request to `/api/leads`, **Then** the system responds with HTTP 201 Created and the new Lead ID.
2. **Given** a valid API Key and payload, **When** the request is processed, **Then** a new record is created in the database associated with that Site.

### User Story 2 - Validation & Error Handling (Priority: P2)

As a system administrator, I want the API to reject invalid or malformed data so that the database remains clean and reliable.

**Why this priority**: Prevents data corruption and ensures only useful leads are stored.

**Independent Test**: Send requests with missing fields, invalid JSON, or exceeding field lengths.

**Acceptance Scenarios**:

1. **Given** a payload missing the `form_name` field, **When** the request is sent, **Then** the system responds with HTTP 422 Unprocessable Content and a validation error message.
2. **Given** a payload with malformed JSON, **When** the request is sent, **Then** the system responds with a HTTP 400 Bad Request (or 422 depending on framework handling).

### User Story 3 - Authentication & Rate Limiting (Priority: P1)

As a system owner, I want to restrict access to valid API keys and limit request rates so that the system is protected from unauthorized access and abuse.

**Why this priority**: Security critical. Prevents spam and unauthorized data injection.

**Independent Test**: Send requests without headers, with invalid headers, and send rapid-fire requests.

**Acceptance Scenarios**:

1. **Given** a request missing the `X-API-Key` header, **When** sent, **Then** the system responds with HTTP 401 Unauthorized.
2. **Given** a request with an invalid `X-API-Key`, **When** sent, **Then** the system responds with HTTP 401 Unauthorized.
3. **Given** a Site has exceeded 60 requests in the last minute, **When** another request is sent, **Then** the system responds with HTTP 429 Too Many Requests.

### Edge Cases

- **Revoked Keys**: What happens if a key is valid format but the Site is marked inactive? (Should return 401).
- **Large Payloads**: How does the system handle extremely large `form_data` blobs? (Should enforce a reasonable max body size).
- **Database Downtime**: Graceful handling of DB connection failures (HTTP 500).

## Requirements

### Functional Requirements

- **FR-001**: The system MUST expose a RESTful endpoint `POST /api/leads` for lead submission.
- **FR-002**: The system MUST authenticate all requests using a custom header `X-API-Key`.
- **FR-003**: The system MUST validate that the API Key belongs to an active `Site` record.
- **FR-004**: The system MUST validate that the request body contains `form_name` (string) and `form_data` (array/json).
- **FR-005**: The system MUST store the lead with: `site_id`, `form_name`, `form_data`, `ip_address`, `user_agent`, and timestamps.
- **FR-006**: The system MUST default the lead status to "new" upon creation.
- **FR-007**: The system MUST enforce a rate limit of 60 requests per minute per Site API Key.
- **FR-008**: The system MUST return a JSON response with `success: true` and `lead_id` on success.
- **FR-009**: The system MUST NOT allow lead creation via the web UI (this is an API-only feature).
- **FR-010**: The system MUST log failed authentication attempts to the standard application log for security auditing.
- **FR-011**: The system MUST automatically capture `ip_address` and `user_agent` from the request headers.
- **FR-012**: The system MUST reject request payloads exceeding 1MB.

### Key Entities

- **Lead**: Represents a form submission. Attributes: `id`, `site_id`, `form_name`, `form_data` (JSON), `status`, `ip_address`, `user_agent`, `submitted_at`.
- **Site**: Represents the source of the lead. Attributes: `id`, `api_key`, `is_active`.

## Success Criteria

### Measurable Outcomes

- **SC-001**: 100% of requests with valid keys and payloads result in a stored database record.
- **SC-002**: API responds with HTTP 401 for 100% of requests with invalid or missing keys.
- **SC-003**: API response time (P95) is under 500ms for valid submissions.
- **SC-004**: System successfully blocks requests exceeding the 60/minute rate limit.