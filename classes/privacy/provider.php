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
 * Privacy implementation for local_su_statboard_api.
 *
 * @package    local_su_statboard_api
 * @copyright  2025 Sorbonne Université
 * @copyright  2025 Victor Da Silva Caseiro <victor.da_silva_caseiro@sorbonne-universite.fr>
 * @copyright  2025 Thomas Naudin <thomas.naudin@sorbonne-universite.fr>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_su_statboard_api\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;

/**
 * Privacy provider implementation for local_su_statboard_api.
 */
class provider implements
    // This plugin does store personal user data.
    \core_privacy\local\metadata\provider,

    // This plugin can provide information about which users have data within it.
    \core_privacy\local\request\core_userlist_provider,

    // This plugin processes data in the system context.
    \core_privacy\local\request\plugin\provider {
    /**
     * Returns meta data about this system.
     *
     * @param collection $collection The collection to add metadata to.
     * @return collection The collection with added metadata.
     */
    public static function get_metadata(collection $collection): collection {
        // Add relevant metadata about data storage.
        $collection->add_database_table(
            'external_tokens',
            [
                'token' => 'privacy:metadata:external_tokens:token',
                'userid' => 'privacy:metadata:external_tokens:userid',
                'validuntil' => 'privacy:metadata:external_tokens:validuntil',
            ],
            'privacy:metadata:external_tokens'
        );

        // External service integration.
        $collection->add_external_location_link(
            'moodle_webservice',
            [
                'token' => 'privacy:metadata:moodle_webservice:token',
                'user_id' => 'privacy:metadata:moodle_webservice:user_id',
            ],
            'privacy:metadata:moodle_webservice'
        );

        // Log data.
        $collection->add_subsystem_link(
            'core_logging',
            [],
            'privacy:metadata:core_logging'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param int $userid The user to search.
     * @return contextlist The contextlist containing the list of contexts used by this plugin.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();

        // The only context associated with this plugin is the system context.
        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {external_tokens} et ON et.userid = ?
                 WHERE ctx.contextlevel = ?";

        $params = [
            $userid,
            CONTEXT_SYSTEM,
        ];

        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_system) {
            return;
        }

        $sql = "SELECT userid
                  FROM {external_tokens} et
                  JOIN {external_services} es ON es.id = et.externalserviceid
                 WHERE es.shortname = ?";

        $params = ['local_su_statboard_api'];

        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel != CONTEXT_SYSTEM) {
                continue;
            }

            // Export token information.
            $sql = "SELECT et.*
                      FROM {external_tokens} et
                      JOIN {external_services} es ON es.id = et.externalserviceid
                     WHERE et.userid = ? AND es.shortname = ?";

            $params = [$userid, 'local_su_statboard_api'];

            $tokens = $DB->get_records_sql($sql, $params);

            if (!empty($tokens)) {
                $tokendata = [];
                foreach ($tokens as $token) {
                    $tokendata[] = [
                        'token' => $token->token,
                        'created' => transform::datetime($token->timecreated),
                        'expires' => !empty($token->validuntil) ? transform::datetime($token->validuntil) : get_string('never'),
                    ];
                }

                writer::with_context($context)->export_data(
                    [get_string('pluginname', 'local_su_statboard_api'), get_string('tokens', 'webservice')],
                    (object)$tokendata
                );
            }
        }
    }

    /**
     * Delete all data for all users in the specified context.
     *
     * @param \context $context The specific context to delete data for.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;

        if ($context->contextlevel != CONTEXT_SYSTEM) {
            return;
        }

        // Delete all tokens associated with this service.
        $service = $DB->get_record('external_services', ['shortname' => 'local_su_statboard_api']);
        if ($service) {
            $DB->delete_records('external_tokens', ['externalserviceid' => $service->id]);
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $userid = $contextlist->get_user()->id;

        foreach ($contextlist as $context) {
            if ($context->contextlevel != CONTEXT_SYSTEM) {
                continue;
            }

            // Delete all tokens for this user associated with our service.
            $sql = "DELETE FROM {external_tokens}
                     WHERE userid = ?
                       AND externalserviceid IN (
                           SELECT id FROM {external_services} WHERE shortname = ?
                       )";

            $params = [$userid, 'local_su_statboard_api'];

            $DB->execute($sql, $params);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if (!$context instanceof \context_system) {
            return;
        }

        // Get the service ID.
        $service = $DB->get_record('external_services', ['shortname' => 'local_su_statboard_api']);
        if (!$service) {
            return;
        }

        [$userinsql, $userinparams] = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
        $params = array_merge(['serviceid' => $service->id], $userinparams);

        $select = "externalserviceid = :serviceid AND userid {$userinsql}";
        $DB->delete_records_select('external_tokens', $select, $params);
    }
}
