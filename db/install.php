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
 * Installation for local_su_statboard_api.
 *
 * @package    local_su_statboard_api
 * @copyright  2025 Sorbonne Université
 * @copyright  2025 Victor Da Silva Caseiro <victor.da_silva_caseiro@sorbonne-universite.fr>
 * @copyright  2025 Thomas Naudin <thomas.naudin@sorbonne-universite.fr>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Installation function for the plugin.
 *
 * @return bool True if success, false otherwise.
 */
function xmldb_local_su_statboard_api_install() {
    global $DB, $CFG;

    // Database compatibility check.
    $dbfamily = $DB->get_dbfamily();
    $dbversion = $DB->get_server_info();
    $dbtype = isset($CFG->dbtype) ? $CFG->dbtype : $dbfamily;

    mtrace('SU Statboard API: Starting installation...');

    // Check for unsupported databases.
    $unsupportedtypes = ['sqlsrv', 'mssql', 'oci', 'sqlite'];
    $unsupportedfamilies = ['mssql', 'oracle'];

    if (in_array($dbtype, $unsupportedtypes) || in_array($dbfamily, $unsupportedfamilies)) {
        $dbname = $dbtype === 'oci' ? 'Oracle' : ($dbtype === 'sqlite' ? 'SQLite' : ucfirst($dbtype));
        mtrace("ERROR: $dbname database is not supported");
        mtrace('Supported databases: PostgreSQL 12+, MySQL 5.7.33+, MariaDB 10.6+');
        return false;
    }

    // Check minimum versions.
    if ($dbfamily === 'postgres') {
        $version = isset($dbversion['version']) ? $dbversion['version'] : '0';
        if (version_compare($version, '12.0', '<')) {
            mtrace("ERROR: PostgreSQL version $version is too old (requires 12.0+)");
            return false;
        }
    } else if ($dbfamily === 'mysql') {
        $version = isset($dbversion['version']) ? $dbversion['version'] : '0';
        $ismariadb = strpos(strtolower($dbversion['description']), 'mariadb') !== false;
        $minversion = $ismariadb ? '10.6' : '5.7.33';
        $dbname = $ismariadb ? 'MariaDB' : 'MySQL';

        if (version_compare($version, $minversion, '<')) {
            mtrace("ERROR: $dbname version $version is too old (requires $minversion+)");
            return false;
        }
    }

    mtrace("Database compatibility: OK ($dbtype $dbversion[version])");

    require_once($CFG->libdir . '/accesslib.php'); // For role_assign().

    try {
        // 1. Clean up existing installation.
        $existingservice = $DB->get_record('external_services', ['shortname' => 'local_su_statboard_api']);
        if ($existingservice) {
            $tokens = $DB->get_records('external_tokens', ['externalserviceid' => $existingservice->id]);
            foreach ($tokens as $token) {
                $DB->delete_records('external_tokens', ['id' => $token->id]);
            }
            $DB->delete_records('external_services_functions', ['externalserviceid' => $existingservice->id]);
            $DB->delete_records('external_services_users', ['externalserviceid' => $existingservice->id]);
            mtrace('Cleanup: Removed existing installation');
        }

        // 2. Create or reuse webservice user.
        $existingusers = $DB->get_records_select(
            'user',
            'username LIKE ? AND deleted = 0',
            ['webservice_statboard_%'],
            'id DESC',
            '*',
            0, 1
        );
        $existinguser = !empty($existingusers) ? reset($existingusers) : null;

        if ($existinguser) {
            $user = $existinguser;
            mtrace("User: Reusing existing user {$user->username}");
        } else {
            $username = 'webservice_statboard_' . time();
            $user = new stdClass();
            $user->auth = 'manual';
            $user->confirmed = 1;
            $user->username = $username;
            $user->password = hash_internal_user_password(generate_password(32));
            $user->firstname = 'Webservice';
            $user->lastname = 'Statboard';
            $user->email = get_admin()->email;
            $user->timecreated = time();
            $user->id = user_create_user($user);
            mtrace("User: Created new user {$user->username}");
        }

        // 3. Assign manager role.
        $systemcontext = context_system::instance();
        $managerrole = $DB->get_record('role', ['shortname' => 'manager']);
        if ($managerrole) {
            $existingassignment = $DB->get_record('role_assignments', [
                'roleid' => $managerrole->id,
                'contextid' => $systemcontext->id,
                'userid' => $user->id,
            ]);
            if (!$existingassignment) {
                role_assign($managerrole->id, $user->id, $systemcontext->id);
            }
            mtrace('Permissions: Manager role assigned');
        }

        // 4. Create web service.
        $service = new stdClass();
        $service->name = 'SU Statboard API Service';
        $service->shortname = 'local_su_statboard_api';
        $service->enabled = 1;
        $service->restrictedusers = 0;
        $service->downloadfiles = 0;
        $service->uploadfiles = 0;
        $service->timecreated = time();

        if ($existingservice) {
            $service->id = $existingservice->id;
            mtrace("Service: Reusing existing service (ID: {$service->id})");
        } else {
            $service->id = $DB->insert_record('external_services', $service);
            mtrace("Service: Created new service (ID: {$service->id})");
        }

        // 5. Add function to service.
        $functionname = 'local_su_statboard_api_get_statboard_stats';
        if (!$DB->record_exists('external_services_functions',
            ['externalserviceid' => $service->id, 'functionname' => $functionname])) {
            $servicefunction = new stdClass();
            $servicefunction->externalserviceid = $service->id;
            $servicefunction->functionname = $functionname;
            $DB->insert_record('external_services_functions', $servicefunction);
        }
        mtrace('Function: API function linked to service');

        // 6. Authorize user for service.
        if (!$DB->record_exists('external_services_users',
            ['externalserviceid' => $service->id, 'userid' => $user->id])) {
            $serviceuser = new stdClass();
            $serviceuser->externalserviceid = $service->id;
            $serviceuser->userid = $user->id;
            $serviceuser->timecreated = time();
            $DB->insert_record('external_services_users', $serviceuser);
        }
        mtrace('Authorization: User authorized for service');

        // 7. Configure settings
        set_config('token_validity_period', 365, 'local_su_statboard_api');
        set_config('token_no_expiration', '1', 'local_su_statboard_api');
        mtrace('Settings: Token configured as permanent');

        // 8. Generate token using the modern \core_external\util::generate_token() API.
        // Avoids including lib/externallib.php (which would conflict with PHPUnit isolation
        // requirements at install time).
        // Note: EXTERNAL_TOKEN_PERMANENT is defined in lib/externallib.php as value 0; we use
        // the literal 0 directly here to avoid triggering require_phpunit_isolation().
        $token = \core_external\util::generate_token(
            0, // EXTERNAL_TOKEN_PERMANENT.
            $service,
            $user->id,
            $systemcontext,
            0,
            ''
        );
        set_config('webservice_token', $token, 'local_su_statboard_api');
        mtrace('Token: Generated and stored (' . substr($token, 0, 8) . '...' . substr($token, -8) . ')');

        mtrace('Installation completed successfully');
        mtrace('Next steps: Enable web services and REST protocol in admin settings');

        return true;

    } catch (Exception $e) {
        mtrace('ERROR: Installation failed - ' . $e->getMessage());
        return false;
    }
}
