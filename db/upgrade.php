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
 * Upgrade script for local_su_statboard_api.
 *
 * @package    local_su_statboard_api
 * @copyright  2025 Sorbonne Université
 * @copyright  2025 Victor Da Silva Caseiro <victor.da_silva_caseiro@sorbonne-universite.fr>
 * @copyright  2025 Thomas Naudin <thomas.naudin@sorbonne-universite.fr>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Run plugin upgrade steps.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool
 */
function xmldb_local_su_statboard_api_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // Version 1.0.3 — Rename custom tables to follow the Frankenstyle convention
    // (plugintype_pluginname_tablename), per Moodle plugin contribution guidelines.
    if ($oldversion < 2026061701) {
        $renames = [
            'su_statboard_daily_stats'  => 'local_su_statboard_api_day',
            'su_statboard_hourly_stats' => 'local_su_statboard_api_hour',
        ];

        foreach ($renames as $oldname => $newname) {
            $oldtable = new xmldb_table($oldname);
            $newtable = new xmldb_table($newname);

            // Only rename when the old table exists AND the new one does not yet.
            // This guards against partial upgrades, manual interventions or future re-runs.
            if ($dbman->table_exists($oldtable) && !$dbman->table_exists($newtable)) {
                $dbman->rename_table($oldtable, $newname);
            }
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2026061701, 'local', 'su_statboard_api');
    }

    return true;
}
