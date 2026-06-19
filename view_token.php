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
 * API token view page.
 *
 * @package    local_su_statboard_api
 * @copyright  2025 Sorbonne Université
 * @copyright  2025 Victor Da Silva Caseiro <victor.da_silva_caseiro@sorbonne-universite.fr>
 * @copyright  2025 Thomas Naudin <thomas.naudin@sorbonne-universite.fr>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/su_statboard_api/locallib.php');

// Check permissions.
require_login();
$context = context_system::instance();
require_capability('local/su_statboard_api:view', $context);

// Set up page.
$PAGE->set_context($context);
$PAGE->set_url('/local/su_statboard_api/view_token.php');
$PAGE->set_title(get_string('token_page_title', 'local_su_statboard_api'));
$PAGE->set_heading(get_string('token_page_title', 'local_su_statboard_api'));

// Get token information.
$token = get_config('local_su_statboard_api', 'webservice_token');
$service = $DB->get_record('external_services', ['shortname' => 'local_su_statboard_api']);

// Prepare context for template.
$templatecontext = new stdClass();
$templatecontext->token = $token;

if ($service && $token) {
    $tokeninfo = $DB->get_record('external_tokens', ['token' => $token, 'externalserviceid' => $service->id]);

    if ($tokeninfo) {
        // Create expiration information.
        if (empty($tokeninfo->validuntil)) {
            $expirationinfo = html_writer::tag('div',
                get_string('token_no_expiration_info', 'local_su_statboard_api'),
                ['class' => 'alert alert-info mt-3']
            );
        } else {
            $isexpired = ($tokeninfo->validuntil < time());
            $statusclass = $isexpired ? 'alert-danger' : 'alert-success';
            $statustext = $isexpired
                ? get_string('token_expired', 'local_su_statboard_api')
                : get_string('token_valid', 'local_su_statboard_api');

            $expirationinfo = html_writer::tag('div',
                $statustext . ': ' . userdate($tokeninfo->validuntil, get_string('strftimedatetimeshort', 'core_langconfig')),
                ['class' => 'alert ' . $statusclass . ' mt-3']
            );
        }
        $templatecontext->expiration_html = $expirationinfo;
    }
}

// Register JS module.
$PAGE->requires->js_call_amd('local_su_statboard_api/token_manager', 'init', [[]]);

// Output page.
echo $OUTPUT->header();

// Display the plugin header with logo.
echo local_su_statboard_api_display_header(
    get_string('token_page_title', 'local_su_statboard_api'),
    get_string('token_intro', 'local_su_statboard_api')
);

echo $OUTPUT->render_from_template('local_su_statboard_api/view_token', $templatecontext);
echo $OUTPUT->footer();
