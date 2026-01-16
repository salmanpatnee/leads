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

## Implementation Order

### Phase 1: Core Data Layer
1. Create Site model, migration, factory
2. Create Lead model, migration, factory
3. Run migrations

### Phase 2: API Endpoint
1. Create API key authentication middleware
2. Create LeadApiController with store method
3. Create StoreLeadRequest for validation
4. Add API routes
5. Write API tests

### Phase 3: Sites Management
1. Create SiteController (index, create, store, edit, update, destroy)
2. Create form requests
3. Create Vue pages (Index, Create, Edit)
4. Add navigation link
5. Write tests

### Phase 4: Leads Management
1. Create LeadController (index, show, updateStatus)
2. Create Vue pages (Index with filters, Show)
3. Add navigation link
4. Write tests

### Phase 5: Analytics Dashboard
1. Create AnalyticsController with data aggregation
2. Install chart library (Chart.js via vue-chartjs)
3. Create analytics Vue page with charts
4. Write tests

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
