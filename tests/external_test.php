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
 * Unit tests for the Statboard API external function.
 *
 * @package    local_su_statboard_api
 * @copyright  2025 Sorbonne Université
 * @copyright  2025 Victor Da Silva Caseiro <victor.da_silva_caseiro@sorbonne-universite.fr>
 * @copyright  2025 Thomas Naudin <thomas.naudin@sorbonne-universite.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_su_statboard_api;

use advanced_testcase;
use context_system;
use externallib_advanced_testcase;
use local_su_statboard_api_external;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/su_statboard_api/externallib.php');
require_once($CFG->dirroot . '/webservice/tests/helpers.php');

/**
 * Tests for {@see local_su_statboard_api_external::get_statboard_stats()}.
 *
 * @covers \local_su_statboard_api_external
 */
final class external_test extends externallib_advanced_testcase {

    /**
     * The function should return the expected response structure with all fields populated.
     */
    public function test_get_statboard_stats_returns_expected_structure(): void {
        $this->resetAfterTest(true);

        // Create an admin-equivalent user with the required capability and log them in.
        $user = $this->getDataGenerator()->create_user();
        $context = context_system::instance();
        $managerrole = $this->assignUserCapability('local/su_statboard_api:view', $context->id);
        role_assign($managerrole, $user->id, $context->id);
        $this->setUser($user);

        // Create a few courses to ensure total_courses > 0.
        $this->getDataGenerator()->create_course();
        $this->getDataGenerator()->create_course();

        // Call the external function.
        $result = local_su_statboard_api_external::get_statboard_stats(0);

        // External API returns must be cleaned through the validate process.
        $result = \external_api::clean_returnvalue(
            local_su_statboard_api_external::get_statboard_stats_returns(),
            $result
        );

        // Assert structure.
        $this->assertIsArray($result);
        $this->assertArrayHasKey('total_users', $result);
        $this->assertArrayHasKey('total_courses', $result);
        $this->assertArrayHasKey('users_online_now', $result);
        $this->assertArrayHasKey('max_connections', $result);
        $this->assertArrayHasKey('hourly_connections', $result);
        $this->assertArrayHasKey('quiz_completed_today', $result);

        // Assert types.
        $this->assertIsInt($result['total_users']);
        $this->assertIsInt($result['total_courses']);
        $this->assertIsInt($result['users_online_now']);
        $this->assertIsArray($result['max_connections']);
        $this->assertArrayHasKey('count', $result['max_connections']);
        $this->assertArrayHasKey('date', $result['max_connections']);
        $this->assertIsArray($result['hourly_connections']);
        $this->assertIsInt($result['quiz_completed_today']);

        // Sanity values.
        $this->assertGreaterThanOrEqual(2, $result['total_courses']);
        $this->assertGreaterThanOrEqual(0, $result['users_online_now']);
    }

    /**
     * The function should refuse calls from a user without the view capability.
     */
    public function test_get_statboard_stats_requires_view_capability(): void {
        $this->resetAfterTest(true);

        // Create a regular user without any custom capability.
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->expectException(\required_capability_exception::class);
        local_su_statboard_api_external::get_statboard_stats(0);
    }

    /**
     * Hourly connections should contain at most 24 entries and follow the HH:00 format.
     */
    public function test_hourly_connections_format(): void {
        $this->resetAfterTest(true);

        $user = $this->getDataGenerator()->create_user();
        $context = context_system::instance();
        $managerrole = $this->assignUserCapability('local/su_statboard_api:view', $context->id);
        role_assign($managerrole, $user->id, $context->id);
        $this->setUser($user);

        $result = local_su_statboard_api_external::get_statboard_stats(0);
        $result = \external_api::clean_returnvalue(
            local_su_statboard_api_external::get_statboard_stats_returns(),
            $result
        );

        $this->assertLessThanOrEqual(24, count($result['hourly_connections']));
        foreach ($result['hourly_connections'] as $bucket) {
            $this->assertMatchesRegularExpression('/^[0-2][0-9]:00$/', $bucket['hour']);
            $this->assertGreaterThanOrEqual(0, $bucket['count']);
        }
    }
}
