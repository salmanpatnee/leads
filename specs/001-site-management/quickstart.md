# Quickstart: Site Management

## Prerequisites
- PHP 8.3
- MySQL

## Setup
1. **Migrations**:
   ```bash
   php artisan migrate
   ```

## Usage

### Managing Sites (Admin)

**Create a Site:**
```bash
curl -X POST http://leads.test/api/sites \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"site_name": "My Blog", "domain": "blog.example.com"}'
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "site_name": "My Blog",
    "domain": "blog.example.com",
    "api_key": "550e8400-e29b-41d4-a716-446655440000",
    "is_active": true,
    ...
  }
}
```

### Using the API Key (Client)

Add the `X-API-Key` header to requests:
```bash
curl -X POST http://leads.test/api/leads \
  -H "X-API-Key: 550e8400-e29b-41d4-a716-446655440000" \
  ...
```

## Testing
Run the feature tests:
```bash
php artisan test --filter=SiteManagementTest
```
