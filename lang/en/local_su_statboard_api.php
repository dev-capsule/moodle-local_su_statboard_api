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
 * Language strings for local_su_statboard_api.
 *
 * @package    local_su_statboard_api
 * @copyright  2025 Sorbonne Université
 * @copyright  2025 Victor Da Silva Caseiro <victor.da_silva_caseiro@sorbonne-universite.fr>
 * @copyright  2025 Thomas Naudin <thomas.naudin@sorbonne-universite.fr>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// General plugin strings.

$string['back_to_settings'] = 'Back to settings';
$string['cachedef_statboard_max'] = 'Dashboard maximum daily connections (30 days)';
$string['cachedef_statboard_quiz'] = 'Quiz completed today';
$string['cachedef_statboard_totals'] = 'Dashboard totals (users, courses)';
$string['copy_token'] = 'Copy token';
$string['cron_info_desc'] = 'This plugin uses two scheduled tasks. The daily task runs every night at 00:05 and calculates the number of distinct logins for the previous day, storing the result in a summary table so the API can instantly read the 30-day maximum. The hourly task runs every hour at HH:01 and calculates a connection snapshot for the previous hour (users active in the 5 minutes before each hour mark). Both tasks automatically purge outdated entries.';
$string['cron_info_heading'] = 'Scheduled tasks';
$string['current_expiration'] = 'Current expiration';
$string['current_token'] = 'Current token';
$string['data_retention_desc'] = 'Hourly connection snapshots are retained for 30 rolling days (720 rows maximum). The dashboard chart allows navigation over the last 30 days via the date selector. Daily maximum connection statistics are also retained for 30 days.';
$string['data_retention_heading'] = 'Data retention';
$string['enable_edit'] = 'Enable editing';
$string['eventstatsviewed'] = 'Statistics viewed';
$string['expiration_date'] = 'Expiration date';
$string['expiration_date_desc'] = 'Select an expiration date for the token.';
$string['expiration_date_past'] = 'Expiration date cannot be in the past';
$string['expiration_section'] = 'Expiration settings';
$string['inconsistency_token_should_be_permanent'] = 'Inconsistency: configuration says "no expiration" but the token actually expires on {$a}. Please go to token settings to fix this.';
$string['inconsistency_token_should_expire'] = 'Inconsistency: configuration says the token should expire but it is currently set as permanent. Please go to token settings to fix this.';
$string['manage_token'] = 'Manage token';
$string['modify_expiration_date'] = 'Modify token expiration date';
$string['modify_expiration_instructions'] = 'Select a new expiration date for the token. The selected date will be applied to all tokens for this service.';
$string['modify_token'] = 'Modify token';
$string['modify_token_instructions'] = 'Use this form to modify the web service token.';
$string['modify_validity_period'] = 'Modify validity period';
$string['modify_validity_period_instructions'] = 'Use the form below to modify the token validity period. The value is expressed in days.';
$string['new_token'] = 'New token';
$string['no_expiration'] = 'Token without expiration';
$string['no_expiration_desc'] = 'If enabled, the token will never expire';
$string['no_expiration_label'] = 'Never expire this token';
$string['no_token'] = 'No token found. Try reinstalling the plugin.';
$string['no_token_configured'] = 'No token configured. Please reinstall the plugin.';
$string['no_tokens_found'] = 'No tokens found for this service. Please reinstall the plugin.';
$string['pluginname'] = 'Statboard API';
$string['privacy:metadata:core_logging'] = 'The plugin logs user actions through the Moodle logging system';
$string['privacy:metadata:external_tokens'] = 'Information about the stored web service tokens for accessing the API';
$string['privacy:metadata:external_tokens:token'] = 'The token value';
$string['privacy:metadata:external_tokens:userid'] = 'The ID of the user that the token belongs to';
$string['privacy:metadata:external_tokens:validuntil'] = 'The date until which the token is valid';
$string['privacy:metadata:moodle_webservice'] = 'The plugin transmits data externally using Moodle web services';
$string['privacy:metadata:moodle_webservice:token'] = 'The user\'s token for authentication with the web service';
$string['privacy:metadata:moodle_webservice:user_id'] = 'The user ID for authentication with the web service';
$string['regenerate_token'] = 'Generate new token';
$string['save_changes'] = 'Save changes';
$string['service_not_found'] = 'Web service not found. Please reinstall the plugin.';
$string['settings'] = 'Statboard API settings';
$string['su_statboard_api:managetokensettings'] = 'Manage API token settings';
$string['su_statboard_api:view'] = 'View usage statistics';
$string['su_token_admin'] = 'API Token';
$string['task_aggregate_daily_stats'] = 'Daily login statistics aggregation';
$string['task_aggregate_hourly_stats'] = 'Hourly connection snapshot aggregation';
$string['token'] = 'Web service token';
$string['token_copied'] = 'Token copied to clipboard!';
$string['token_desc'] = 'Use this token to access statistics via the API';
$string['token_empty'] = 'Token cannot be empty';
$string['token_error'] = 'Error updating token';
$string['token_expiration_date'] = 'Token expiration date';
$string['token_expiration_date_desc'] = 'Select the date on which the token will expire. This date will be applied to all tokens for this service.';
$string['token_expiration_disabled'] = 'Token expiration has been disabled.';
$string['token_expiration_enabled'] = 'Token expiration has been enabled.';
$string['token_expired'] = 'Token expired';
$string['token_expires'] = 'Expiration date';
$string['token_intro'] = 'Use this token to access the logs API:';
$string['token_management'] = 'Token management';
$string['token_management_desc'] = 'Use this page to manage the web service token settings.';
$string['token_no_expiration_info'] = 'Token is configured to never expire';
$string['token_not_found_db'] = 'Token not found in database. Please reinstall the plugin.';
$string['token_page_title'] = 'API token for SU Statboard logs';
$string['token_placeholder'] = 'Enter new token';
$string['token_regenerated'] = 'Token successfully regenerated';
$string['token_section'] = 'Web service token';
$string['token_settings_title'] = 'API token settings';
$string['token_update_exception'] = 'Error updating token';
$string['token_updated'] = 'Token successfully updated';
$string['token_valid'] = 'Token valid until';
$string['token_validity_period'] = 'Token validity period (days)';
$string['token_validity_period_desc'] = 'Number of days the token will be valid before expiration';
$string['update_expiration_date'] = 'Update expiration date';
$string['update_token'] = 'Update token';
$string['update_validity_period'] = 'Update validity period';
$string['validity_days'] = 'Validity days';
$string['validity_period_error'] = 'Error updating validity period';
$string['validity_period_invalid'] = 'Validity period must be a positive number';
$string['validity_period_updated'] = 'Validity period successfully updated';
$string['view_token'] = 'View API token';
