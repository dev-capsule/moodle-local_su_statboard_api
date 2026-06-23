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
 * JavaScript for token management page.
 *
 * @module      local_su_statboard_api/token_manager
 * @copyright   2025 Sorbonne Université
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define([], function() {

    /**
     * Initialize token manager functionality.
     *
     */
    var init = function() {
        // Copy token button functionality.
        var copyBtn = document.getElementById('copy-token-btn');
        if (copyBtn) {
            copyBtn.addEventListener('click', function() {
                var tokenInput = document.getElementById('api-token');
                tokenInput.select();
                document.execCommand('copy');
                var message = document.getElementById('copy-message');
                message.style.display = 'inline';
                setTimeout(function() {
                    message.style.display = 'none';
                }, 2000);
            });
        }

        // Toggle token edit mode.
        var checkbox = document.getElementById('token_no_expiration');
        var dateGroup = document.getElementById('expiration_date_group');

        if (checkbox && dateGroup) {
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    dateGroup.style.display = 'none';
                } else {
                    dateGroup.style.display = '';
                }
            });
        }
    };

    return {
        init: init
    };
});
