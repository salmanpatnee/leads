# WordPress Contact Form 7 Leads System

## Overview
Build a system to receive, store, and analyze leads from multiple WordPress sites using Contact Form 7. The system includes a secure API endpoint and a dashboard with business analytics.

---

## Data Model

### Sites Table
Represents WordPress sites that send leads.
```
sites
├── id (primary)
├── name (string) - Friendly name for the site
├── domain (string, unique) - WordPress site domain
├── api_key (string, unique) - Authentication key
├── is_active (boolean, default: true)
├── timestamps
```

### Leads Table
Stores form submissions with flexible JSON storage.
```
leads
├── id (primary)
├── site_id (foreign key → sites)
├── form_name (string, nullable) - CF7 form identifier
├── form_data (json) - All submitted fields
├── status (enum: new, contacted, converted) - Conversion tracking
├── ip_address (string, nullable)
├── user_agent (string, nullable)
├── submitted_at (timestamp) - Original submission time
├── timestamps
```

---

## API Design

### Authentication
- **Method**: API key per site (simple, secure, trackable)
- **Header**: `X-API-Key: {site_api_key}`
- **Rate Limit**: 60 requests per minute per site

### Endpoint
```
POST /api/leads

Headers:
  X-API-Key: {api_key}
  Content-Type: application/json

Body:
{
  "form_name": "contact-form-1",  // optional
  "form_data": {                  // required, flexible structure
    "your-name": "John Doe",
    "your-email": "john@example.com",
    "your-message": "Hello..."
  },
  "submitted_at": "2026-01-16T10:30:00Z"  // optional
}

Response (201):
{
  "success": true,
  "lead_id": 123
}
```

---

## Dashboard Features

### 1. Sites Management
- List all registered WordPress sites
- Add new site (generates API key)
- Edit site details
- Regenerate API key
- Activate/deactivate sites

### 2. Leads List
- Paginated table of all leads
- Filters: by site, by status, by date range
- Search within form data
- Bulk status update
- Export to CSV (future)

### 3. Lead Detail View
- Full form data display
- Status change (new → contacted → converted)
- Metadata (IP, user agent, timestamps)
- Site information

### 4. Analytics Dashboard
Charts and metrics for business insights:

**Volume Trends**
- Leads over time (line chart: daily/weekly/monthly toggle)
- Total leads count with period comparison

**Source Tracking**
- Leads by site (bar/pie chart)
- Leads by form name (bar chart)
- Top performing sites table

**Conversion Funnel**
- Status breakdown (new/contacted/converted)
- Conversion rate percentage
- Funnel visualization

---

## Files to Create/Modify

### Backend (Laravel)

**Models & Migrations**
- `app/Models/Site.php`
- `app/Models/Lead.php`
- `database/migrations/xxxx_create_sites_table.php`
- `database/migrations/xxxx_create_leads_table.php`
- `database/factories/SiteFactory.php`
- `database/factories/LeadFactory.php`

**Controllers**
- `app/Http/Controllers/Api/LeadApiController.php` - API endpoint
- `app/Http/Controllers/SiteController.php` - Sites CRUD
- `app/Http/Controllers/LeadController.php` - Leads management
- `app/Http/Controllers/AnalyticsController.php` - Dashboard analytics

**Form Requests**
- `app/Http/Requests/Api/StoreLeadRequest.php`
- `app/Http/Requests/StoreSiteRequest.php`
- `app/Http/Requests/UpdateSiteRequest.php`
- `app/Http/Requests/UpdateLeadStatusRequest.php`

**Middleware**
- `app/Http/Middleware/AuthenticateSiteApiKey.php`

**Routes**
- `routes/api.php` - API routes
- `routes/web.php` - Dashboard routes (sites, leads, analytics)

### Frontend (Vue/Inertia)

**Pages**
- `resources/js/pages/sites/Index.vue` - Sites list
- `resources/js/pages/sites/Create.vue` - Add site form
- `resources/js/pages/sites/Edit.vue` - Edit site
- `resources/js/pages/leads/Index.vue` - Leads list with filters
- `resources/js/pages/leads/Show.vue` - Lead detail
- `resources/js/pages/analytics/Index.vue` - Analytics dashboard

**Components**
- `resources/js/components/LeadsTable.vue`
- `resources/js/components/LeadStatusBadge.vue`
- `resources/js/components/charts/LeadsOverTimeChart.vue`
- `resources/js/components/charts/LeadsBySiteChart.vue`
- `resources/js/components/charts/ConversionFunnelChart.vue`

### Tests (Pest)

**Feature Tests**
- `tests/Feature/Api/LeadApiTest.php` - API endpoint tests
- `tests/Feature/SiteManagementTest.php` - Sites CRUD tests
- `tests/Feature/LeadManagementTest.php` - Leads management tests
- `tests/Feature/AnalyticsTest.php` - Analytics endpoint tests

---

## Implementation Order (MVP-Based)

Each MVP is spec-driven: write tests first, then implement until tests pass.

---

### MVP 1: Site Model & Factory
**Goal**: Establish the Site data structure with testable factories.

**Spec (Tests)**:
```php
// tests/Feature/Models/SiteTest.php
it('creates a site with required fields');
it('generates a unique api_key on creation');
it('enforces unique domain constraint');
it('has is_active defaulting to true');
it('can be soft-toggled active/inactive');
```

**Deliverables**:
- [ ] `database/migrations/xxxx_create_sites_table.php`
- [ ] `app/Models/Site.php` with fillable, casts, and `generateApiKey()` method
- [ ] `database/factories/SiteFactory.php`
- [ ] `tests/Feature/Models/SiteTest.php`

**Done when**: `php artisan test --filter=SiteTest` passes.

---

### MVP 2: Lead Model & Factory
**Goal**: Establish the Lead data structure with site relationship.

**Spec (Tests)**:
```php
// tests/Feature/Models/LeadTest.php
it('creates a lead with form_data json');
it('belongs to a site');
it('has status defaulting to new');
it('casts form_data to array');
it('casts submitted_at to datetime');
```

**Deliverables**:
- [ ] `database/migrations/xxxx_create_leads_table.php`
- [ ] `app/Models/Lead.php` with relationship, fillable, casts
- [ ] `database/factories/LeadFactory.php`
- [ ] `tests/Feature/Models/LeadTest.php`

**Done when**: `php artisan test --filter=LeadTest` passes.

---

### MVP 3: API Authentication Middleware
**Goal**: Secure API with site-based API key authentication.

**Spec (Tests)**:
```php
// tests/Feature/Api/ApiAuthenticationTest.php
it('rejects requests without X-API-Key header', 401);
it('rejects requests with invalid API key', 401);
it('rejects requests from inactive sites', 403);
it('accepts requests with valid API key from active site');
it('attaches authenticated site to request');
```

**Deliverables**:
- [ ] `app/Http/Middleware/AuthenticateSiteApiKey.php`
- [ ] Register middleware in `bootstrap/app.php`
- [ ] `tests/Feature/Api/ApiAuthenticationTest.php`

**Done when**: `php artisan test --filter=ApiAuthenticationTest` passes.

---

### MVP 4: Lead Submission API
**Goal**: Accept and store leads from WordPress sites.

**Spec (Tests)**:
```php
// tests/Feature/Api/LeadApiTest.php
it('stores a lead with valid payload', 201);
it('returns lead_id in response');
it('validates form_data is required');
it('validates form_data must be array/object');
it('stores optional form_name');
it('stores optional submitted_at or defaults to now');
it('captures ip_address from request');
it('captures user_agent from request');
it('associates lead with authenticated site');
```

**Deliverables**:
- [ ] `app/Http/Controllers/Api/LeadApiController.php`
- [ ] `app/Http/Requests/Api/StoreLeadRequest.php`
- [ ] Route in `routes/api.php`: `POST /api/leads`
- [ ] `tests/Feature/Api/LeadApiTest.php`

**Done when**: `php artisan test --filter=LeadApiTest` passes.

---

### MVP 5: Sites CRUD Backend
**Goal**: Backend endpoints for managing WordPress sites.

**Spec (Tests)**:
```php
// tests/Feature/SiteManagementTest.php
it('requires authentication for all site routes');
it('lists all sites paginated');
it('shows create site form');
it('stores a new site with generated api_key');
it('validates name and domain are required');
it('validates domain is unique');
it('shows edit site form');
it('updates site name and domain');
it('regenerates api_key on demand');
it('toggles site active status');
it('deletes a site');
```

**Deliverables**:
- [ ] `app/Http/Controllers/SiteController.php`
- [ ] `app/Http/Requests/StoreSiteRequest.php`
- [ ] `app/Http/Requests/UpdateSiteRequest.php`
- [ ] Routes in `routes/web.php`
- [ ] `tests/Feature/SiteManagementTest.php`

**Done when**: `php artisan test --filter=SiteManagementTest` passes.

---

### MVP 6: Sites Dashboard UI
**Goal**: Vue/Inertia pages for site management.

**Spec (Browser Tests)**:
```php
// tests/Browser/SiteManagementBrowserTest.php
it('displays sites list with name, domain, status');
it('shows API key with copy button');
it('creates a new site via form');
it('edits an existing site');
it('confirms before regenerating API key');
it('toggles site active status inline');
```

**Deliverables**:
- [ ] `resources/js/pages/sites/Index.vue`
- [ ] `resources/js/pages/sites/Create.vue`
- [ ] `resources/js/pages/sites/Edit.vue`
- [ ] Navigation link in layout
- [ ] `tests/Browser/SiteManagementBrowserTest.php`

**Done when**: Browser tests pass and UI is functional.

---

### MVP 7: Leads List Backend
**Goal**: Backend for viewing and filtering leads.

**Spec (Tests)**:
```php
// tests/Feature/LeadManagementTest.php
it('requires authentication');
it('lists leads paginated, newest first');
it('filters leads by site_id');
it('filters leads by status');
it('filters leads by date range');
it('searches within form_data json');
it('eager loads site relationship');
```

**Deliverables**:
- [ ] `app/Http/Controllers/LeadController.php` (index method)
- [ ] Routes in `routes/web.php`
- [ ] `tests/Feature/LeadManagementTest.php`

**Done when**: `php artisan test --filter=LeadManagementTest` passes.

---

### MVP 8: Lead Detail & Status Update
**Goal**: View lead details and update conversion status.

**Spec (Tests)**:
```php
// tests/Feature/LeadDetailTest.php
it('shows lead with all form_data fields');
it('shows lead metadata (ip, user_agent, timestamps)');
it('shows associated site info');
it('updates lead status to contacted');
it('updates lead status to converted');
it('validates status is valid enum value');
```

**Deliverables**:
- [ ] `LeadController@show` method
- [ ] `LeadController@updateStatus` method
- [ ] `app/Http/Requests/UpdateLeadStatusRequest.php`
- [ ] `tests/Feature/LeadDetailTest.php`

**Done when**: `php artisan test --filter=LeadDetailTest` passes.

---

### MVP 9: Leads Dashboard UI
**Goal**: Vue/Inertia pages for leads management.

**Spec (Browser Tests)**:
```php
// tests/Browser/LeadsBrowserTest.php
it('displays leads table with site, form, status, date');
it('paginates leads list');
it('filters by site dropdown');
it('filters by status dropdown');
it('filters by date range picker');
it('searches form data');
it('navigates to lead detail');
it('changes status from detail view');
```

**Deliverables**:
- [ ] `resources/js/pages/leads/Index.vue`
- [ ] `resources/js/pages/leads/Show.vue`
- [ ] `resources/js/components/LeadsTable.vue`
- [ ] `resources/js/components/LeadStatusBadge.vue`
- [ ] Navigation link in layout
- [ ] `tests/Browser/LeadsBrowserTest.php`

**Done when**: Browser tests pass and UI is functional.

---

### MVP 10: Analytics Data Endpoints
**Goal**: Backend aggregations for dashboard charts.

**Spec (Tests)**:
```php
// tests/Feature/AnalyticsTest.php
it('requires authentication');
it('returns leads count by day for date range');
it('returns leads count by site');
it('returns leads count by form_name');
it('returns leads count by status');
it('returns conversion rate percentage');
it('compares current period to previous period');
```

**Deliverables**:
- [ ] `app/Http/Controllers/AnalyticsController.php`
- [ ] Routes in `routes/web.php`
- [ ] `tests/Feature/AnalyticsTest.php`

**Done when**: `php artisan test --filter=AnalyticsTest` passes.

---

### MVP 11: Analytics Dashboard UI
**Goal**: Charts and visualizations for business insights.

**Spec (Browser Tests)**:
```php
// tests/Browser/AnalyticsBrowserTest.php
it('displays leads over time chart');
it('toggles between daily/weekly/monthly view');
it('displays leads by site chart');
it('displays conversion funnel');
it('shows period comparison stats');
```

**Deliverables**:
- [ ] Install `vue-chartjs` and `chart.js`
- [ ] `resources/js/pages/analytics/Index.vue`
- [ ] `resources/js/components/charts/LeadsOverTimeChart.vue`
- [ ] `resources/js/components/charts/LeadsBySiteChart.vue`
- [ ] `resources/js/components/charts/ConversionFunnelChart.vue`
- [ ] Navigation link in layout
- [ ] `tests/Browser/AnalyticsBrowserTest.php`

**Done when**: Browser tests pass and charts render correctly.

---

### MVP Completion Checklist

| MVP | Description | Tests | Status |
|-----|-------------|-------|--------|
| 1 | Site Model & Factory | `SiteTest` | ⬜ |
| 2 | Lead Model & Factory | `LeadTest` | ⬜ |
| 3 | API Authentication | `ApiAuthenticationTest` | ⬜ |
| 4 | Lead Submission API | `LeadApiTest` | ⬜ |
| 5 | Sites CRUD Backend | `SiteManagementTest` | ⬜ |
| 6 | Sites Dashboard UI | Browser tests | ⬜ |
| 7 | Leads List Backend | `LeadManagementTest` | ⬜ |
| 8 | Lead Detail & Status | `LeadDetailTest` | ⬜ |
| 9 | Leads Dashboard UI | Browser tests | ⬜ |
| 10 | Analytics Endpoints | `AnalyticsTest` | ⬜ |
| 11 | Analytics Dashboard UI | Browser tests | ⬜ |

---

## Verification Plan

1. **API Testing**
   - Use tinker to create a test site
   - POST to `/api/leads` with valid/invalid API keys
   - Verify lead is stored in database

2. **Dashboard Testing**
   - Log in to dashboard
   - Create a new site, verify API key generation
   - View leads list, apply filters
   - Change lead status
   - View analytics charts

3. **Automated Tests**
   - Run `php artisan test --compact` after each phase
   - Ensure all tests pass before moving to next phase

---

## WordPress Integration Notes

For the WordPress plugin (to be built separately or documented):

```php
// WordPress side - hook into CF7 submission
add_action('wpcf7_mail_sent', function($contact_form) {
    $submission = WPCF7_Submission::get_instance();

    wp_remote_post('https://your-laravel-app.com/api/leads', [
        'headers' => [
            'X-API-Key' => 'your-site-api-key',
            'Content-Type' => 'application/json'
        ],
        'body' => json_encode([
            'form_name' => $contact_form->title(),
            'form_data' => $submission->get_posted_data(),
            'submitted_at' => date('c')
        ])
    ]);
});
```

---

## Tech Stack Summary

- **Backend**: Laravel 12, PHP 8.3
- **Frontend**: Vue 3, Inertia.js v2, Tailwind CSS 4
- **Charts**: Chart.js via vue-chartjs
- **Auth**: Laravel Fortify (existing)
- **Testing**: Pest v4
- **Database**: SQLite (existing, can be swapped for MySQL/PostgreSQL)
