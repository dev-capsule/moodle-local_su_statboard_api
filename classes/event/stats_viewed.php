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
 * Event class for stats viewed
 *
 * @package    local_su_statboard_api
 * @copyright  2025 Sorbonne Université
 * @copyright  2025 Victor Da Silva Caseiro <victor.da_silva_caseiro@sorbonne-universite.fr>
 * @copyright  2025 Thomas Naudin <thomas.naudin@sorbonne-universite.fr>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_su_statboard_api\event;

/**
 * Event triggered when statistics are viewed.
 */
class stats_viewed extends \core\event\base {
    /**
     * Initialize event properties.
     */
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        // No objecttable: this event signals "stats viewed" but doesn't reference
        // a specific record in any table. Setting objecttable would require objectid
        // on every trigger() call, which has no semantic meaning here.
    }

    /**
     * Get the event name.
     * @return string
     */
    public static function get_name() {
        return get_string('eventstatsviewed', 'local_su_statboard_api');
    }

    /**
     * Get the event description.
     * @return string
     */
    public function get_description() {
        return sprintf("The user with id '%s' viewed usage statistics.", $this->userid);
    }

    /**
     * Get the event URL.
     * @return \moodle_url
     */
    public function get_url() {
        return new \moodle_url('/local/su_statboard_api/view_token.php');
    }
}
