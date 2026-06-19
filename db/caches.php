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
 * Cache definitions for local_su_statboard_api.
 *
 * Declares MUC (Moodle Universal Cache) stores used by the plugin.
 * Each definition targets a specific metric with an appropriate TTL.
 *
 * TTL summary:
 *   - statboard_totals     : 1 hour   (total_users, total_courses — very stable)
 *   - statboard_max        : 15 min   (max_connections today's count)
 *   - statboard_quiz       : 5 min    (quiz_completed_today)
 *
 * hourly_connections is read directly from the {local_su_statboard_api_hour} summary
 * table (≤ 24 rows per call) — no cache layer needed.
 *
 * users_online_now is NOT cached — it must remain real-time.
 *
 * @package    local_su_statboard_api
 * @copyright  2025 Sorbonne Université
 * @copyright  2025 Victor Da Silva Caseiro <victor.da_silva_caseiro@sorbonne-universite.fr>
 * @copyright  2025 Thomas Naudin <thomas.naudin@sorbonne-universite.fr>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$definitions = [

    // Total users and total courses — very stable, cached 1 hour.
    'statboard_totals' => [
        'mode'      => cache_store::MODE_APPLICATION,
        'ttl'       => 3600, // 1 hour
        'simplekeys' => true,
        'simpledata' => true,
    ],

    // Max daily connections over 30 days — cached 15 minutes.
    'statboard_max' => [
        'mode'      => cache_store::MODE_APPLICATION,
        'ttl'       => 900, // 15 minutes
        'simplekeys' => true,
        'simpledata' => true,
    ],

    // Quiz completed today — cached 5 minutes.
    'statboard_quiz' => [
        'mode'       => cache_store::MODE_APPLICATION,
        'ttl'        => 300, // 5 minutes
        'simplekeys' => true,
        'simpledata' => true,
    ],
];
