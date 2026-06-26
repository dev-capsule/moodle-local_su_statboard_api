# Statboard API — Technical documentation

Technical documentation of the Moodle plugin **`local_su_statboard_api`** to expose platform usage statistics through a secure, optimised REST API designed to feed an external dashboard.

## Table of contents

1. [Overview](#overview)
2. [Compatibility and requirements](#compatibility-and-requirements)
3. [Plugin file tree](#plugin-file-tree)
4. [Database schema](#database-schema)
5. [REST API](#rest-api)
6. [MUC cache strategy](#muc-cache-strategy)
7. [Scheduled tasks](#scheduled-tasks)
8. [Token management](#token-management)
9. [Installation](#installation)
10. [Uninstallation](#uninstallation)
11. [Security and permissions](#security-and-permissions)
12. [GDPR compliance](#gdpr-compliance)
13. [Internationalisation](#internationalisation)
14. [Development guide](#development-guide)
15. [Maintenance and troubleshooting](#maintenance-and-troubleshooting)

## Overview

The plugin **`local_su_statboard_api`** (release `v1.0.0`, version `2025021406`) is a Moodle local plugin that exposes, in a single REST call, a set of usage statistics designed to feed an external dashboard.

The architecture combines three mechanisms to serve metrics with minimal latency, even on a very high-volume platform (logstore of over 100 million rows, 4-server cluster):

- **MUC cache** (Moodle Universal Cache) with TTLs differentiated by metric volatility.
- **Summary tables** populated by cron, avoiding direct queries against `logstore_standard_log` on every call.
- **Portable SQL queries** written with named parameters (no `UNIX_TIMESTAMP()` or vendor-specific function) so they work on PostgreSQL, MySQL and MariaDB alike.

On a cold call (cache completely empty), at most **4 queries** are executed against the database, down from 55+ in the first version of the plugin.

### License

GNU GPL v3 or later.

## Compatibility and requirements

### Moodle

- Minimum version: Moodle 4.2 (`2023042400`). Tested against Moodle 4.2, 4.3, 4.4, 4.5 LTS, 5.0, 5.1 and 5.2 (current stable).
- Compatibility tested up to Moodle 4.5+.

### Databases

| RDBMS | Minimum version | Status |
|-------|-----------------|--------|
| PostgreSQL | 12.0+ | Supported |
| MySQL | 5.7.33+ | Supported |
| MariaDB | 10.6+ | Supported |
| Microsoft SQL Server | — | **Not supported** |
| Oracle | — | **Not supported** |
| SQLite | — | **Not supported** |

The installer (`db/install.php`) automatically checks the RDBMS type and version at startup. If the database is unsupported or too old, installation aborts with an explicit error message.

## Plugin file tree

```
local/su_statboard_api/
├── amd/
│   ├── src/token_manager.js          # AMD source (JS for the token page)
│   └── build/token_manager.min.js    # Minified version
├── classes/
│   ├── event/
│   │   └── stats_viewed.php          # Event triggered on every API call
│   ├── privacy/
│   │   └── provider.php              # GDPR compliance (full Privacy API)
│   └── task/
│       ├── aggregate_daily_stats.php   # Daily cron (00:05)
│       └── aggregate_hourly_stats.php  # Hourly cron (HH:01)
├── db/
│   ├── access.php                    # Capabilities (view + managetokensettings)
│   ├── admin.php                     # Administration pages
│   ├── caches.php                    # MUC definitions (statboard_*)
│   ├── events.php                    # Observer config_log_created
│   ├── install.php                   # Automated installation
│   ├── install.xml                   # Custom tables schema
│   ├── services.php                  # Web service definition
│   ├── tasks.php                     # Scheduled tasks declaration
│   └── uninstall.php                 # Clean and complete uninstallation
├── lang/
│   ├── de/local_su_statboard_api.php
│   ├── en/local_su_statboard_api.php
│   ├── es/local_su_statboard_api.php
│   ├── fr/local_su_statboard_api.php
│   ├── it/local_su_statboard_api.php
│   ├── pt/local_su_statboard_api.php
│   └── pt_br/local_su_statboard_api.php
├── pix/
│   └── icon.png                      # Plugin icon
├── style/
│   └── styles.css                    # Admin pages styles
├── templates/
│   ├── token_settings.mustache       # Token management UI
│   └── view_token.mustache           # Token consultation UI
├── externallib.php                   # Implementation of get_statboard_stats()
├── locallib.php                      # Token helpers + portable SQL
├── settings.php                      # Plugin configuration page
├── token_settings.php                # Token management controller
├── version.php                       # Plugin metadata
└── view_token.php                    # Token consultation controller
```

## Database schema

### Custom tables created by the plugin

#### `local_su_statboard_api_day`

Daily aggregation of logins. Populated every night at 00:05 by the `aggregate_daily_stats` task. Contains at most 30 rows (rolling retention).

| Field | Type | Description |
|-------|------|-------------|
| `id` | int(10), AUTO | Primary key |
| `statsdate` | char(10) | Date in `YYYY-MM-DD` format (unique index) |
| `logins` | int(10) | Number of distinct users who triggered a `\core\event\user_loggedin` event that day |
| `timecreated` | int(10) | Timestamp when the row was created by the cron |

Unique index on `statsdate` to prevent duplicates.

#### `local_su_statboard_api_hour`

Hourly snapshots of active users. Populated every hour at HH:01 by the `aggregate_hourly_stats` task. Contains at most 720 rows (24 hours × 30 days).

| Field | Type | Description |
|-------|------|-------------|
| `id` | int(10), AUTO | Primary key |
| `statsdate` | char(10) | Date in `YYYY-MM-DD` format |
| `hour` | int(2) | Hour (0–23) |
| `connections` | int(10) | Number of distinct users active in the window `[H-5min, H]` |
| `timecreated` | int(10) | Timestamp when the row was created by the cron |

Composite unique index on `(statsdate, hour)`.

### Moodle tables used read-only

- `user`, `course`: total counts and online users.
- `logstore_standard_log`: aggregation source for crons (used only by the scheduled tasks and for the current day in `max_connections`).
- `quiz_attempts`: number of quizzes completed today.

### Moodle tables used read/write

- `external_services`, `external_services_functions`, `external_services_users`, `external_tokens`: web service declaration and authorisation.
- `config_plugins`: plugin configuration (token, expiration).
- `user`, `role_assignments`: dedicated webservice user and manager role.

### Plugin configuration

| Key (`config_plugins`) | Description | Default value |
|------------------------|-------------|---------------|
| `webservice_token` | Current API token | Generated on install |
| `token_validity_period` | Validity period (days) | `365` |
| `token_no_expiration` | Permanent token (1) or not (0) | `'1'` |

## REST API

### Web service

A single web service is created on install:

- **Name**: `SU Statboard API Service`
- **Shortname**: `local_su_statboard_api`
- **Authentication**: bearer token
- **Required capability**: `local/su_statboard_api:view`

### Exposed function

`local_su_statboard_api_get_statboard_stats`

Implementation: `local_su_statboard_api_external::get_statboard_stats()` in `externallib.php`.

#### Parameters

| Name | Type | Required | Description |
|------|------|----------|-------------|
| `date` | int (Unix timestamp) | No (default `0`) | Day to analyse for daily metrics. `0` = today. |

#### Response

```json
{
    "total_users": 1250,
    "total_courses": 89,
    "users_online_now": 42,
    "max_connections": {
        "count": 456,
        "date": "2025-01-15"
    },
    "hourly_connections": [
        { "hour": "00:00", "count": 5 },
        { "hour": "01:00", "count": 2 },
        { "hour": "08:00", "count": 45 }
    ],
    "quiz_completed_today": 312
}
```

#### Metric details

**`total_users`** — Number of active users (not deleted, not suspended). Read from `{user}`.

**`total_courses`** — Number of courses excluding the front page (`id > 1`). Read from `{course}`.

**`users_online_now`** — Real users active in the last 5 minutes (based on `lastaccess`). Excludes `webservice` and `nologin` technical accounts. **Always real-time, never cached.**

**`max_connections`** — Active-user peak over the last 30 days, with the peak date. Combines the `local_su_statboard_api_day` summary table (J-1 to J-30, fed by the daily cron) and a live count on `{user}.lastaccess` for the current day (avoids any runtime query on `logstore_standard_log`, per Moodle review recommendation).

**`hourly_connections`** — Array of the number of distinct active users per hour of the requested day. For today: hours from 00 to the current hour. For a past day: 24 full hours. Read directly from `local_su_statboard_api_hour`.

**`quiz_completed_today`** — Number of quiz attempts in `finished` state started since 00:00 of the requested day. Read from `{quiz_attempts}`.

### Audit

Every call to the API triggers the Moodle event `\local_su_statboard_api\event\stats_viewed`, available through the standard logs report.

## MUC cache strategy

Four MUC stores are declared in `db/caches.php`, all in `MODE_APPLICATION` mode (shared across cluster servers):

| Store | TTL | Concerned metrics | Justification |
|-------|-----|-------------------|---------------|
| `statboard_totals` | 1 h | `total_users`, `total_courses` | Very stable data |
| `statboard_max` | 15 min | Today's connection count for `max_connections` | Evolves slowly during the day |
| `statboard_quiz` | 5 min | `quiz_completed_today` | Updates regularly |

`users_online_now` is **never cached** — the metric must stay real-time.

`hourly_connections` is **not cached** either: data is read directly from the aggregated `local_su_statboard_api_hour` summary table (≤ 24 rows per call, instant read on a unique index). A cache would bring no measurable benefit and would introduce a lag with the hourly cron that refreshes the table at `HH:01`.

The cache keys for `max_connections` and `quiz_completed_today` include the current day (`max_today_YYYYMMDD`, `quiz_completed_YYYYMMDD`), ensuring automatic reset at midnight.

## Scheduled tasks

Declared in `db/tasks.php`, both with `blocking = 1` to prevent concurrent execution across the cluster.

### `aggregate_daily_stats` — daily, 00:05

`classes/task/aggregate_daily_stats.php`

On each execution:

1. Calculates the number of distinct logins for J-1 by querying `{logstore_standard_log}` with a filter `eventname = '\core\event\user_loggedin'` (using the exact eventname avoids costly `LIKE` scans on tens of millions of rows).
2. Inserts or updates the corresponding row in `local_su_statboard_api_day` (the unique index on `statsdate` prevents duplicates).
3. Purges all entries whose `statsdate` is older than J-30.

### `aggregate_hourly_stats` — hourly, HH:01

`classes/task/aggregate_hourly_stats.php`

On each execution (e.g. at 11:01):

1. Determines the hour that just ended (10 in this example).
2. Counts distinct users active over the window `[H:00 - 5 min, H:00]` (e.g. 09:55 → 10:00) by filtering on `{logstore_standard_log}`, `userid > 1` and excluding `%webservice%` events.
3. Inserts or updates the row in `local_su_statboard_api_hour` (composite unique index on `(statsdate, hour)`).
4. Purges entries older than 30 days.

## Token management

All the logic lives in `locallib.php`. A dedicated admin page (`token_settings.php`) lets the Moodle administrator manage the token without going through the native web services screens.

### Main functions

`local_su_statboard_api_regenerate_token()`
Generates a new token via `external_generate_token()` (permanent token by default), deletes the old one and persists the new one in `config_plugins`. Respects the `token_no_expiration` and `token_validity_period` configuration.

`local_su_statboard_api_update_expiration_date($timestamp)`
Updates the expiration date of all service tokens and synchronises the `token_validity_period` value (in days) in the configuration.

`local_su_statboard_api_set_token_no_expiration($serviceid, $token = null)`
Switches a token (or all tokens of a service) to permanent mode by setting `validuntil = NULL` (PostgreSQL-compatible).

`local_su_statboard_api_update_token($newtoken)`
Replaces the value of an existing token without regenerating a new one (used for manual fixes).

### Configuration ↔ database consistency

The `token_settings.php` controller automatically detects and corrects inconsistencies between the plugin configuration (`token_no_expiration`) and the real token state (`external_tokens.validuntil`). If the two diverge, the real token state in the database wins: the plugin configuration is silently realigned.

### Portable SQL helpers

`local_su_statboard_api_get_db_compatible_sql($operation, $column)` — utility for the rare cases where a vendor-specific SQL function is needed (`date_format`, `hour_extract`, `timestamp_to_date`). Automatically switches between PostgreSQL syntax (`to_char`, `to_timestamp`, `EXTRACT`) and MySQL/MariaDB syntax (`FROM_UNIXTIME`, `HOUR`).

## Installation

`db/install.php` orchestrates an automated and idempotent installation:

1. Checks the RDBMS compatibility (type + minimum version).
2. Cleans up any previous installation (tokens, functions and users linked to the `local_su_statboard_api` service).
3. Creates (or reuses) a webservice user `webservice_statboard_<timestamp>` with a random password.
4. Assigns the `manager` role at the system level.
5. Creates the web service `SU Statboard API Service` (`shortname = local_su_statboard_api`).
6. Links the `local_su_statboard_api_get_statboard_stats` function to the service.
7. Authorises the webservice user for this service.
8. Configuration: `token_validity_period = 365`, `token_no_expiration = '1'`.
9. Generates a permanent token via `external_generate_token(EXTERNAL_TOKEN_PERMANENT, ...)` and persists it in `config_plugins`.

The installer `mtrace`s every step to ease CLI diagnostics.

The administrator must then **manually enable web services and the REST protocol** via `Site administration > Plugins > Web services`.

## Uninstallation

`db/uninstall.php` performs a complete cleanup:

1. Retrieves the token and services whose `shortname` matches or whose `name` matches `%SU Statboard API%`.
2. Deletes all linked tokens, functions and authorisations.
3. Deletes the services themselves.
4. Deletes orphan tokens by value (safety).
5. Marks `deleted = 1` for `webservice_statboard_*` users.
6. Wipes all plugin configuration via `unset_all_config_for_plugin('local_su_statboard_api')`.

The custom tables `local_su_statboard_api_day` and `local_su_statboard_api_hour` are automatically removed by Moodle (XMLDB declaration).

## Security and permissions

### Capabilities

Declared in `db/access.php`.

`local/su_statboard_api:view` — Read statistics through the API.
- `riskbitmask`: `RISK_PERSONAL`
- `captype`: `read`
- `contextlevel`: `CONTEXT_SYSTEM`
- Archetype: `manager` (allowed by default)

`local/su_statboard_api:managetokensettings` — Manage the token (regeneration, expiration).
- `riskbitmask`: `RISK_CONFIG | RISK_PERSONAL`
- `captype`: `write`
- `contextlevel`: `CONTEXT_SYSTEM`
- Archetype: `manager` (allowed by default)

### API authentication

All calls to `get_statboard_stats` require:

- A valid token via the `wstoken` parameter.
- The `local/su_statboard_api:view` capability at the system level for the user linked to the token.

### Administration pages

`db/admin.php` registers two entries in the administration menu, both protected by `local/su_statboard_api:managetokensettings`:

- An entry in `localplugins` (main plugin page).
- An entry in `webservicesettings` for direct access from the web services admin.

## GDPR compliance

`classes/privacy/provider.php` fully implements Moodle's Privacy API.

### Declared metadata

- Table `external_tokens` (fields `token`, `userid`, `validuntil`).
- External link `moodle_webservice` (transmission via WS).
- Subsystem `core_logging` (event `stats_viewed`).

### Implemented methods

- `get_metadata()`: declaration of stored and transmitted personal data.
- `get_contexts_for_userid($userid)`: returns the system context if the user has a token.
- `get_users_in_context(userlist)`: lists users with a token for this service.
- `export_user_data(approved_contextlist)`: exports the user's tokens (value, creation date, expiration).
- `delete_data_for_all_users_in_context($context)`: deletes all service tokens.
- `delete_data_for_user(approved_contextlist)`: deletes a specific user's tokens.
- `delete_data_for_users(approved_userlist)`: bulk deletion for multiple users.

### Data outside the Privacy API scope

The aggregated tables created by the plugin (`local_su_statboard_api_day`, `local_su_statboard_api_hour`) are **not** declared in `provider.php` because they contain only anonymous counters (connection counts, distinct user counts). No `userid` or other identifier is stored, in line with the data minimisation principle.

API calls do, however, generate a `stats_viewed` event in Moodle's standard logstore, attributed to the user behind the token. These logs follow Moodle's standard retention policy and are covered by the `core_logging` declaration above.

## Internationalisation

The plugin ships with **7 languages**:

| Code | Language |
|------|----------|
| `en` | English (reference language) |
| `fr` | French |
| `de` | German |
| `es` | Spanish |
| `it` | Italian |
| `pt` | Portuguese (Portugal) |
| `pt_br` | Portuguese (Brazil) |

Each file `lang/<code>/local_su_statboard_api.php` covers all the strings: plugin name, capabilities, token management, expiration dates, MUC cache descriptions, scheduled task labels, error messages, and Privacy API strings.

## Development guide

### Adding a metric to `get_statboard_stats`

1. Add the query (ideally with MUC cache) in `externallib.php::get_statboard_stats()`.
2. If the metric deserves a new store, declare a definition in `db/caches.php` with an appropriate TTL and add the `cachedef_<name>` string in all language files.
3. Complete `get_statboard_stats_returns()` with the return structure.
4. If the query needs to run against large volumes, consider a summary table populated by cron, following the `local_su_statboard_api_day` model.
5. Test on PostgreSQL **and** on MySQL/MariaDB.

### Adding a new function to the service

```php
// classes or externallib.php
public static function get_course_stats_parameters() {
    return new external_function_parameters([
        'courseid' => new external_value(PARAM_INT, 'Course ID'),
    ]);
}

public static function get_course_stats($courseid) {
    // implementation
}

public static function get_course_stats_returns() {
    // return structure
}
```

```php
// db/services.php
'local_su_statboard_api_get_course_stats' => [
    'classname'    => 'local_su_statboard_api_external',
    'methodname'   => 'get_course_stats',
    'description'  => 'Get course-specific statistics',
    'type'         => 'read',
    'ajax'         => true,
    'capabilities' => 'local/su_statboard_api:view',
],
```

Don't forget to bump `$plugin->version` in `version.php` so Moodle re-reads `services.php`.

### Manual call tests

```bash
curl -X POST "https://moodle.example.com/webservice/rest/server.php" \
  -d "wstoken=YOUR_TOKEN" \
  -d "wsfunction=local_su_statboard_api_get_statboard_stats" \
  -d "moodlewsrestformat=json" \
  -d "date=0"
```

### Manual cron triggering

```bash
php admin/cli/scheduled_task.php --execute='\local_su_statboard_api\task\aggregate_daily_stats'
php admin/cli/scheduled_task.php --execute='\local_su_statboard_api\task\aggregate_hourly_stats'
```

## Maintenance and troubleshooting

### Health check

The configuration page `Site administration > Plugins > Local plugins > Statboard API` displays in real time:

- The token state (valid / expired / permanent).
- Any detected inconsistency between configuration and database.
- A shortcut to the Moodle scheduled tasks list.

### Common error codes

| Code | Likely cause | Action |
|------|--------------|--------|
| `invalidtoken` | Token expired, deleted, or mistyped | Regenerate the token via the management page |
| `nopermissions` | Token's user without `local/su_statboard_api:view` | Check the manager role assignment |
| `servicenotavailable` | Global web services disabled or REST protocol disabled | Enable in `Web services > Overview` |
| `dml_*_exception` in cron | Transient DB issue or unique index violation | Check Moodle logs, re-run the cron |

### Checking summary tables consistency

```sql
-- Expected rows: <= 30
SELECT COUNT(*)