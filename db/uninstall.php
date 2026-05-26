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
 * Uninstallation script for local_su_statboard_api.
 *
 * @package    local_su_statboard_api
 * @copyright  2025 Sorbonne Université
 * @copyright  2025 Victor Da Silva Caseiro <victor.da_silva_caseiro@sorbonne-universite.fr>
 * @copyright  2025 Thomas Naudin <thomas.naudin@sorbonne-universite.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Function to perform plugin uninstallation.
 *
 * @return bool True on success, false on failure.
 */
function xmldb_local_su_statboard_api_uninstall() {
    global $DB;

    mtrace('SU Statboard API: Starting uninstallation...');

    try {
        // 1. Get configuration and identify services.
        $token = get_config('local_su_statboard_api', 'webservice_token');
        $services = $DB->get_records_sql(
            "SELECT * FROM {external_services}
             WHERE shortname = ? OR name LIKE ?",
            ['local_su_statboard_api', '%SU Statboard API%']
        );

        if (!empty($token)) {
            mtrace('Configuration: Found token ' . substr($token, 0, 8) . '...' . substr($token, -8));
        }

        if (empty($services)) {
            mtrace('Services: No services found to remove');
        } else {
            mtrace('Services: Found ' . count($services) . ' service(s) to remove');
        }

        // 2. Remove services and associated data.
        $totaltokens = 0;
        $totalfunctions = 0;
        $totalusers = 0;

        foreach ($services as $service) {
            // Remove tokens.
            $tokens = $DB->get_records('external_tokens', ['externalserviceid' => $service->id]);
            foreach ($tokens as $tokenrecord) {
                $DB->delete_records('external_tokens', ['id' => $tokenrecord->id]);
            }
            $totaltokens += count($tokens);

            // Remove service functions.
            $functions = $DB->get_records('external_services_functions', ['externalserviceid' => $service->id]);
            $DB->delete_records('external_services_functions', ['externalserviceid' => $service->id]);
            $totalfunctions += count($functions);

            // Remove service users.
            $serviceusers = $DB->get_records('external_services_users', ['externalserviceid' => $service->id]);
            $DB->delete_records('external_services_users', ['externalserviceid' => $service->id]);
            $totalusers += count($serviceusers);

            // Remove service.
            $DB->delete_records('external_services', ['id' => $service->id]);
        }
        if ($totaltokens > 0) {
            mtrace("Tokens: Removed $totaltokens token(s)");
        }
        if ($totalfunctions > 0) {
            mtrace("Functions: Removed $totalfunctions function(s)");
        }
        if ($totalusers > 0) {
            mtrace("Users: Removed $totalusers user authorization(s)");
        }
        if (!empty($services)) {
            mtrace('Services: All services removed');
        }
        // 3. Clean up tokens by value (safety check).
        if (!empty($token)) {
            $tokensbyvalue = $DB->get_records('external_tokens', ['token' => $token]);
            foreach ($tokensbyvalue as $tokenbyvalue) {
                $DB->delete_records('external_tokens', ['id' => $tokenbyvalue->id]);
            }
            if (!empty($tokensbyvalue)) {
                mtrace('Cleanup: Removed ' . count($tokensbyvalue) . ' additional token(s) by value');
            }
        }

        // 4. Clean up webservice users.
        $webserviceusers = $DB->get_records_select(
            'user',
            'username LIKE ? AND deleted = 0',
            ['webservice_statboard_%']
        );

        foreach ($webserviceusers as $wsuser) {
            // Remove role assignments.
            $roleassignments = $DB->get_records('role_assignments', ['userid' => $wsuser->id]);
            if (!empty($roleassignments)) {
                $DB->delete_records('role_assignments', ['userid' => $wsuser->id]);
            }

            // Mark user as deleted.
            $wsuser->deleted = 1;
            $wsuser->timemodified = time();
            $DB->update_record('user', $wsuser);
        }

        if (!empty($webserviceusers)) {
            mtrace('Users: Cleaned up ' . count($webserviceusers) . ' webservice user(s)');
        }

        // 5. Remove configuration.
        $configs = $DB->get_records('config_plugins', ['plugin' => 'local_su_statboard_api']);
        unset_all_config_for_plugin('local_su_statboard_api');

        if (!empty($configs)) {
            mtrace('Configuration: Removed ' . count($configs) . ' setting(s)');
        }

        mtrace('Uninstallation completed successfully');
        mtrace('All API access has been revoked and data cleaned');

        return true;

    } catch (Exception $e) {
        mtrace('ERROR: Uninstallation failed - ' . $e->getMessage());
        mtrace('Manual cleanup may be required');
        return false;
    }
}
