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
 * Admin pages configuration for local_su_statboard_api
 *
 * @package    local_su_statboard_api
 * @copyright  2025 Sorbonne Université
 * @copyright  2025 Victor Da Silva Caseiro <victor.da_silva_caseiro@sorbonne-universite.fr>
 * @copyright  2025 Thomas Naudin <thomas.naudin@sorbonne-universite.fr>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();

// Add a link to the token management page in the admin menu.
$ADMIN->add('localplugins', new admin_externalpage(
    'local_su_statboard_api_token_settings',
    new lang_string('token_settings_title', 'local_su_statboard_api'),
    new moodle_url('/local/su_statboard_api/token_settings.php'),
    'local/su_statboard_api:managetokensettings',
    true,
    context_system::instance(),
    null,
    new pix_icon('icon', get_string('pluginname', 'local_su_statboard_api'), 'local_su_statboard_api')
));

// Add a direct link to access the token management page in web services administration.
$ADMIN->add('webservicesettings', new admin_externalpage(
    'local_su_statboard_api_token_admin',
    new lang_string('su_token_admin', 'local_su_statboard_api'),
    new moodle_url('/local/su_statboard_api/token_settings.php'),
    'local/su_statboard_api:managetokensettings',
    true,
    context_system::instance(),
    null,
    new pix_icon('icon', get_string('pluginname', 'local_su_statboard_api'), 'local_su_statboard_api')
));
