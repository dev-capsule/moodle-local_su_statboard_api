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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 * Scheduled tasks for local_su_statboard_api.
 *
 * @package    local_su_statboard_api
 * @copyright  2025 Sorbonne Université
 * @copyright  2025 Victor Da Silva Caseiro <victor.da_silva_caseiro@sorbonne-universite.fr>
 * @copyright  2025 Thomas Naudin <thomas.naudin@sorbonne-universite.fr>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$tasks = [
    // Runs every night at 00:05 — aggregates J-1 logins into local_su_statboard_api_day.
    // blocking=1 prevents simultaneous execution across the 4-server cluster.
    [
        'classname' => '\local_su_statboard_api\task\aggregate_daily_stats',
        'blocking'  => 1,
        'minute'    => '5',
        'hour'      => '0',
        'day'       => '*',
        'month'     => '*',
        'dayofweek' => '*',
        'disabled'  => 0,
    ],
    // Runs every hour at HH:01 — calculates the snapshot for the previous hour into local_su_statboard_api_hour.
    // blocking=1 prevents simultaneous execution across the 4-server cluster.
    [
        'classname' => '\local_su_statboard_api\task\aggregate_hourly_stats',
        'blocking'  => 1,
        'minute'    => '1',
        'hour'      => '*',
        'day'       => '*',
        'month'     => '*',
        'dayofweek' => '*',
        'disabled'  => 0,
    ],
];
