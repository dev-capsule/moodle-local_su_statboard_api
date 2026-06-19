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
 * Token management page for local_su_statboard_api.
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

// Page configuration.
$context = context_system::instance();
$PAGE->set_context($context);
require_login();
require_capability('moodle/site:config', $context);

$PAGE->set_url('/local/su_statboard_api/token_settings.php');
$PAGE->set_title(get_string('token_settings_title', 'local_su_statboard_api'));
$PAGE->set_heading(get_string('pluginname', 'local_su_statboard_api'));

// Get current token.
$token = get_config('local_su_statboard_api', 'webservice_token');
$validityperiod = get_config('local_su_statboard_api', 'token_validity_period');
if (empty($validityperiod)) {
    $validityperiod = 365; // Default value if not configured.
}

// Initialize variables for expiration.
$expirationdate = null;
$isexpired = false;

// Get current token information from database.
global $DB;
$service = $DB->get_record('external_services', ['shortname' => 'local_su_statboard_api']);
if ($service && $token) {
    $tokeninfo = $DB->get_record('external_tokens', ['token' => $token, 'externalserviceid' => $service->id]);
    if ($tokeninfo && !empty($tokeninfo->validuntil)) {
        $expirationdate = $tokeninfo->validuntil;
        $isexpired = ($expirationdate < time());
    }
}

// Get the state of the "no expiration" option.
$noexpiration = get_config('local_su_statboard_api', 'token_no_expiration');

// If parameter is not defined, set a default value.
if ($noexpiration === false) {
    // By default, option is disabled.
    set_config('token_no_expiration', '0', 'local_su_statboard_api');
    $noexpiration = '0';
}

// Check if token has an expiration date or not.
$hasnormalexpiration = false;
if ($tokeninfo) {
    // A token without expiration has validuntil NULL or 0 in Moodle.
    if (isset($tokeninfo->validuntil) && $tokeninfo->validuntil !== null && $tokeninfo->validuntil > 0) {
        $hasnormalexpiration = true;
    }
}

// If the token has a normal expiration date and the "no expiration" option is enabled,
// there is inconsistency - priority is given to reality (token has an expiration date).
if ($noexpiration === '1' && $hasnormalexpiration) {
    // Disable "no expiration" option to be consistent with reality.
    set_config('token_no_expiration', '0', 'local_su_statboard_api');
    $noexpiration = '0';
}

// If the token has no expiration date and the "no expiration" option is disabled,
// there is inconsistency - priority is given to reality (token has no expiration date).
if ($noexpiration === '0' && $tokeninfo && $tokeninfo->validuntil === null) {
    // Enable "no expiration" option to be consistent with reality.
    set_config('token_no_expiration', '1', 'local_su_statboard_api');
    $noexpiration = '1';
}

// Update validity period if necessary.
if ($noexpiration === '1') {
    if ($tokeninfo && $tokeninfo->validuntil !== null) {
        // Update token to never expire.
        $tokeninfo->validuntil = null;
        $DB->update_record('external_tokens', $tokeninfo);
    }
} else if ($noexpiration === '0' && (!$expirationdate || $isexpired)) {
    // Option is disabled AND there is no valid expiration date.
    // Set a default expiration date.
    if ($tokeninfo) {
        $tokeninfo->validuntil = time() + ($validityperiod * 24 * 60 * 60);
        $DB->update_record('external_tokens', $tokeninfo);
        $expirationdate = $tokeninfo->validuntil;
        $isexpired = false;
    }
}

if (!$expirationdate && $noexpiration === '0') {
    $expirationdate = time() + ($validityperiod * 24 * 60 * 60);
}

// Process token regeneration.
$message = '';
$messagetype = '';

// Capture form submissions through Moodle's optional_param() rather than $_POST directly.
// PARAM_TEXT is used for the two submit buttons because their value comes from a translated
// language string (e.g. "Regenerate token") — we only test for non-empty to detect a click.
$regeneratesubmitted = optional_param('regenerate_token', '', PARAM_TEXT);
$settingssubmitted   = optional_param('submit_settings', '', PARAM_TEXT);
$noexpirationposted  = optional_param('token_no_expiration', 0, PARAM_BOOL);
$expirationdateinput = optional_param('expiration_date', '', PARAM_TEXT);

if ($regeneratesubmitted !== '' && confirm_sesskey()) {
    $result = local_su_statboard_api_regenerate_token();

    if ($result === true) {
        // Update current token for display.
        $token = get_config('local_su_statboard_api', 'webservice_token');
        $message = get_string('token_regenerated', 'local_su_statboard_api');
        $messagetype = 'success';

        // Get new token information.
        if ($service) {
            $tokeninfo = $DB->get_record('external_tokens', ['token' => $token, 'externalserviceid' => $service->id]);
            if ($tokeninfo && !empty($tokeninfo->validuntil)) {
                $expirationdate = $tokeninfo->validuntil;
                $isexpired = ($expirationdate < time());
            }
        }
    } else {
        $message = $result; // Detailed error message.
        $messagetype = 'error';
    }
}

// Process form submission.
if ($settingssubmitted !== '' && confirm_sesskey()) {
    // Handle expiration option.
    $newnoexpiration = $noexpirationposted ? '1' : '0';

    // Only if the value has changed.
    if ($newnoexpiration != $noexpiration) {
        set_config('token_no_expiration', $newnoexpiration, 'local_su_statboard_api');
        $noexpiration = $newnoexpiration;

        // Update tokens.
        if ($service) {
            if ($newnoexpiration === '1') {
                // Option enabled: set validuntil to NULL to indicate "no expiration".
                // Use the dedicated function to avoid problems.
                $result = local_su_statboard_api_set_token_no_expiration($service->id, $token);

                if ($result) {
                    // Update local variable for display.
                    if ($tokeninfo) {
                        $tokeninfo->validuntil = null;
                    }

                    $message = get_string('token_expiration_disabled', 'local_su_statboard_api');
                    $messagetype = 'success';

                    // Update local variables.
                    $expirationdate = null;
                    $isexpired = false;
                } else {
                    $message = get_string('token_update_exception', 'local_su_statboard_api');
                    $messagetype = 'error';
                }
            } else {
                // Option disabled: use the configured validity period.
                $validityperiod = get_config('local_su_statboard_api', 'token_validity_period');
                if (empty($validityperiod)) {
                    $validityperiod = 365; // Default 1 year if not configured.
                }
                $expirationtimestamp = time() + ($validityperiod * 24 * 60 * 60);

                // Update ALL tokens associated with this service using Moodle's database abstraction.
                $tokens = $DB->get_records('external_tokens', ['externalserviceid' => $service->id]);
                foreach ($tokens as $tokenrecord) {
                    $tokenrecord->validuntil = $expirationtimestamp;
                    $DB->update_record('external_tokens', $tokenrecord);
                }

                // Ensure our specific token is also updated using Moodle's API.
                if ($token) {
                    $DB->set_field('external_tokens', 'validuntil', $expirationtimestamp, ['token' => $token]);
                }

                // Update local variable for display.
                if ($tokeninfo) {
                    $tokeninfo->validuntil = $expirationtimestamp;
                }

                $message = get_string('token_expiration_enabled', 'local_su_statboard_api');
                $messagetype = 'success';

                // Update local variables.
                $expirationdate = $expirationtimestamp;
                $isexpired = false;
            }
        }
    }

    // If expiration is enabled and a new date is provided.
    if ($noexpiration === '0' && !empty($expirationdateinput)) {
        $inputdate = $expirationdateinput;

        // Format is YYYY-MM-DD.
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $inputdate, $matches)) {
            $year = (int)$matches[1];
            $month = (int)$matches[2];
            $day = (int)$matches[3];

            // Check that the date is valid.
            if (checkdate($month, $day, $year)) {
                $timestamp = mktime(0, 0, 0, $month, $day, $year);

                if ($timestamp <= time()) {
                    $message = get_string('expiration_date_past', 'local_su_statboard_api');
                    $messagetype = 'error';
                } else {
                    // Update ALL tokens associated with this service using Moodle's database abstraction.
                    if ($service) {
                        $tokens = $DB->get_records('external_tokens', ['externalserviceid' => $service->id]);
                        foreach ($tokens as $tokenrecord) {
                            $tokenrecord->validuntil = $timestamp;
                            $DB->update_record('external_tokens', $tokenrecord);
                        }

                        // Ensure our specific token is also updated using Moodle's API.
                        if ($token) {
                            $DB->set_field('external_tokens', 'validuntil', $timestamp, ['token' => $token]);
                        }

                        // Update the validity period configuration.
                        $days = ceil(($timestamp - time()) / (24 * 60 * 60));
                        set_config('token_validity_period', $days, 'local_su_statboard_api');

                        $message = get_string('validity_period_updated', 'local_su_statboard_api');
                        $messagetype = 'success';

                        // Update local variables.
                        $validityperiod = $days;
                        $expirationdate = $timestamp;
                        $isexpired = false;

                        // Update local variable for display.
                        if ($tokeninfo) {
                            $tokeninfo->validuntil = $timestamp;
                        }
                    } else {
                        $message = get_string('validity_period_error', 'local_su_statboard_api');
                        $messagetype = 'error';
                    }
                }
            }
        }
    }

    // Get current data after update.
    if ($service) {
        $tokeninfo = $DB->get_record('external_tokens', ['token' => $token, 'externalserviceid' => $service->id]);
        if ($tokeninfo && !empty($tokeninfo->validuntil)) {
            $expirationdate = $tokeninfo->validuntil;
            $isexpired = ($expirationdate < time());
        }
    }

    // Check the state of the "no expiration" option again after form processing.
    $noexpiration = get_config('local_su_statboard_api', 'token_no_expiration');
    if ($noexpiration === false) {
        $noexpiration = '0';
    }
}

// Create context object for mustache template.
$templatecontext = new stdClass();
$templatecontext->sesskey = sesskey();
$templatecontext->action = $PAGE->url->out(false);
$templatecontext->token = $token;
$templatecontext->token_no_expiration = ($noexpiration === '1');

// Format expiration date for datepicker field.
$formatteddate = date('Y-m-d', time() + (365 * 24 * 60 * 60));
if ($noexpiration === '0' && $expirationdate && !$isexpired) {
    $formatteddate = date('Y-m-d', $expirationdate);
}
$templatecontext->expiration_date = $formatteddate;
$templatecontext->min_date = date('Y-m-d', time());

// Create HTML for expiration status.
$expirationhtml = '';
if ($noexpiration === '1') {
    $expirationhtml = html_writer::tag('div',
        get_string('token_no_expiration_info', 'local_su_statboard_api'),
        ['class' => 'alert alert-info']
    );
} else if ($expirationdate) {
    $statusclass = $isexpired ? 'alert-danger' : 'alert-success';
    $statustext = $isexpired
        ? get_string('token_expired', 'local_su_statboard_api')
        : get_string('token_valid', 'local_su_statboard_api');

    $expirationhtml = html_writer::tag('div',
        $statustext . ': ' . userdate($expirationdate, get_string('strftimedatetimeshort', 'core_langconfig')),
        ['class' => 'alert ' . $statusclass]
    );
}
$templatecontext->current_expiration_html = $expirationhtml;
$templatecontext->return_url = new moodle_url('/admin/settings.php',
    ['section' => 'local_su_statboard_api_settings']);

// Display message if present.
if (!empty($message)) {
    $templatecontext->notification = (object)[
        'message' => $message,
        'type' => $messagetype,
    ];
}

// Register JS module.
$PAGE->requires->js_call_amd('local_su_statboard_api/token_manager', 'init', [[]]);

// Display the page.
echo $OUTPUT->header();

// Display the plugin header with logo.
echo local_su_statboard_api_display_header(
    get_string('token_settings_title', 'local_su_statboard_api'),
    get_string('token_management_desc', 'local_su_statboard_api')
);

echo $OUTPUT->render_from_template('local_su_statboard_api/token_settings', $templatecontext);
echo $OUTPUT->footer();
