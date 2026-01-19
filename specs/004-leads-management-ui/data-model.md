# Data Model

## Entities

### Lead
Represents a form submission from a WordPress site.

| Field | Type | Description |
|-------|------|-------------|
| `id` | BigInt | Primary Key |
| `site_id` | BigInt | Foreign Key (links to `sites`) |
| `form_name` | String | Identifier of the source form (nullable) |
| `form_data` | JSON | The submitted form fields (flexible key-value) |
| `status` | Enum | `new`, `contacted`, `converted` (default: `new`) |
| `ip_address` | String | Submitter's IP (nullable) |
| `user_agent` | String | Submitter's Browser UA (nullable) |
| `submitted_at` | DateTime | When the form was originally submitted |
| `created_at` | DateTime | Record creation time |
| `updated_at` | DateTime | Record update time |

**Relationships**:
- `belongsTo(Site::class)`

**Casts**:
- `form_data` => `array` (or `AsArrayObject`)
- `submitted_at` => `datetime`
- `status` => `LeadStatus` (Enum)

### Site (Existing)
Represents a source WordPress site.

**Relationships**:
- `hasMany(Lead::class)`

## Enums

### LeadStatus
```php
enum LeadStatus: string {
    case New = 'new';
    case Contacted = 'contacted';
    case Converted = 'converted';
}
```

## Filters & Search (Query Parameters)
The `LeadController@index` will accept these parameters:

- `search`: String (searches within `form_data`)
- `site_id`: Integer (filters by `site_id`)
- `status`: String (filters by `status` enum value)
- `date_from`: Date (filters `submitted_at` >= date)
- `date_to`: Date (filters `submitted_at` <= date)
