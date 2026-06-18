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
 * Library functions for local_su_statboard_api.
 *
 * @package    local_su_statboard_api
 * @copyright  2025 Sorbonne Université
 * @copyright  2025 Victor Da Silva Caseiro <victor.da_silva_caseiro@sorbonne-universite.fr>
 * @copyright  2025 Thomas Naudin <thomas.naudin@sorbonne-universite.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Get database-compatible SQL for date operations.
 *
 * @param string $operation The operation type ('date_format', 'hour_extract', etc.)
 * @param string $column The column name to operate on
 * @param string $format Optional format parameter
 * @return string Database-compatible SQL
 */
function local_su_statboard_api_get_db_compatible_sql($operation, $column, $format = null) {
    global $DB;

    $dbfamily = $DB->get_dbfamily();

    switch ($operation) {
        case 'date_format':
            if ($dbfamily === 'postgres') {
                // PostgreSQL example: use "to_char(to_timestamp(column), 'YYYY-MM-DD')".
                return "to_char(to_timestamp({$column}), 'YYYY-MM-DD')";
            } else {
                // MySQL/MariaDB: FROM_UNIXTIME(column, '%Y-%m-%d').
                return "FROM_UNIXTIME({$column}, '%Y-%m-%d')";
            }

        case 'hour_extract':
            if ($dbfamily === 'postgres') {
                // PostgreSQL: EXTRACT(hour FROM to_timestamp(column)).
                return "EXTRACT(hour FROM to_timestamp({$column}))";
            } else {
                // MySQL/MariaDB: HOUR(FROM_UNIXTIME(column)).
                return "HOUR(FROM_UNIXTIME({$column}))";
            }

        case 'timestamp_to_date':
            if ($dbfamily === 'postgres') {
                return "to_timestamp({$column})";
            } else {
                return "FROM_UNIXTIME({$column})";
            }

        default:
            return $column;
    }
}

/**
 * Updates the web service token for the local_su_statboard_api plugin.
 *
 * @param string $newtoken The new token to use.
 * @return bool|string True if update successful, error message otherwise.
 */
function local_su_statboard_api_update_token($newtoken) {
    global $DB;

    // Check that token is not empty.
    if (empty($newtoken)) {
        return get_string('token_empty', 'local_su_statboard_api');
    }

    try {
        // 1. Get web service information.
        $service = $DB->get_record('external_services', ['shortname' => 'local_su_statboard_api']);
        if (!$service) {
            return get_string('service_not_found', 'local_su_statboard_api');
        }

        // 2. Get the old token.
        $oldtoken = get_config('local_su_statboard_api', 'webservice_token');

        // 3. Find the token record in the database.
        $tokenrecord = null;
        if (!empty($oldtoken)) {
            $tokenrecord = $DB->get_record('external_tokens', ['token' => $oldtoken, 'externalserviceid' => $service->id]);
        }

        if (!$tokenrecord) {
            // If token doesn't exist, try to find any token for this service.
            $tokens = $DB->get_records('external_tokens', ['externalserviceid' => $service->id], 'id DESC', '*', 0, 1);
            if (empty($tokens)) {
                return get_string('no_tokens_found', 'local_su_statboard_api');
            }
            $tokenrecord = reset($tokens);
        }

        // 4. Update the token in the database.
        $tokenrecord->token = $newtoken;
        $DB->update_record('external_tokens', $tokenrecord);

        // 5. Update the configuration.
        set_config('webservice_token', $newtoken, 'local_su_statboard_api');

        return true;
    } catch (Exception $e) {
        return get_string('token_update_exception', 'local_su_statboard_api') . ': ' . $e->getMessage();
    }
}

/**
 * Generates a new token for the web service and updates it.
 *
 * @return bool|string True if update successful, error message otherwise.
 */
function local_su_statboard_api_regenerate_token() {
    global $DB, $CFG;

    // Token generation uses core_external\util (modern Moodle 4.x API)
    // — no need to include lib/externallib.php (which would conflict with PHPUnit isolation).

    try {
        // 1. Get web service information.
        $service = $DB->get_record('external_services', ['shortname' => 'local_su_statboard_api']);
        if (!$service) {
            return get_string('service_not_found', 'local_su_statboard_api');
        }

        // 2. Get the old token.
        $oldtoken = get_config('local_su_statboard_api', 'webservice_token');

        // 3. Find the token record in the database.
        $tokenrecord = null;
        if (!empty($oldtoken)) {
            $tokenrecord = $DB->get_record('external_tokens', ['token' => $oldtoken, 'externalserviceid' => $service->id]);
        }

        if (!$tokenrecord) {
            // If token doesn't exist, try to find any token for this service.
            $tokens = $DB->get_records('external_tokens', ['externalserviceid' => $service->id], 'id DESC', '*', 0, 1);
            if (empty($tokens)) {
                return get_string('no_tokens_found', 'local_su_statboard_api');
            }
            $tokenrecord = reset($tokens);
        }

        // 4. Delete the old token.
        $DB->delete_records('external_tokens', ['id' => $tokenrecord->id]);

        // 5. Determine token validity.
        $noexpiration = get_config('local_su_statboard_api', 'token_no_expiration');
        $validuntil = 0; // By default, no expiration.

        if ($noexpiration !== '1') {
            $validityperiod = get_config('local_su_statboard_api', 'token_validity_period');
            if (empty($validityperiod)) {
                $validityperiod = 365; // Default value if not configured.
            }
            $validuntil = time() + ($validityperiod * 24 * 60 * 60);
        }

        // 6. Ensure iprestriction is an empty string and not null.
        $iprestriction = '';
        if (!empty($tokenrecord->iprestriction)) {
            $iprestriction = $tokenrecord->iprestriction;
        }

        // 7. Generate a new token via Moodle standard API.
        $context = context_system::instance();
        // EXTERNAL_TOKEN_PERMANENT is defined in lib/externallib.php as value 0; we use the
        // literal here to avoid having to include that file (PHPUnit isolation concern).
        // util::generate_token() expects the service OBJECT, not its ID.
        $newtoken = \core_external\util::generate_token(
            0, // EXTERNAL_TOKEN_PERMANENT.
            $service,
            $tokenrecord->userid,
            $context,
            $validuntil,
            $iprestriction
        );

        // 8. Update the configuration.
        set_config('webservice_token', $newtoken, 'local_su_statboard_api');

        return true;
    } catch (Exception $e) {
        return get_string('token_update_exception', 'local_su_statboard_api') . ': ' . $e->getMessage();
    }
}

/**
 * Updates the token expiration date and synchronizes with Moodle.
 *
 * @param int $timestamp Timestamp of the new expiration date.
 * @return bool True if update successful, false otherwise.
 */
function local_su_statboard_api_update_expiration_date($timestamp) {
    global $DB;

    try {
        // 1. Update the validity period configuration.
        $days = ceil(($timestamp - time()) / (24 * 60 * 60));
        set_config('token_validity_period', $days, 'local_su_statboard_api');

        // 2. Get the service.
        $service = $DB->get_record('external_services', ['shortname' => 'local_su_statboard_api']);
        if (!$service) {
            return false;
        }

        // 3. Update all tokens associated with this service.
        $tokens = $DB->get_records('external_tokens', ['externalserviceid' => $service->id]);
        foreach ($tokens as $tokenrecord) {
            $tokenrecord->validuntil = $timestamp;
            $DB->update_record('external_tokens', $tokenrecord);
        }

        // 4. Specific update for the current token using Moodle's database abstraction.
        $token = get_config('local_su_statboard_api', 'webservice_token');
        if (!empty($token)) {
            $DB->set_field('external_tokens', 'validuntil', $timestamp, ['token' => $token]);
        }

        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Observer for configuration changes.
 * Called when configuration settings are modified in Moodle.
 *
 * @param \core\event\config_log_created $event The configuration change event.
 */
function local_su_statboard_api_config_changed_observer(\core\event\config_log_created $event) {
    // Get event data.
    $eventdata = $event->get_data();

    // Only process changes related to our plugin.
    if (isset($eventdata['other']['plugin']) && $eventdata['other']['plugin'] === 'local_su_statboard_api') {

        // Log the configuration change for audit purposes.
        $configname = isset($eventdata['other']['name']) ? $eventdata['other']['name'] : 'unknown';
        $configvalue = isset($eventdata['other']['value']) ? $eventdata['other']['value'] : 'unknown';

        // You can add specific logic here based on what configuration changed.
        switch ($configname) {
            case 'token_no_expiration':
                // Token expiration setting changed.
                // Could trigger token updates if needed.
                break;

            case 'token_validity_period':
                // Token validity period changed.
                // Could update existing tokens if needed.
                break;

            case 'webservice_token':
                // Token itself changed.
                // Could trigger cache clearing or notifications.
                break;

            default:
                // Other configuration changes.
                break;
        }

        // For now, we just log that a change occurred.
        // In the future, this could trigger specific actions based on the change.
        debugging("SU Statboard API: Configuration '$configname' changed to '$configvalue'");
    }
}

/**
 * Updates the validuntil field of tokens to indicate they never expire.
 *
 * @param int $serviceid ID of the web service.
 * @param string $token Specific token to update (optional).
 * @return bool True if update successful, false otherwise.
 */
function local_su_statboard_api_set_token_no_expiration($serviceid, $token = null) {
    global $DB;

    try {
        // Build conditions array for better database compatibility.
        $conditions = ['externalserviceid' => $serviceid];

        // Add the condition for the specific token if provided.
        if (!empty($token)) {
            $conditions['token'] = $token;
        }

        // Use Moodle's database abstraction to set validuntil to null.
        // In PostgreSQL, we need to set to null, not 0.
        $tokens = $DB->get_records('external_tokens', $conditions);
        foreach ($tokens as $tokenrecord) {
            $tokenrecord->validuntil = null;
            $DB->update_record('external_tokens', $tokenrecord);
        }

        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Display the plugin header with logo.
 *
 * @param string $title
 * @param string $subtitle
 * @return string
 */
function local_su_statboard_api_display_header($title, $subtitle = '') {
    global $OUTPUT;

    // Render the icon directly with html_writer + inline style to bypass Moodle's
    // .icon CSS rule which forces 16x16 with high specificity / !important.
    $iconurl = $OUTPUT->image_url('icon', 'local_su_statboard_api');
    $icon = html_writer::empty_tag('img', [
        'src'   => $iconurl->out(false),
        'alt'   => get_string('pluginname', 'local_su_statboard_api'),
        'class' => 'plugin-icon',
        'style' => 'width: 48px; height: 48px; max-width: 48px; max-height: 48px; '
                   . 'margin-right: 1rem; flex-shrink: 0; '
                   . 'filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));',
    ]);

    $header = html_writer::start_div('plugin-header d-flex align-items-center mb-4');
    $header .= $icon;
    $header .= html_writer::start_div('plugin-titles ml-3');
    $header .= html_writer::tag('h2', $title, ['class' => 'plugin-title mb-1']);
    if (!empty($subtitle)) {
        $header .= html_writer::tag('p', $subtitle, ['class' => 'plugin-subtitle text-muted mb-0']);
    }
    $header .= html_writer::end_div();
    $header .= html_writer::end_div();

    return $header;
}

/**
 * Display the simplified plugin header with logo for templates.
 *
 * @param string $title
 * @return array
 */
function local_su_statboard_api_get_header_context($title) {
    global $OUTPUT;

    return [
        'icon' => $OUTPUT->pix_icon('icon', get_string('pluginname', 'local_su_statboard_api'), 'local_su_statboard_api'),
        'title' => $title,
        'plugin_name' => get_string('pluginname', 'local_su_statboard_api'),
    ];
}
