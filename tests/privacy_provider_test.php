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
 * Unit tests for the Privacy API implementation.
 *
 * @package    local_su_statboard_api
 * @copyright  2025 Sorbonne Université
 * @copyright  2025 Victor Da Silva Caseiro <victor.da_silva_caseiro@sorbonne-universite.fr>
 * @copyright  2025 Thomas Naudin <thomas.naudin@sorbonne-universite.fr>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_su_statboard_api;

use advanced_testcase;
use core_privacy\local\metadata\collection;
use local_su_statboard_api\privacy\provider;

/**
 * Tests for {@see provider}.
 *
 * @covers \local_su_statboard_api\privacy\provider
 */
final class privacy_provider_test extends advanced_testcase {

    /**
     * `get_metadata()` should declare the `external_tokens` table, the `moodle_webservice`
     * external link, and the `core_logging` subsystem.
     */
    public function test_get_metadata_declares_expected_items(): void {
        $this->resetAfterTest(true);

        $collection = new collection('local_su_statboard_api');
        $collection = provider::get_metadata($collection);

        $items = $collection->get_collection();
        $this->assertNotEmpty($items);

        $names = array_map(static function ($item): string {
            return $item->get_name();
        }, $items);

        $this->assertContains('external_tokens', $names);
        $this->assertContains('moodle_webservice', $names);
        $this->assertContains('core_logging', $names);
    }

    /**
     * `get_contexts_for_userid()` should return the system context for a user that owns a token,
     * and an empty list for an unrelated user.
     */
    public function test_get_contexts_for_userid_returns_system_context_for_token_owner(): void {
        global $DB;

        $this->resetAfterTest(true);

        $tokenowner = $this->getDataGenerator()->create_user();
        $other = $this->getDataGenerator()->create_user();

        // Insert a fake service and a token for $tokenowner.
        $serviceid = $DB->insert_record('external_services', (object)[
            'name'             => 'Test Statboard Service',
            'shortname'        => 'local_su_statboard_api',
            'enabled'          => 1,
            'restrictedusers'  => 0,
            'downloadfiles'    => 0,
            'uploadfiles'      => 0,
            'timecreated'      => time(),
        ]);
        $DB->insert_record('external_tokens', (object)[
            'token'             => sha1('test-token'),
            'tokentype'         => 0,
            'userid'            => $tokenowner->id,
            'externalserviceid' => $serviceid,
            'contextid'         => \context_system::instance()->id,
            'creatorid'         => $tokenowner->id,
            'iprestriction'     => '',
            'validuntil'        => 0,
            'timecreated'       => time(),
            'lastaccess'        => 0,
        ]);

        // Token owner: should get the system context.
        $contextlist = provider::get_contexts_for_userid($tokenowner->id);
        $this->assertGreaterThanOrEqual(1, count($contextlist->get_contextids()));

        // Other user: empty list.
        $emptylist = provider::get_contexts_for_userid($other->id);
        $this->assertCount(0, $emptylist->get_contextids());
    }
}
