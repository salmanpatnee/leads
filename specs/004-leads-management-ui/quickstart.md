# Quickstart: Leads Management UI

## Prerequisites
- Ensure the database is migrated (tables `leads` and `sites` should exist).
- Ensure NPM dependencies are installed (`npm install`).

## Running Tests
Run the feature tests for the backend logic:
```bash
php artisan test --filter=LeadManagementTest
```

Run the browser tests for the UI (if set up):
```bash
php artisan dusk --filter=LeadsBrowserTest
```
*(Note: Dusk setup might be required)*

## Manual Testing
1. Seed the database with sites and leads:
   ```bash
   php artisan db:seed
   ```
   (Ensure `LeadSeeder` is called in `DatabaseSeeder`)

2. Serve the app:
   ```bash
   php artisan serve
   npm run dev
   ```

3. Navigate to `/leads` (login required).
4. Verify:
   - List loads with data.
   - Filtering by site works.
   - Search works.
   - Clicking "View" opens the detail page.
