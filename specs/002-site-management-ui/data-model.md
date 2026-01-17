# Data Model: Site Management

## Entities

### Site
Represents a WordPress site registered in the system.

| Field | Type | Description |
| :--- | :--- | :--- |
| `id` | BigInt (PK) | Unique identifier |
| `site_name` | String | User-friendly name of the site |
| `domain` | String | Bare domain (e.g., example.com), unique |
| `api_key` | UUID | Secret key for API authentication, unique |
| `is_active` | Boolean | Whether the site is currently accepting leads |
| `created_at` | Timestamp | Creation time |
| `updated_at` | Timestamp | Last update time |

## Relationships
- **Leads**: A `Site` has many `Leads` (FK: `site_id` in `leads` table).

## Validation Rules

### StoreSiteRequest
- `site_name`: required, string, max:255
- `domain`: required, string, max:255, regex (domain format), unique:sites,domain

### UpdateSiteRequest
- `site_name`: sometimes, string, max:255
- `domain`: sometimes, string, max:255, regex, unique:sites,domain (excluding current)
- `is_active`: sometimes, boolean

## State Transitions
- **Status**: Active ↔ Inactive (Toggled via Edit form)
