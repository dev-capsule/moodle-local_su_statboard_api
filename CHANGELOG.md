# Changelog

All notable changes to the **`local_su_statboard_api`** plugin are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.3] — 2026-06-17

### Changed (breaking, fixed by automatic upgrade)

- **Tables renamed to follow the Frankenstyle convention** (`plugintype_pluginname_tablename`):
  - `su_statboard_daily_stats` → `local_su_statboard_api_day`
  - `su_statboard_hourly_stats` → `local_su_statboard_api_hour`

  (Short suffixes used because Moodle's `sql_generator` enforces a 28-character table-name limit for Oracle compatibility.)
  - Existing installations are migrated automatically by `db/upgrade.php` — no manual action required.

### Fixed

- **Security (BLOCKER)**: added `self::validate_context()` call in `get_statboard_stats()` (Moodle external service guideline).
- **Security**: replaced two `PARAM_RAW` by `PARAM_TEXT` for submit-button parameters in `token_settings.php`.
- **Best practice**: use `get_config()` instead of direct DML on `{config_plugins}` in the uninstaller.

### Removed

- Dead cache definition `statboard_hourly` that was declared but never used. `hourly_connections` reads directly from the aggregated summary table (≤ 24 rows per call, already instant).

### Added

- GitHub Actions CI workflow (`.github/workflows/ci.yml`) running Moodle Plugin CI across Moodle 4.1 → 4.5, PHP 8.0 → 8.3, PostgreSQL and MariaDB (14 jobs).
- `db/upgrade.php` with the v1.0.3 table-rename migration block.

## [1.0.2] — 2026-04-29

### Changed

- Plugin renamed from `local_su_dashboard_api` to `local_su_statboard_api`.
- Documentation overhaul: `DEVELOPERS_fr.md` and `DEVELOPERS_en.md`, `README.md`, `CHANGELOG.md`, `LICENSE` (GPL v3).
- Full Moodle CodeChecker compliance (0 errors, 0 warnings).
- PHPUnit test suite (external, privacy provider, daily aggregation task).

## [1.0.0] — 2026-04-29

### Added

- Initial public release of the Statboard API plugin.
- Single REST web service `local_su_statboard_api_get_statboard_stats` returning a complete metric set in one call:
  - `total_users` — active users (not deleted, not suspended).
  - `total_courses` — number of courses excluding the front page.
  - `users_online_now` — real users active in the last 5 minutes (excludes `webservice` and `nologin` accounts).
  - `max_connections` — daily login peak over the last 30 days, with date.
  - `hourly_connections` — hourly snapshot of distinct active users.
  - `quiz_completed_today` — finished quiz attempts of the day.
- MUC cache strategy with per-metric TTL (`statboard_totals` 1 h, `statboard_max` 15 min, `statboard_quiz` 5 min); `users_online_now` always live; `hourly_connections` read directly from the summary table.
- Two scheduled tasks (`blocking=1` for cluster safety):
  - `\local_su_statboard_api\task\aggregate_daily_stats` — nightly login aggregation (00:05).
  - `\local_su_statboard_api\task\aggregate_hourly_stats` — hourly snapshot aggregation (HH:01).
- Two custom tables `su_statboard_daily_stats` (≤ 30 rows) and `su_statboard_hourly_stats` (≤ 720 rows) populated by the cron, with automatic 30-day rolling purge.
- Automated installer that creates the dedicated webservice user, attaches the manager role, registers the external service and generates a permanent token.
- Automated uninstaller that performs full cleanup of users, tokens, services and configuration.
- Token management UI (`token_settings.php`) with regeneration, expiration toggle, automatic detection and correction of inconsistencies between configuration and database state.
- Read-only token consultation page (`view_token.php`).
- Two capabilities: `local/su_statboard_api:view` and `local/su_statboard_api:managetokensettings`.
- Full Privacy API implementation covering metadata declaration, user data export and deletion (single user, multiple users, all users in context).
- Custom event `\local_su_statboard_api\event\stats_viewed` fired on every API call for audit purposes.
- Multi-database support: PostgreSQL 12+, MySQL 5.7.33+, MariaDB 10.6+. Installation aborts with a clear error on unsupported databases (MSSQL, Oracle, SQLite).
- Localised in 7 languages: English (reference), French, German, Spanish, Italian, Portuguese (Portugal), Portuguese (Brazil).
- Portable SQL helpers (`local_su_statboard_api_get_db_compatible_sql`) that branch between PostgreSQL and MySQL/MariaDB syntax for date and hour operations.

### Compatibility

- Requires Moodle 4.1 or later (`2022112800+`).

[1.0.3]: https://github.com/dev-capsule/moodle-local_su_statboard_api/releases/tag/v1.0.3
[1.0.2]: https://github.com/dev-capsule/moodle-local_su_statboard_api/releases/tag/v1.0.2
[1.0.0]: https://github.com/dev-capsule/moodle-local_su_statboard_api/releases/tag/v1.0.0