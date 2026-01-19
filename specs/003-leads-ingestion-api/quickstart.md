# Quickstart: Testing the Leads Ingestion API

## Prerequisites

1. **Database Setup**: Ensure migrations are run.
   ```bash
   php artisan migrate
   ```
2. **Create a Site**: You need a valid API Key.
   ```bash
   php artisan tinker
   >>> \App\Models\Site::factory()->create(['name' => 'Test Site', 'is_active' => true]);
   ```
   *Copy the `api_key` generated.*

## Manual Testing (curl)

Replace `YOUR_API_KEY` with the key from above.

```bash
curl -X POST http://leads.test/api/leads \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "X-API-Key: YOUR_API_KEY" \
  -d 
{
    "form_name": "Contact Form 1",
    "form_data": {
        "email": "test@example.com",
        "message": "Hello World"
    }
}
```

**Expected Output (Success):**
```json
{
    "success": true,
    "lead_id": 1
}
```

**Test Payload > 1MB:**
Attempt to send a large file to verify rejection.

```bash
# Generate 1MB+ dummy file
# (Send via curl using @filename syntax)
```

## Running Automated Tests

Run the feature tests specifically for this endpoint:

```bash
php artisan test tests/Feature/Api/StoreLeadTest.php
```