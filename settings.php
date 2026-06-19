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
 * Settings for local_su_statboard_api.
 *
 * @package    local_su_statboard_api
 * @copyright  2025 Sorbonne Université
 * @copyright  2025 Victor Da Silva Caseiro <victor.da_silva_caseiro@sorbonne-universite.fr>
 * @copyright  2025 Thomas Naudin <thomas.naudin@sorbonne-universite.fr>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    global $CFG, $PAGE, $DB;

    require_once($CFG->dirroot . '/local/su_statboard_api/locallib.php');

    $settings = new admin_settingpage('local_su_statboard_api_settings',
        get_string('pluginname', 'local_su_statboard_api'));

    // Add a link to the token management page.
    $settings->add(new admin_setting_heading(
        'local_su_statboard_api/token_management',
        get_string('token_management', 'local_su_statboard_api'),
        get_string('token_management_desc', 'local_su_statboard_api') .
        '<br><a href="' . $CFG->wwwroot . '/local/su_statboard_api/token_settings.php" class="btn btn-primary">' .
        get_string('manage_token', 'local_su_statboard_api') . '</a>'
    ));

    // Display the current expiration date.
    $token = get_config('local_su_statboard_api', 'webservice_token');
    if (!empty($token)) {
        // Get token information to display expiration date.
        $service = $DB->get_record('external_services', ['shortname' => 'local_su_statboard_api']);
        if ($service) {
            $tokeninfo = $DB->get_record('external_tokens', ['token' => $token, 'externalserviceid' => $service->id]);

            if ($tokeninfo) {
                // Get the "no expiration" configuration.
                $noexpiration = get_config('local_su_statboard_api', 'token_no_expiration');

                // Check if the token has an expiration date or not.
                $hasrealexpiration = !empty($tokeninfo->validuntil) && $tokeninfo->validuntil > 0;

                if ($noexpiration === '1' && !$hasrealexpiration) {
                    // Configuration says "no expiration" AND token really has no expiration.
                    $expirationinfo = html_writer::tag('div',
                        get_string('token_no_expiration_info', 'local_su_statboard_api'),
                        ['class' => 'alert alert-info']
                    );
                } else if ($noexpiration === '1' && $hasrealexpiration) {
                    // Configuration says "no expiration" BUT token has an expiration - inconsistency!
                    $expirationinfo = html_writer::tag('div',
                        get_string('inconsistency_token_should_be_permanent',
                            'local_su_statboard_api',
                            userdate($tokeninfo->validuntil, get_string('strftimedatetimeshort', 'core_langconfig'))),
                        ['class' => 'alert alert-warning']
                    );
                } else if ($noexpiration !== '1' && $hasrealexpiration) {
                    // Configuration says "has expiration" AND token really has expiration.
                    $isexpired = ($tokeninfo->validuntil < time());
                    $statusclass = $isexpired ? 'alert-danger' : 'alert-success';
                    $statustext = $isexpired
                        ? get_string('token_expired', 'local_su_statboard_api')
                        : get_string('token_valid', 'local_su_statboard_api');

                    $expirationinfo = html_writer::tag('div',
                        $statustext . ': ' . userdate($tokeninfo->validuntil,
                            get_string('strftimedatetimeshort', 'core_langconfig')),
                        ['class' => 'alert ' . $statusclass]
                    );
                } else {
                    // Configuration says "has expiration" BUT token has no expiration - inconsistency!
                    $expirationinfo = html_writer::tag('div',
                        get_string('inconsistency_token_should_expire', 'local_su_statboard_api'),
                        ['class' => 'alert alert-warning']
                    );
                }

                $settings->add(new admin_setting_heading(
                    'local_su_statboard_api/token_expiration_info',
                    get_string('token_expiration_date', 'local_su_statboard_api'),
                    $expirationinfo
                ));
            } else {
                // Token not found in database.
                $settings->add(new admin_setting_heading(
                    'local_su_statboard_api/token_expiration_info',
                    get_string('token_expiration_date', 'local_su_statboard_api'),
                    html_writer::tag('div',
                        get_string('token_not_found_db', 'local_su_statboard_api'),
                        ['class' => 'alert alert-danger']
                    )
                ));
            }
        } else {
            // Service not found.
            $settings->add(new admin_setting_heading(
                'local_su_statboard_api/token_expiration_info',
                get_string('token_expiration_date', 'local_su_statboard_api'),
                html_writer::tag('div',
                    get_string('service_not_found', 'local_su_statboard_api'),
                    ['class' => 'alert alert-danger']
                )
            ));
        }
    } else {
        // No token configured.
        $settings->add(new admin_setting_heading(
            'local_su_statboard_api/token_expiration_info',
            get_string('token_expiration_date', 'local_su_statboard_api'),
            html_writer::tag('div',
                get_string('no_token_configured', 'local_su_statboard_api'),
                ['class' => 'alert alert-warning']
            )
        ));
    }
    // Cron information section.
    $cronurl = new moodle_url('/admin/tool/task/scheduledtasks.php');
    $settings->add(new admin_setting_heading(
        'local_su_statboard_api/cron_info',
        get_string('cron_info_heading', 'local_su_statboard_api'),
        html_writer::tag('div',
            get_string('cron_info_desc', 'local_su_statboard_api'),
            ['class' => 'alert alert-info']
        ) .
        '<a href="' . $cronurl . '" class="btn btn-secondary btn-sm mt-2">' .
        get_string('scheduledtasks', 'tool_task') . '</a>'
    ));

    // Data retention information section.
    $settings->add(new admin_setting_heading(
        'local_su_statboard_api/data_retention_info',
        get_string('data_retention_heading', 'local_su_statboard_api'),
        html_writer::tag('div',
            get_string('data_retention_desc', 'local_su_statboard_api'),
            ['class' => 'alert alert-info']
        )
    ));

    $ADMIN->add('localplugins', $settings);
}
