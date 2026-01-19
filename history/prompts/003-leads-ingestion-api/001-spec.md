---
id: 003-leads-ingestion-api-spec
title: leads-ingestion-api-spec
stage: spec
date: 2026-01-19T13:10:00Z
surface: cli
model: gemini-2.0-flash-thinking-exp-1219
feature: leads-ingestion-api
branch: 003-leads-ingestion-api
user: user
command: /sp.specify
labels: [spec, api, leads]
links:
  spec: specs/003-leads-ingestion-api/spec.md
files:
  - specs/003-leads-ingestion-api/spec.md
  - specs/003-leads-ingestion-api/checklists/requirements.md
tests: []
---

## Prompt

Create a Laravel-powered API responsible for storing lead records submitted from external WordPress sites. The system must store leads with the required fields id, site_id, form_name, form_data, ip_address, user_agent, submitted_at, created_at, and updated_at, where lead records are created exclusively via API and cannot be created or managed through any UI. The API must validate incoming requests, securely authenticate requests using a site-specific API key sent through the X-API-Key request header, associate each lead with the correct site based on the provided API key, and protect against unauthorized access, malformed payloads, and abuse. The specification should define the database schema, API endpoints, request and response formats, validation rules, authentication and authorization flow, error handling, rate limiting considerations, logging and auditing expectations, and extensibility for future integrations, while remaining implementation-ready and aligned with Laravel best practices.

## Response snapshot

Created feature branch `003-leads-ingestion-api` and specification file `specs/003-leads-ingestion-api/spec.md`. The spec defines:
- **User Stories**: Lead submission, Validation, Auth/Rate Limiting.
- **Requirements**: RESTful API, `X-API-Key` auth, JSON validation, rate limiting (60/min), logging.
- **Entities**: Lead, Site.
- **Success Criteria**: measurable outcomes for uptime, response time, and accuracy.

Validated with checklist `specs/003-leads-ingestion-api/checklists/requirements.md` - All passed.

## Outcome

- ✅ Impact: Defined core ingestion API for the system.
- 🧪 Tests: Defined acceptance criteria for API endpoints.
- 📄 Files: Spec and Checklist created.
- 🔮 Next prompts: /sp.plan
- 🧠 Reflection: Initial script failed on arg parsing, recovered by manually writing the spec file based on the template and user input.
