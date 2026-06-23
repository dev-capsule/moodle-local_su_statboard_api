<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Scheduled task to aggregate daily login statistics.
 *
 * Runs every night at 00:05 and:
 *   1. Calculates the number of distinct logins for the previous day (J-1)
 *      from {logstore_standard_log} and stores the result in
 *      {local_su_statboard_api_day}.
 *   2. Purges entries older than 30 days from {local_su_statboard_api_day}.
 *
 * This allows the Statboard API to serve max_connections instantly by reading
 * the small summary table instead of scanning 100M+ rows in logstore.
 *
 * @package    local_su_statboard_api
 * @copyright  2025 Sorbonne Université
 * @copyright  2025 Victor Da Silva Caseiro <victor.da_silva_caseiro@sorbonne-universite.fr>
 * @copyright  2025 Thomas Naudin <thomas.naudin@sorbonne-universite.fr>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_su_statboard_api\task;

/**
 * Scheduled task class for aggregating daily login statistics.
 */
class aggregate_daily_stats extends \core\task\scheduled_task {
    /**
     * Returns the name of this task shown in the admin UI.
     * @return string
     */
    public function get_name() {
        return get_string('task_aggregate_daily_stats', 'local_su_statboard_api');
    }

    /**
     * Executes the task:
     *   1. Inserts or updates the login count for yesterday (J-1).
     *   2. Purges entries older than 30 days.
     */
    public function execute() {
        global $DB;

        $now      = time();
        $yesterday = strtotime('yesterday', $now);

        // J-1 boundaries.
        $startofyesterday = strtotime('today', $yesterday);           // 00:00:00 J-1
        $endofyesterday   = strtotime('today', $now) - 1;            // 23:59:59 J-1
        $datestr          = date('Y-m-d', $yesterday);

        mtrace("SU Statboard API: Aggregating stats for $datestr...");
        // Step 1 — Calculate logins for J-1.
        // Uses the exact eventname to avoid LIKE scans on 100M+ rows.
        // Compatible: timestamps passed as named parameters (no UNIX_TIMESTAMP).
        $logins = $DB->count_records_sql(
            "SELECT COUNT(DISTINCT userid)
               FROM {logstore_standard_log}
              WHERE timecreated >= :startofday
                AND timecreated <= :endofday
                AND userid > 1
                AND eventname = :eventname",
            [
                'startofday' => $startofyesterday,
                'endofday'   => $endofyesterday,
                'eventname'  => '\\core\\event\\user_loggedin',
            ]
        );

        mtrace("SU Statboard API: Found $logins logins for $datestr.");
        // Step 2 — Insert or update the record for J-1.
        // Uses UNIQUE index on statsdate to avoid duplicates.
        $existing = $DB->get_record('local_su_statboard_api_day', ['statsdate' => $datestr]);

        if ($existing) {
            $existing->logins      = $logins;
            $existing->timecreated = $now;
            $DB->update_record('local_su_statboard_api_day', $existing);
            mtrace("SU Statboard API: Updated existing record for $datestr.");
        } else {
            $record              = new \stdClass();
            $record->statsdate   = $datestr;
            $record->logins      = $logins;
            $record->timecreated = $now;
            $DB->insert_record('local_su_statboard_api_day', $record);
            mtrace("SU Statboard API: Inserted new record for $datestr.");
        }
        // Step 3 — Purge entries older than 30 days.
        // Keeps the summary table lean (max 30 rows).
        $cutoffdate = date('Y-m-d', strtotime('-30 days', $now));
        $deleted    = $DB->delete_records_select(
            'local_su_statboard_api_day',
            'statsdate < :cutoff',
            ['cutoff' => $cutoffdate]
        );

        mtrace("SU Statboard API: Purged entries older than $cutoffdate ($deleted row(s) deleted).");
        mtrace("SU Statboard API: Daily aggregation completed successfully.");
    }
}
