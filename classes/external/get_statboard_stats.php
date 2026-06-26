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
 * External function: get_statboard_stats.
 *
 * Returns a complete set of platform usage statistics in a single call.
 *
 * Cache strategy:
 *   - total_users / total_courses : 1 hour   (very stable)
 *   - quiz_completed_today        : 5 min    (changes during the day)
 *   - max_connections             : 15 min   (changes rarely during a day)
 *   - users_online_now            : no cache (real-time)
 *
 * Query breakdown (cache miss worst case):
 *   - users_online_now     : 1 query on {user} (always executed, no cache)
 *   - total_users/courses  : 2 queries on {user} and {course} (cached 1h)
 *   - max_connections      : 1 COUNT(DISTINCT) on {user}.lastaccess for today (cached 15min, < 50k rows)
 *                            + 1 read on {local_su_statboard_api_day} for J-1 to J-30 (instant)
 *   - hourly_connections   : 1 read on {local_su_statboard_api_hour} for the requested day
 *                            (24 rows max, instant - no cache needed)
 *   - quiz_completed_today : 1 COUNT query on {quiz_attempts} (cached 5min)
 *
 * Compatible: MySQL, MariaDB, PostgreSQL (timestamps passed as parameters).
 *
 * @package    local_su_statboard_api
 * @copyright  2025 Sorbonne Université
 * @copyright  2025 Victor Da Silva Caseiro <victor.da_silva_caseiro@sorbonne-universite.fr>
 * @copyright  2025 Thomas Naudin <thomas.naudin@sorbonne-universite.fr>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_su_statboard_api\external;

use cache;
use context_system;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;

/**
 * External service implementation for the Statboard stats endpoint.
 */
class get_statboard_stats extends external_api {
    /**
     * Define parameters for the external function.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters(
            [
                'date' => new external_value(PARAM_INT, 'Timestamp of the day (0 for today)', VALUE_DEFAULT, 0),
            ]
        );
    }

    /**
     * Return the dashboard statistics with MUC cache for non-real-time metrics.
     *
     * @param int $date Timestamp of the day to analyze (0 for today).
     * @return array
     */
    public static function execute(int $date = 0): array {
        global $DB, $CFG;

        // Parameter validation.
        $params = self::validate_parameters(
            self::execute_parameters(),
            ['date' => $date]
        );

        /*
         * Context and capability checks (Moodle external service security guidelines).
         * 1. validate_context() ensures the user has permission to access the given context
         *    and sets it as the current page context (required for capability checks below).
         * 2. require_capability() enforces the plugin-specific 'view' capability.
         */
        $context = context_system::instance();
        self::validate_context($context);
        require_capability('local/su_statboard_api:view', $context);

        // If date is not provided, use today.
        if (empty($params['date'])) {
            $today = time();
            $startofday = strtotime('today', $today);
        } else {
            $startofday = strtotime('today', $params['date']);
        }
        $currenttime = time();

        // Prepare response.
        $result = [];
        // Cache instances - defined once here and reused below for each metric group.
        $cachetotals = cache::make('local_su_statboard_api', 'statboard_totals');
        $cachemax    = cache::make('local_su_statboard_api', 'statboard_max');
        $cachequiz   = cache::make('local_su_statboard_api', 'statboard_quiz');
        // 1. Total active users - cached 1 hour.
        $totalusers = $cachetotals->get('total_users');
        if ($totalusers === false) {
            $totalusers = $DB->count_records_select('user', 'deleted = 0 AND suspended = 0');
            $cachetotals->set('total_users', $totalusers);
        }
        $result['total_users'] = $totalusers;
        // 2. Total courses - cached 1 hour.
        $totalcourses = $cachetotals->get('total_courses');
        if ($totalcourses === false) {
            $totalcourses = $DB->count_records_select('course', 'id > 1');
            $cachetotals->set('total_courses', $totalcourses);
        }
        $result['total_courses'] = $totalcourses;
        /*
         * 3. Users currently online - NOT cached, always real-time.
         *    Source   : {user}.lastaccess - single table, uses existing index.
         *    Filter   : excludes technical accounts via auth method (webservice, nologin).
         *    Window   : last 5 minutes.
         *    Portable : timestamps passed as named parameters (no UNIX_TIMESTAMP()).
         */
        $now   = time();
        $since = $now - 300; // 5 minutes.

        $result['users_online_now'] = $DB->count_records_sql(
            "SELECT COUNT(DISTINCT u.id)
               FROM {user} u
              WHERE u.lastaccess > :since
                AND u.lastaccess <= :now
                AND u.deleted = 0
                AND u.suspended = 0
                AND u.confirmed = 1
                AND u.id > 1
                AND u.auth NOT IN ('webservice', 'nologin')",
            ['since' => $since, 'now' => $now]
        );
        /*
         * 4. Max daily connections over the last 30 days.
         *    Source (J-1 to J-30) : {local_su_statboard_api_day} summary table - instant read.
         *    Source (today)       : {user}.lastaccess - counts active users today.
         *    Today is cached 15min; summary table is always read fresh (already aggregated).
         *    Note: queries {user} (max 50k rows) instead of {logstore_standard_log}
         *    (potentially 100M+ rows) per the reviewer's recommendation to avoid runtime
         *    scans of the logstore. Semantically, "today's count" now means "distinct
         *    users active today" (rather than "logged in today") - a more accurate proxy
         *    for current usage that includes ongoing sessions.
         */
        $maxcachekey  = 'max_today_' . date('Ymd', $startofday); // Date as digits only - MUC simple keys forbid hyphens.
        $todaylogins  = $cachemax->get($maxcachekey);

        if ($todaylogins === false) {
            $todaylogins = $DB->count_records_sql(
                "SELECT COUNT(DISTINCT u.id)
                   FROM {user} u
                  WHERE u.lastaccess >= :startofday
                    AND u.lastaccess <= :currenttime
                    AND u.deleted = 0
                    AND u.suspended = 0
                    AND u.confirmed = 1
                    AND u.id > 1
                    AND u.auth NOT IN ('webservice', 'nologin')",
                [
                    'startofday'  => $startofday,
                    'currenttime' => $currenttime,
                ]
            );
            $cachemax->set($maxcachekey, $todaylogins);
        }

        // Read past days from the summary table (instant - max 30 rows).
        $pastdays = $DB->get_records('local_su_statboard_api_day', null, 'statsdate DESC', 'statsdate, logins');

        // Find the maximum across past days + today.
        $maxconnections = ['count' => $todaylogins, 'date' => date('Y-m-d', $startofday)];

        foreach ($pastdays as $record) {
            if ((int)$record->logins > $maxconnections['count']) {
                $maxconnections['count'] = (int)$record->logins;
                $maxconnections['date']  = $record->statsdate;
            }
        }

        $result['max_connections'] = $maxconnections;
        /*
         * 5. Hourly connected users for the current day.
         *    Source : {local_su_statboard_api_hour} - pre-calculated by cron every 5min.
         *    Instant read - max 24 rows.
         *    Falls back to 0 for hours not yet calculated by the cron.
         *    For today: show hours 0 to current hour.
         *    For past dates: show all 24 hours (complete day).
         */
        $istoday     = (date('Y-m-d', $startofday) === date('Y-m-d', $currenttime));
        $currenthour = $istoday ? (int)userdate($currenttime, '%H') : 23;

        // Read all hourly snapshots for today from the summary table.
        $hourrows = $DB->get_records(
            'local_su_statboard_api_hour',
            ['statsdate' => date('Y-m-d', $startofday)],
            'hour ASC',
            'hour, connections'
        );

        // Index by hour for easy lookup.
        $hourlylookup = [];
        foreach ($hourrows as $row) {
            $hourlylookup[(int)$row->hour] = (int)$row->connections;
        }

        // Build the response array - initialize all hours to 0 up to current hour.
        $hourlyconnections = [];
        for ($hour = 0; $hour <= $currenthour; $hour++) {
            $hourlyconnections[] = [
                'hour'  => sprintf("%02d:00", $hour),
                'count' => isset($hourlylookup[$hour]) ? $hourlylookup[$hour] : 0,
            ];
        }

        $result['hourly_connections'] = $hourlyconnections;
        /*
         * 6. Quiz completed today - cached 5 minutes.
         *    Source   : {quiz_attempts} - lightweight COUNT on a well-indexed table.
         *    Filter   : state = 'finished', timestart >= start of today.
         *    Portable : startofday passed as named parameter (no UNIX_TIMESTAMP()).
         *    Cache key includes the day so it resets automatically at midnight.
         */
        $quizcachekey       = 'quiz_completed_' . date('Ymd', $startofday); // Date as digits only - MUC simple keys forbid hyphens.
        $quizcompletedtoday = $cachequiz->get($quizcachekey);

        if ($quizcompletedtoday === false) {
            $quizcompletedtoday = $DB->count_records_sql(
                "SELECT COUNT(*)
                   FROM {quiz_attempts}
                  WHERE timestart >= :startofday
                    AND state = 'finished'",
                ['startofday' => $startofday]
            );
            $cachequiz->set($quizcachekey, $quizcompletedtoday);
        }

        $result['quiz_completed_today'] = $quizcompletedtoday;

        // Record the event.
        $event = \local_su_statboard_api\event\stats_viewed::create([
            'context' => $context,
            'other'   => [
                'stats_type' => 'statboard',
                'date'       => $startofday,
            ],
        ]);
        $event->trigger();

        return $result;
    }

    /**
     * Define the return type for the external function.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure(
            [
                'total_users'    => new external_value(PARAM_INT, 'Total number of active users'),
                'total_courses'  => new external_value(PARAM_INT, 'Total number of courses'),
                'users_online_now' => new external_value(
                    PARAM_INT,
                    'Number of real users active in the last 5 minutes (excludes webservice/nologin accounts)'
                ),
                'max_connections' => new external_single_structure(
                    [
                        'count' => new external_value(PARAM_INT, 'Maximum number of daily logins over the last 30 days'),
                        'date'  => new external_value(PARAM_TEXT, 'Date of maximum logins (YYYY-MM-DD format)'),
                    ]
                ),
                'hourly_connections' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'hour'  => new external_value(PARAM_TEXT, 'Hour in HH:00 format (00:00 to 23:00)'),
                            'count' => new external_value(PARAM_INT, 'Number of distinct users active during this hour'),
                        ]
                    ),
                    'Distinct active users per hour for the current day'
                ),
                'quiz_completed_today' => new external_value(
                    PARAM_INT,
                    'Number of quiz attempts finished today'
                ),
            ]
        );
    }
}
