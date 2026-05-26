<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Scheduled task to aggregate hourly connection snapshots.
 *
 * Runs every hour at HH:01 and:
 *   1. Calculates the snapshot for the previous hour (H-1).
 *      Snapshot = distinct users active in the 5 minutes before H:00:00.
 *      e.g. at 11:01, calculates users active between 09:55 and 10:00.
 *   2. Stores the result in {su_statboard_hourly_stats}.
 *   3. Purges entries older than 30 days (max 720 rows = 24h x 30 days).
 *
 * Compatible: MySQL, MariaDB, PostgreSQL (timestamps as named parameters).
 *
 * @package    local_su_statboard_api
 * @copyright  2025 Sorbonne Université
 * @copyright  2025 Victor Da Silva Caseiro <victor.da_silva_caseiro@sorbonne-universite.fr>
 * @copyright  2025 Thomas Naudin <thomas.naudin@sorbonne-universite.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_su_statboard_api\task;

/**
 * Scheduled task class for aggregating hourly connection snapshots.
 */
class aggregate_hourly_stats extends \core\task\scheduled_task {

    /**
     * Returns the name of this task shown in the admin UI.
     * @return string
     */
    public function get_name() {
        return get_string('task_aggregate_hourly_stats', 'local_su_statboard_api');
    }

    /**
     * Executes the task:
     *   1. Calculates snapshot for H-1 (the hour that just ended).
     *   2. Inserts or updates the record in {su_statboard_hourly_stats}.
     *   3. Purges entries older than 30 days.
     */
    public function execute() {
        global $DB;

        $now = time();
        // Step 1 — Determine the previous hour (H-1).
        // The cron runs at HH:01 — we calculate the hour that just ended.
        // e.g. runs at 11:01 → calculates hour 10 (snapshot 09:55 → 10:00).
        $previoushourmark = mktime(date('G', $now), 0, 0, date('n', $now), date('j', $now), date('Y', $now));
        $since            = $previoushourmark - 300; // H:00:00 minus 5 minutes.
        $until            = $previoushourmark;       // H:00:00.
        $hour             = (int)date('G', $previoushourmark);
        $datestr          = date('Y-m-d', $previoushourmark);

        mtrace("SU Statboard API: Calculating snapshot for $datestr hour $hour (window: " .
            date('H:i', $since) . " -> " . date('H:i', $until) . ")...");
        // Step 2 — Count distinct users in the 5-minute window.
        // Compatible: timestamps as named parameters (no UNIX_TIMESTAMP()).
        $connections = $DB->count_records_sql(
            "SELECT COUNT(DISTINCT userid)
               FROM {logstore_standard_log}
              WHERE timecreated >= :since
                AND timecreated < :until
                AND userid > 1
                AND eventname NOT LIKE '%webservice%'",
            [
                'since' => $since,
                'until' => $until,
            ]
        );

        mtrace("SU Statboard API: Found $connections connections for $datestr hour $hour.");
        // Step 3 — Insert or update the record.
        // UNIQUE index on (statsdate, hour) prevents duplicates.
        $existing = $DB->get_record('su_statboard_hourly_stats', [
            'statsdate' => $datestr,
            'hour'      => $hour,
        ]);

        if ($existing) {
            $existing->connections = $connections;
            $existing->timecreated = $now;
            $DB->update_record('su_statboard_hourly_stats', $existing);
            mtrace("SU Statboard API: Updated existing record for $datestr hour $hour.");
        } else {
            $record              = new \stdClass();
            $record->statsdate   = $datestr;
            $record->hour        = $hour;
            $record->connections = $connections;
            $record->timecreated = $now;
            $DB->insert_record('su_statboard_hourly_stats', $record);
            mtrace("SU Statboard API: Inserted new record for $datestr hour $hour.");
        }
        // Step 4 — Purge entries older than 30 days.
        // 24 hours x 30 days = 720 rows maximum.
        $cutoffdate = date('Y-m-d', strtotime('-30 days', $now));
        $deleted    = $DB->delete_records_select(
            'su_statboard_hourly_stats',
            'statsdate < :cutoff',
            ['cutoff' => $cutoffdate]
        );

        mtrace("SU Statboard API: Purged entries older than $cutoffdate ($deleted row(s) deleted).");
        mtrace("SU Statboard API: Hourly aggregation completed successfully.");
    }
}
