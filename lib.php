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
 * Library of functions and constants for module aichatbot
 *
 * @package    mod_aichatbot
 * @copyright  2025 think modular <support@think-modular.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\http_client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use core_table\local\filter\filter;
use core_table\local\filter\integer_filter;
use core_table\local\filter\string_filter;

/**
 * Adds an aichatbot instance
 *
 * This is done by calling the add_instance() method of the aichatbot type class
 * @param stdClass $data
 * @param mod_assign_mod_form $form
 * @return int The instance id of the new aichatbot
 */
function aichatbot_add_instance($data, $mform) {
    global $DB;
    $data->id = $DB->insert_record('aichatbot', $data);
    return $data->id;
}

/**
 * Update an aichatbot instance
 *
 * This is done by calling the update_instance() method of the aichatbot type class
 * @param stdClass $data
 * @param stdClass $form - unused
 * @return object
 */
function aichatbot_update_instance($data, $mform) {
    global $DB;
    $data->id = $data->instance;
    $DB->update_record('aichatbot', $data);
    return true;
}

/**
 * delete an aichatbot instance
 * @param int $id
 * @return bool
 */
function aichatbot_delete_instance($id) {
    global $DB;

    if (!$aichatbot = $DB->get_record('aichatbot', ['id' => $id])) {
        return false;
    }

    // Delete the main instance.
    $DB->delete_records('aichatbot', ['id' => $id]);

    return true;
}

/**
 * Return the list if Moodle features this module supports
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know or string for the module purpose.
 */
function aichatbot_supports($feature) {
    switch ($feature) {
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_MOD_PURPOSE:
            return MOD_PURPOSE_ASSESSMENT;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        default:
            return null;
    }
}

/**
 * Add a get_coursemodule_info function in case any aichatbot type wants to add 'extra' information
 * for the course (see resource).
 *
 * Given a course_module object, this function returns any "extra" information that may be needed
 * when printing this activity in a course listing.  See get_array_of_activities() in course/lib.php.
 *
 * @param stdClass $coursemodule The coursemodule object (record).
 * @return cached_cm_info An object on information that the courses
 *                        will know about (most noticeably, an icon).
 */
function aichatbot_get_coursemodule_info($coursemodule) {
    global $DB;

    $dbparams = ['id' => $coursemodule->instance];
    $fields = 'id, name, intro, introformat, completionattempts, completionshare';
    if (!$aichatbot = $DB->get_record('aichatbot', $dbparams, $fields)) {
        return false;
    }

    $result = new cached_cm_info();

    $result->name = $aichatbot->name;

    if ($coursemodule->showdescription) {
        // Convert intro to html. Do not filter cached version, filters run at display time.
        $result->content = format_module_intro('aichatbot', $aichatbot, $coursemodule->id, false);
    }

    // Populate the custom completion rules as key => value pairs, but only if the completion mode is 'automatic'.
    if ($coursemodule->completion == COMPLETION_TRACKING_AUTOMATIC) {
        $result->customdata['customcompletionrules']['completionattempts'] = $aichatbot->completionattempts;
        $result->customdata['customcompletionrules']['completionshare'] = $aichatbot->completionshare;
    }

    return $result;
}

/**
 * Checks if the completion rule for attempts is enabled.
 *
 * @param stdClass $data The data object containing completion settings.
 * @return bool True if the completion rule is enabled, false otherwise.
 */
function aichatbot_completion_rule_enabled($data) {
    return !empty($data->completionattempts);
}
