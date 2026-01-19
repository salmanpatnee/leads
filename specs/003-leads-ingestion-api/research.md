# Research & Decisions: Leads Ingestion API

## Decisions

### 1. Authentication Strategy
**Decision**: Use a custom Middleware `EnsureSiteApiKeyIsValid` to validate the `X-API-Key` header.
**Rationale**: 
- Lightweight and specific to the "Site" entity.
- Sanctum/Passport are overkill for simple machine-to-machine API key auth without user sessions.
- Allows cleanly separating auth logic from the controller.
- **Logging**: Failed attempts will be logged to the standard Laravel application log (`storage/logs/laravel.log`) using `Log::warning()`.
**Alternatives**: 
- **Inline Controller Check**: Clutters controller, violates separation of concerns.
- **Sanctum**: Designed more for user/SPA authentication.

### 2. Rate Limiting Implementation
**Decision**: Use Laravel's native `RateLimiter` facade defined in `bootstrap/app.php` (or `AppServiceProvider`) applied via route middleware.
**Rationale**:
- Built-in, robust, and supports cache backends (Redis/Database).
- Requirements specify 60 req/min/site. We can key the limiter by the Site ID or API Key.
**Alternatives**:
- **Custom Middleware**: Reinventing the wheel. Laravel's throttle middleware is sufficient.

### 3. Payload Size Limit (1MB)
**Decision**: Enforce max payload size via Laravel Validation Rule in Form Request AND PHP configuration where possible (though PHP `post_max_size` is usually higher).
**Rationale**:
- Spec requires rejecting payloads > 1MB.
- `StoreLeadRequest` will use validation rules (if feasible for JSON body size) or `LeadController` can check `Content-Length` header / raw body size before processing.
- *Refined Approach*: Use `ValidatePostSize` middleware globally (standard in Laravel) but since that defaults to `post_max_size` (usually 8MB+), we will add a specific check in the `StoreLeadRequest` or a middleware.
- **Chosen Implementation**: Add a check in `StoreLeadRequest`'s `authorize` or `prepareForValidation` method to abort with 422/413 if content length > 1MB.
**Alternatives**:
- **Web Server Config**: Hard to manage in shared dev environments/Herd without touching global config. App-level is safer for feature-specific constraints.

### 4. Controller Architecture
**Decision**: Single Action Invokable Controller `StoreLeadController` (or `LeadController` with `__invoke`).
**Rationale**:
- The requirement specifies an "invokable controller responsible solely for storing leads".
- Keeps the class small and focused (Single Responsibility Principle).
**Alternatives**:
- **Resource Controller (`LeadController@store`)**: Valid, but "invokable" was explicitly requested and fits the single-endpoint nature.

### 5. Database Schema
**Decision**: `form_data` stored as `json` column.
**Rationale**:
- Contact Form 7 forms are dynamic; schema is unknown.
- MySQL JSON type allows efficient storage and querying if needed later.

## Open Questions Resolved
- **Status Enum**: Defaults to 'new'.
- **Response Format**: `{ success: true, lead_id: 123 }`.
- **Max Payload**: 1MB.
- **Auth Logging**: Standard Application Log.