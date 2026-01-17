# Data Model: Site Management

## Entities

### Site
Represents a registered WordPress site authorized to send leads.

| Field | Type | Attributes | Description |
|-------|------|------------|-------------|
| `id` | BigInteger | PK, Auto-increment | Unique identifier |
| `site_name` | String | Required | Human-readable name |
| `domain` | String | Unique, Required, Lowercase | The site's domain (e.g., example.com) |
| `api_key` | String | Unique, Required, UUID | Secure access token |
| `is_active` | Boolean | Default(true) | Status toggle |
| `created_at` | Timestamp | | |
| `updated_at` | Timestamp | | |

## Constraints & Indexes

- **Primary Key**: `id`
- **Unique Index**: `domain`
- **Unique Index**: `api_key`
- **Index**: `is_active` (useful for filtering active sites)

## Eloquent Model

```php
class Site extends Model
{
    protected $fillable = [
        'site_name',
        'domain',
        'is_active',
        // api_key is not fillable as it is auto-generated
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
```
