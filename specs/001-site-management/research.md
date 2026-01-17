# Research: Site Management System

## Unknowns & Clarifications

### 1. API Key Format and Generation
- **Requirement**: "Secure and unique api_key automatically".
- **Decision**: Use UUID v4.
- **Rationale**: UUID v4 provides 122 bits of entropy, making collisions practically impossible. It is standard, supported natively by Laravel (`Str::uuid()`), and easy to validate.
- **Alternatives**: 
  - Random alphanumeric string (e.g., `Str::random(64)`): Good, but UUID is more standardized for IDs/Keys.
  - Hashed keys (Sanctum style): Provide better security if the DB is compromised, but the requirement implies the key *is* the identifier for the site in the header `X-API-Key`. If we hash it, we can't look it up directly without a separate ID in the request. Since the requirement specifies `X-API-Key: {site_api_key}`, we need to store it plainly or use a lookup hash. For simplicity and this specific requirement, storing UUID is acceptable for a "Site" identity, similar to a client ID.

### 2. Authentication for Site Management vs. Site Usage
- **Requirement**: "Web Authentication" for management; "API-key mechanism" for usage.
- **Decision**: 
  - **Management**: Use Laravel's default `auth:web` middleware. Routes will be in `routes/web.php` (or `routes/api.php` using Sanctum stateful auth if SPA). Given "Backend Only" and "future Inertia", putting them in `routes/web.php` or `routes/api.php` with `auth:sanctum` is best. I will use `routes/api.php` with `auth:sanctum` which handles session auth for SPA/Internal usage nicely.
  - **Usage (Middleware)**: Create a custom middleware `EnsureSiteApiKeyIsValid` that checks `X-API-Key`.
- **Rationale**: Separates concerns. Admins use Session/Sanctum; Sites use API Key.

### 3. API Response Format
- **Requirement**: "Formats responses using API Resources".
- **Decision**: Use Laravel `JsonResource`.
- **Rationale**: Provides a transformation layer between Models and JSON output, ensuring consistent responses.

## Architecture Decisions

- **Controller**: `SiteController` (Standard Resource Controller).
- **Validation**: `StoreSiteRequest` and `UpdateSiteRequest`.
- **Model**: `Site` model with `guarded` (or `fillable`) and `casts` (boolean for `is_active`).
- **Database**: `sites` table.
