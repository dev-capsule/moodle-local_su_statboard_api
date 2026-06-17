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
 * Unit tests for the daily stats aggregation scheduled task.
 *
 * @package    local_su_statboard_api
 * @copyright  2025 Sorbonne Université
 * @copyright  2025 Victor Da Silva Caseiro <victor.da_silva_caseiro@sorbonne-universite.fr>
 * @copyright  2025 Thomas Naudin <thomas.naudin@sorbonne-universite.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_su_statboard_api;

use advanced_testcase;
use local_su_statboard_api\task\aggregate_daily_stats;

/**
 * Tests for {@see aggregate_daily_stats}.
 *
 * @covers \local_su_statboard_api\task\aggregate_daily_stats
 */
final class task_aggregate_daily_stats_test extends advanced_testcase {

    /**
     * The task should insert one row in {local_su_statboard_api_daily_stats} after running, with the count
     * of distinct users who logged in on J-1.
     */
    public function test_task_inserts_yesterday_logins(): void {
        global $DB;

        $this->resetAfterTest(true);

        // Build two distinct user_loggedin events located in J-1.
        $yesterday = strtotime('yesterday') + 12 * 3600; // J-1 at noon.
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();

        foreach ([$user1->id, $user2->id] as $userid) {
            $event = (object)[
                'eventname'    => '\\core\\event\\user_loggedin',
                'component'    => 'core',
                'action'       => 'loggedin',
                'target'       => 'user',
                'objecttable'  => 'user',
                'objectid'     => $userid,
                'crud'         => 'r',
                'edulevel'     => 0,
                'contextid'    => \context_system::instance()->id,
                'contextlevel' => CONTEXT_SYSTEM,
                'contextinstanceid' => 0,
                'userid'       => $userid,
                'courseid'     => 0,
                'relateduserid' => null,
                'anonymous'    => 0,
                'other'        => 'a:0:{}',
                'timecreated'  => $yesterday,
                'origin'       => 'cli',
                'ip'           => '127.0.0.1',
                'realuserid'   => null,
            ];
            $DB->insert_record('logstore_standard_log', $event);
        }

        // Run the task.
        $task = new aggregate_daily_stats();
        ob_start();
        $task->execute();
        ob_end_clean();

        // Assert that one row was created for J-1 with the expected count.
        $datestr = date('Y-m-d', $yesterday);
        $row = $DB->get_record('local_su_statboard_api_daily_stats', ['statsdate' => $datestr]);

        $this->assertNotEmpty($row, 'A row for yesterday should have been inserted.');
        $this->assertEquals(2, (int)$row->logins);
    }

    /**
     * Running the task twice on the same day should update — not duplicate — the row.
     */
    public function test_task_is_idempotent_for_the_same_day(): void {
        global $DB;

        $this->resetAfterTest(true);

        $task = new aggregate_daily_stats();
        ob_start();
        $task->execute();
        $task->execute();
        ob_end_clean();

        $datestr = date('Y-m-d', strtotime('yesterday'));
        $rows = $DB->get_records('local_su_statboard_api_daily_stats', ['statsdate' => $datestr]);

        $this->assertLessThanOrEqual(1, count($rows),
            'No duplicate rows should be created when the task runs twice for the same date.');
    }

    /**
     * Entries older than 30 days should be purged on each run.
     */
    public function test_task_purges_old_entries(): void {
        global $DB;

        $this->resetAfterTest(true);

        // Seed a row dated 60 days ago.
        $oldrow = (object)[
            'statsdate'   => date('Y-m-d', strtotime('-60 days')),
            'logins'      => 100,
            'timecreated' => time() - 60 * 86400,
        ];
        $DB->insert_record('local_su_statboard_api_daily_stats', $oldrow);

        // Run the task.
        $task = new aggregate_daily_stats();
        ob_start();
        $task->execute();
        ob_end_clean();

        // Assert the old row is gone.
        $remaining = $DB->get_record('local_su_statboard_api_daily_stats', ['statsdate' => $oldrow->statsdate]);
        $this->assertFalse($remaining, 'Entries older than 30 days should be purged.');
    }
}
