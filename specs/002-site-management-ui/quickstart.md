# Quickstart: Site Management UI

## Setup
1. Ensure the database is migrated:
   ```bash
   php artisan migrate
   ```
2. Generate Wayfinder actions (if not done automatically):
   ```bash
   php artisan wayfinder:generate
   ```

## Key Routes
- `GET /sites`: List all sites
- `GET /sites/create`: Form to register a new site
- `GET /sites/{site}`: View site details and API key
- `GET /sites/{site}/edit`: Edit site information

## Development
- Frontend components are in `resources/js/pages/sites/`.
- Layout is provided by `AppLayout.vue`.
- Icons are from `lucide-vue-next`.
- Shadcn components are in `resources/js/components/ui/`.

## Testing
Run the site management feature tests:
```bash
php artisan test --filter=SiteManagement
```
(Note: You may need to create these tests if they don't exist yet).
