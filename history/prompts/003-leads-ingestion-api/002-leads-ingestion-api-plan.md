---
id: 002
title: Leads Ingestion API Plan
stage: plan
date: 2026-01-19
surface: CLI
model: gemini-2.0-flash-exp
feature: 003-leads-ingestion-api
branch: 003-leads-ingestion-api
user: User
command: /sp.plan
labels: [plan, design, api]
links:
  spec: specs/003-leads-ingestion-api/spec.md
  ticket: 
  adr: 
  pr: 
files:
  - specs/003-leads-ingestion-api/plan.md
  - specs/003-leads-ingestion-api/research.md
  - specs/003-leads-ingestion-api/data-model.md
  - specs/003-leads-ingestion-api/contracts/leads-api.yaml
  - specs/003-leads-ingestion-api/quickstart.md
tests: []
---

## Prompt

Build a Leads ingestion API, including creating a database migration for a leads table with columns id as primary key, site_id as a foreign key referencing the sites table, form_name as a nullable string to store the CF7 form identifier, form_data as a JSON column to store all submitted fields, ip_address and user_agent as nullable strings, submitted_at as a timestamp representing the original submission time, and standard timestamps; defining an Eloquent model with fillable properties for all columns except id and timestamps; implementing an API-only, invokable controller responsible solely for storing leads; validating incoming requests using a dedicated Form Request class to keep the controller clean and lean; authenticating requests by validating the site’s API key provided via the X-API-Key request header before persisting any data; associating each lead with the correct site based on the validated API key; and returning appropriate, consistent JSON responses for both success and failure scenarios while following Laravel best practices for security, validation, and maintainability.

## Response snapshot

Executed the plan workflow.
1. Parsed spec and initialized plan.
2. Analyzed requirements (API-first, simple JSON storage, API Key auth).
3. Created design artifacts:
   - `plan.md`: Implementation plan.
   - `research.md`: Decisions on Auth (Middleware), Rate Limit (Laravel native), and DB (JSON).
   - `data-model.md`: `Lead` model and `leads` table schema.
   - `contracts/leads-api.yaml`: OpenAPI definition.
   - `quickstart.md`: Testing guide.
4. Updated agent context.

## Outcome

- ✅ Impact: Created implementation plan and design artifacts.
- 🧪 Tests: None run yet.
- 📁 Files: Created 5 design docs.
- 🔁 Next prompts: /sp.tasks
- 🧠 Reflection: Plan creation was straightforward as requirements were clear.

## Evaluation notes (flywheel)

- Failure modes observed: None
- Graders run and results (PASS/FAIL): PASS
- Prompt variant (if applicable): N/A
- Next experiment (smallest change to try): N/A
