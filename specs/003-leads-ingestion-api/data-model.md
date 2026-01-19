# Data Model: Leads Ingestion

## Entity: Lead

Represents a form submission from an external WordPress site.

### Database Schema (`leads` table)

| Column Name | Type | Nullable | Description |
|:---|:---|:---|:---|
| `id` | `BIGINT UNSIGNED` | No | Primary Key, Auto-increment |
| `site_id` | `BIGINT UNSIGNED` | No | Foreign Key to `sites.id`. Cascades on delete (or restrict). |
| `form_name` | `VARCHAR(255)` | Yes | Identifier of the CF7 form (e.g., "Contact Form 1"). |
| `form_data` | `JSON` | No | The raw submission data. (Max 1MB enforced by API). |
| `status` | `VARCHAR(20)` | No | Enum: `new`, `contacted`, `converted`. Default: `new`. |
| `ip_address` | `VARCHAR(45)` | Yes | IP address of the submitter. |
| `user_agent` | `TEXT` | Yes | User agent of the submitter. |
| `submitted_at` | `TIMESTAMP` | Yes | Original submission time (if provided), else `created_at`. |
| `created_at` | `TIMESTAMP` | Yes | Laravel standard timestamp. |
| `updated_at` | `TIMESTAMP` | Yes | Laravel standard timestamp. |

### Eloquent Model (`App\Models\Lead`)

**Relationships:**
- `site()`: `BelongsTo` relationship with `App\Models\Site`.

**Fillable Attributes:**
- `site_id`
- `form_name`
- `form_data`
- `status`
- `ip_address`
- `user_agent`
- `submitted_at`

**Casts:**
- `form_data` => `array`
- `submitted_at` => `datetime`

## Entity: Site (Existing)

Referenced for validation.
- `api_key`: Used for lookup during authentication.