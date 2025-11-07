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
 * Adds an assignment instance
 *
 * This is done by calling the add_instance() method of the assignment type class
 * @param stdClass $data
 * @param mod_assign_mod_form $form
 * @return int The instance id of the new assignment
 */
function aichatbot_add_instance($data, $mform) {
    global $DB;
    $data->id = $DB->insert_record('aichatbot', $data);
    return $data->id;
}

/**
 * delete an assignment instance
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
 * Add a get_coursemodule_info function in case any assignment type wants to add 'extra' information
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
 * Update an assignment instance
 *
 * This is done by calling the update_instance() method of the assignment type class
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
 * Renders the chat view for the AI chatbot module.
 *
 * @return string HTML output for the chat interface.
 */
function mod_aichatbot_get_chat_view() {

    global $USER, $DB, $PAGE, $OUTPUT, $SESSION;

    $cmid = $PAGE->cm->id;

    if (mod_aichatbot_get_remaining_attempts() < 1) {
        $alldone = !$DB->record_exists('aichatbot_conversations', ['userid' => $USER->id, 'finished' => 0, 'instanceid' => $cmid]);

        $data = [
            'finishbuttondisabled' => true,
            'cmid' => $cmid,
        ];
        if ($alldone) {
            return $OUTPUT->render_from_template('mod_aichatbot/noattempts', $data);
        }
    }

    $conversation = $DB->get_record('aichatbot_conversations', [
        'userid' => $USER->id,
        'instanceid' => $cmid,
        'finished' => 0,
    ]);

    if (!$conversation) {
        $cm = get_coursemodule_from_id('aichatbot', $cmid, 0, false, MUST_EXIST);
        $aichatbot = $DB->get_record('aichatbot', ['id' => $cm->instance], '*', MUST_EXIST);

        if (mod_aichatbot_get_user_attempts($USER->id, $cmid) === $aichatbot->attempts) {
            return $OUTPUT->render_from_template('mod_aichatbot/noattempts', $data);
        } else {
            $conversation = new stdClass();
            $conversation->userid = $USER->id;
            $conversation->instanceid = $cmid;
            $conversation->finished = 0;
            $conversation->isshared = 0;
            $conversation->ispublic = 0;
            $conversation->id = $DB->insert_record('aichatbot_conversations', $conversation);
        }
    }
    $SESSION->aichatbot_conversationid = $conversation->id;

    $conversationid = $conversation->id;

    $conversationhistory = mod_aichatbot_get_conversation_history($conversationid);
    $thread = [];
    foreach ($conversationhistory as $history) {
        array_push($thread, [
            'isuser' => true,
            'content' => $history->request,
            'timestamp' => userdate($history->timestamp),
        ]);
        array_push($thread, [
            'isuser' => false,
            'content' => $history->response,
            'timestamp' => userdate($history->timestamp),
        ]);
    }

    $context = [
        'attemptsremaining' => mod_aichatbot_get_remaining_attempts(),
        'interactionsremaining' => mod_aichatbot_get_remaining_interactions(),
        'history' => $thread,
        'cmid' => $cmid,
    ];

    return $OUTPUT->render_from_template('mod_aichatbot/view', $context);
}

/**
 * Returns the list of channels available for the AI chatbot.
 *
 * @return array An associative array of channel IDs and names.
 */
function mod_aichatbot_get_channels() {

    // Get active ai provider.
    $pluginname = mod_aichatbot_get_enabled_provider();
    $classname = "aiprovider_{$pluginname}\provider";
    $classobj = new $classname();
    if (method_exists($classobj, 'get_channels')) {
        $result = $classobj->get_channels();

        $options = [];

        foreach ($result as $channel) {
            $options[$channel->id] = $channel->name;
        }

        return $options;
    }

    return [];
}

/**
 * Returns the name of the enabled AI provider.
 *
 * @return string|null The name of the enabled provider or null if none is enabled.
 */
function mod_aichatbot_get_enabled_provider() {
    $plugins = \core_plugin_manager::instance()->get_plugins_of_type('aiprovider');
    foreach ($plugins as $plugin) {
        if ($plugin->is_enabled()) {
            return $plugin->name;
        }
    }
    return null;
}

/**
 * Gets the number of attempts a user has made in a specific AI chatbot instance.
 *
 * @param int $userid The ID of the user.
 * @return int The number of attempts made by the user.
 */
function mod_aichatbot_get_user_attempts($userid, $cmid) {
    global $DB;

    $attempts = $DB->get_records('aichatbot_conversations', [
        'userid' => $userid,
        'instanceid' => $cmid,
    ]);

    return count($attempts);
}

/**
 * Gets the number of remaining attempts for a user in a specific AI chatbot instance.
 *
 * @param int $userid The ID of the user.
 * @return int The number of attempts remaining for the user.
 */
function mod_aichatbot_get_remaining_attempts($cmid = null) {
    global $USER, $DB, $PAGE, $OUTPUT;

    if ($cmid) {
        $cm = get_coursemodule_from_id('aichatbot', $cmid, 0, false, MUST_EXIST);
    }

    if (!$cmid) {
        $cm = $PAGE->cm;
        $cmid = $cm->id;
    }

    $aichatbot = $DB->get_record('aichatbot', ['id' => $cm->instance], '*', MUST_EXIST);
    $attemptsallowed = $aichatbot->attempts;
    $attempts = mod_aichatbot_get_user_attempts($USER->id, $cmid);

    return $attemptsallowed - $attempts;
}

/**
 * Gets the number of interactions a user has made in a specific AI chatbot conversation.
 *
 * @param int $conversationid The ID of the conversation.
 * @return int The number of interactions made by the user in the conversation.
 */
function mod_aichatbot_get_user_interactions($conversationid) {
    global $DB;

    $interactions = $DB->get_records('aichatbot_history', [
        'conversationid' => $conversationid,
    ]);

    return count($interactions);
}

/**
 * Gets the number of remaining interactions for a user in a specific AI chatbot conversation.
 *
 * @param int $cmid The course module ID.
 * @return int The number of interactions remaining for the user in the conversation.
 */
function mod_aichatbot_get_remaining_interactions($cmid = null) {
    global $USER, $DB, $PAGE, $OUTPUT;

    if ($cmid) {
        $cm = get_coursemodule_from_id('aichatbot', $cmid, 0, false, MUST_EXIST);
    }

    if (!$cmid) {
        $cm = $PAGE->cm;
        $cmid = $cm->id;
    }

    $aichatbot = $DB->get_record('aichatbot', ['id' => $cm->instance], '*', MUST_EXIST);
    $maxinteractions = $aichatbot->interactions;
    $conversationid = mod_aichatbot_get_current_conversation($cmid);
    $interactions = mod_aichatbot_get_user_interactions($conversationid);

    return $maxinteractions - $interactions;
}

/**
 * Logs a conversation request and response in the AI chatbot history.
 *
 * @param string $request The user's request to the AI chatbot.
 * @param string $response The AI chatbot's response.
 * @param int $cmid The course module ID.
 */
function mod_aichatbot_log_conversation($request, $response, $cmid) {
    global $DB, $USER;

    $log = new stdClass();
    $log->conversationid = mod_aichatbot_get_current_conversation($cmid);
    $log->provider = mod_aichatbot_get_enabled_provider();
    $log->request = $request;
    $log->response = $response;
    $log->timestamp = time();

    $DB->insert_record('aichatbot_history', $log);
}

/**
 * Gets the current conversation ID for the user in a specific AI chatbot course module.
 *
 * @param int $cmid The course module ID.
 * @return int|null The ID of the current conversation or null if not found.
 */
function mod_aichatbot_get_current_conversation($cmid) {
    global $DB, $USER;

    return $DB->get_field('aichatbot_conversations', 'id', [
        'userid' => $USER->id,
        'instanceid' => $cmid,
        'finished' => 0,
    ]);
}

/**
 * Gets the conversation history for a specific conversation ID.
 *
 * @param int $conversationid The ID of the conversation.
 * @return array An array of conversation history records.
 */
function mod_aichatbot_get_conversation_history($conversationid) {
    global $DB;

    $conversationhistory = $DB->get_records('aichatbot_history', [
        'conversationid' => $conversationid,
    ]);

    return $DB->get_records('aichatbot_history', [
        'conversationid' => $conversationid,
    ]);
}

/**
 * Sets a conversation as complete for a user in a specific AI chatbot course module.
 *
 * @param int $cmid The course module ID.
 * @param int $userid The ID of the user.
 */
function mod_aichatbot_set_conversation_complete($cmid, $userid) {
    global $DB;

    $conversation = $DB->get_record('aichatbot_conversations', [
        'userid' => $userid,
        'instanceid' => $cmid,
        'finished' => 0,
    ]);

    if ($conversation) {
        $conversation->finished = 1;
        $DB->update_record('aichatbot_conversations', $conversation);
        purge_caches();
    }
}

/**
 * Gets the manage dialogs view for students in the AI chatbot module.
 *
 * @param int $cmid The course module ID.
 * @return string HTML output for the manage dialogs view.
 */
function mod_aichatbot_get_manage_dialogs_student_view($cmid) {
    global $OUTPUT;

    $conversations = mod_aichatbot_get_user_conversations($cmid);

    $data = [];
    $counter = 1;
    $userhasshared = mod_aichatbot_user_has_shared_conversation($cmid);
    if ($userhasshared) {
        $sharedconversationid = mod_aichatbot_get_shared_conversation($cmid)->id;
        $data['sharedconversation'] = $sharedconversationid;
    }

    foreach ($conversations as $conversation) {
        $conversationdata = [
            'id' => $conversation->id,
            'finished' => $conversation->finished,
            'isshared' => $conversation->isshared,
            'ispublic' => $conversation->ispublic,
            'comment' => $conversation->comment,
            'counter' => $counter,
        ];

        if ($userhasshared && $conversation->id == $sharedconversationid) {
            $conversationdata['issharedconversation'] = true;
        }

        $data['conversations'][] = $conversationdata;
        $counter++;
    }
    $data['remainingattempts'] = mod_aichatbot_get_remaining_attempts($cmid);
    $data['cmid'] = $cmid;
    $data['hasshared'] = mod_aichatbot_user_has_shared_conversation($cmid);

    return $OUTPUT->render_from_template('mod_aichatbot/manage_dialogs_student', $data);
}

/**
 * Gets the manage dialogs view for teachers in the AI chatbot module.
 *
 * @param int $cmid The course module ID.
 * @return string HTML output for the manage dialogs view.
 */
function mod_aichatbot_get_manage_dialogs_teacher_view($cmid) {
    global $OUTPUT, $PAGE, $DB;

    $firstnameinitial = optional_param('tifirst', '', PARAM_ALPHA);
    $lastnameinitial = optional_param('tilast', '', PARAM_ALPHA);

    $cm = get_coursemodule_from_id('aichatbot', $cmid);
    $course = get_course($cm->course);
    $context = context_course::instance($course->id, MUST_EXIST);

    $additionalparams = ['id' => $cmid];
    $initialselector = new \core_course\output\actionbar\initials_selector(
        $course,
        'mod/aichatbot/manage_dialogs.php',
        $firstnameinitial,
        $lastnameinitial,
        'tifirst',
        'tilast',
        $additionalparams
    );

    $data = [
        "label" => "Example searchable combobox",
        "name" => "input-1",
        "value" => "0",
        "renderlater" => false,
        "usebutton" => true,
        "buttoncontent" => get_string('initalsdropdown', 'mod_aichatbot'),
        "dropdowncontent" => $initialselector->export_for_template($OUTPUT)['dropdowncontent'],
        "instance" => $cmid,
    ];

    $context = context_course::instance($course->id);
    $roleid = 5; // Student role.
    $students = get_role_users($roleid, $context);

    if (!empty($firstnameinitial) || !empty($lastnameinitial)) {
        $filteredstudents = [];
        foreach ($students as $student) {
            $firstnamematch = empty($firstnameinitial) || stripos($student->firstname, $firstnameinitial) === 0;
            $lastnamematch = empty($lastnameinitial) || stripos($student->lastname, $lastnameinitial) === 0;

            if ($firstnamematch && $lastnamematch) {
                $filteredstudents[] = $student;
            }
        }
        $students = $filteredstudents;
    }

    foreach ($students as $student) {
        $studentconversations = mod_aichatbot_get_student_conversations($student->id, $cmid);
        $sharedfound = false;

        foreach ($studentconversations as $conversation) {
            if ($conversation->isshared) {
                $conversationdata = [
                    'id' => $conversation->id,
                    'isshared' => $conversation->isshared,
                    'comment' => $conversation->comment,
                    'lastmodified' => userdate($conversation->updated),
                    'userfullname' => $student->firstname . ' ' . $student->lastname,
                    'useremail' => $student->email,
                ];
                $data['conversations'][] = $conversationdata;

                $sharedfound = true;
                break;
            }
        }

        if (!$sharedfound) {
            $conversationdata = [
                'userfullname' => $student->firstname . ' ' . $student->lastname,
                'useremail' => $student->email,
            ];
            $data['conversations'][] = $conversationdata;
        }
    }

    $data['cmid'] = $cmid;

    return $OUTPUT->render_from_template('mod_aichatbot/manage_dialogs_teacher', $data);
}

/**
 * Gets the user conversations for a specific AI chatbot course module.
 *
 * @param int $cmid The course module ID.
 * @return array An array of conversation records for the user.
 */
function mod_aichatbot_get_user_conversations($cmid) {
    global $DB, $USER;

    $conversations = $DB->get_records('aichatbot_conversations', [
        'userid' => $USER->id,
        'instanceid' => $cmid,
        'finished' => 1,
    ]);

    return $conversations;
}

/**
 * Gets the student conversations for a specific AI chatbot course module.
 *
 * @param int $userid The ID of the user.
 * @param int $cmid The course module ID.
 * @return array An array of conversation records for the user.
 */
function mod_aichatbot_get_student_conversations($userid, $cmid) {
    global $DB;

    $conversations = $DB->get_records('aichatbot_conversations', [
        'userid' => $userid,
        'instanceid' => $cmid,
    ]);

    return $conversations;
}

/**
 * Shares a conversation for a user in a specific AI chatbot course module.
 *
 * @param int $conversationid The ID of the conversation to share.
 * @param int $userid The ID of the user sharing the conversation.
 * @param int $cmid The course module ID.
 */
function mod_aichatbot_share_conversation($conversationid, $userid, $cmid) {
    if (mod_aichatbot_user_has_shared_conversation($cmid)) {
        echo json_encode([
            'error' => get_string('alreadyshared', 'mod_aichatbot'),
        ]);
        return;
    }
    if (mod_aichatbot_user_has_access_to_conversation($conversationid, $userid)) {
        global $DB;

        $conversation = $DB->get_record('aichatbot_conversations', [
            'id' => $conversationid,
            'userid' => $userid,
        ]);

        if ($conversation) {
            $conversation->isshared = 1;
            $conversation->updated = time();
            $DB->update_record('aichatbot_conversations', $conversation);
        }
    }
    purge_caches();
}

/**
 * Checks if a user has access to a specific conversation in the AI chatbot module.
 *
 * @param int $conversationid The ID of the conversation.
 * @param int $userid The ID of the user.
 * @return bool True if the user has access, false otherwise.
 */
function mod_aichatbot_user_has_access_to_conversation($conversationid, $userid) {
    global $DB;

    $conversationisavailable = $DB->get_record('aichatbot_conversations', [
        'id' => $conversationid,
        'userid' => $userid,
    ]);

    return $conversationisavailable;
}

/**
 * Checks if a user has shared a conversation in the AI chatbot module.
 *
 * @param int $cmid The course module ID.
 * @return bool True if the user has shared a conversation, false otherwise.
 */
function mod_aichatbot_user_has_shared_conversation($cmid) {

    $conversation = mod_aichatbot_get_shared_conversation($cmid);

    if ($conversation) {
        return true;
    }
    return false;
}

/**
 * Gets the shared conversation for a user in a specific AI chatbot course module.
 *
 * @param int $cmid The course module ID.
 * @return stdClass|null The shared conversation record or null if not found.
 */
function mod_aichatbot_get_shared_conversation($cmid) {
    global $DB, $USER;

    $conversation = $DB->get_record('aichatbot_conversations', [
        'userid' => $USER->id,
        'isshared' => 1,
        'instanceid' => $cmid,
    ]);

    return $conversation;
}

/**
 * Toggles the public visibility of a conversation for a user in the AI chatbot module.
 *
 * @param int $conversationid The ID of the conversation to toggle.
 * @param int $userid The ID of the user.
 * @return bool True if the conversation is now public, false otherwise.
 */
function mod_aichatbot_toggle_conversation_public($conversationid, $userid) {
    if (mod_aichatbot_user_has_access_to_conversation($conversationid, $userid)) {
        global $DB;
        $conversation = $DB->get_record('aichatbot_conversations', [
            'id' => $conversationid,
            'userid' => $userid,
        ]);

        if ($conversation) {
            $conversation->ispublic = $conversation->ispublic ? 0 : 1;
            $DB->update_record('aichatbot_conversations', $conversation);
        }

        return $conversation->ispublic;
    }
}

/**
 * Revokes the shared status of a conversation for a user in the AI chatbot module.
 *
 * @param int $conversationid The ID of the conversation to revoke sharing.
 * @return string A message indicating the result of the operation.
 */
function mod_aichatbot_revoke_share($conversationid) {
    global $DB;

    $conversation = $DB->get_record('aichatbot_conversations', [
        'id' => $conversationid,
    ]);

    if ($conversation) {
        $conversation->isshared = 0;
        $DB->update_record('aichatbot_conversations', $conversation);
    }

    return get_string('nosubmission', 'mod_aichatbot');
}

/**
 * Gets the comment for a specific conversation in the AI chatbot module.
 *
 * @param int $conversationid The ID of the conversation.
 * @return string The comment associated with the conversation.
 */
function mod_aichatbot_get_comment($conversationid) {
    global $DB;

    $conversation = $DB->get_record('aichatbot_conversations', [
        'id' => $conversationid,
    ]);

    if ($conversation) {
        return $conversation->comment;
    }

    return '';
}

/**
 * Saves a comment for a specific conversation in the AI chatbot module.
 *
 * @param int $conversationid The ID of the conversation.
 * @param string $comment The comment to save.
 */
function mod_aichatbot_save_comment($conversationid, $comment) {
    global $DB;

    $conversation = $DB->get_record('aichatbot_conversations', [
        'id' => $conversationid,
    ]);

    if ($conversation) {
        $conversation->comment = $comment;
        $DB->update_record('aichatbot_conversations', $conversation);
    }
}

/**
 * Displays a no access message for the AI chatbot module.
 *
 * This function is called when a user tries to access the AI chatbot without the necessary permissions.
 */
function mod_aichatbot_no_access() {
    global $OUTPUT;

    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('mod_aichatbot/noaccess', []);
    echo $OUTPUT->footer();
    return;
}

/**
 * Displays the public dialogs for the AI chatbot module.
 *
 * @param int $cmid The course module ID.
 * @return string HTML output for the public dialogs view.
 */
function mod_aichatbot_show_public_dialogs($cmid) {
    global $OUTPUT;

    $publicdialogs = mod_aichatbot_get_public_dialogs($cmid);

    $data = [
        'conversations' => array_values(array_map(function ($dialog) {
            return [
                'id' => $dialog->id,
                'userid' => $dialog->userid,
                'userfullname' => fullname(\core_user::get_user($dialog->userid)),
            ];
        }, $publicdialogs)),
    ];

    $data['cmid'] = $cmid;

    return $OUTPUT->render_from_template('mod_aichatbot/public_dialogs', $data);
}

/**
 * Gets the public dialogs for a specific AI chatbot course module.
 *
 * @param int $cmid The course module ID.
 * @return array An array of public conversation records.
 */
function mod_aichatbot_get_public_dialogs($cmid) {
    global $DB;

    $conversations = $DB->get_records('aichatbot_conversations', [
        'instanceid' => $cmid,
        'ispublic' => 1,
    ]);

    return $conversations;
}

/**
 * Prepares the prompt text for the AI chatbot module.
 *
 * This function appends the conversation history to the prompt text if available.
 *
 * @param string $prompttext The initial prompt text.
 * @param int $cmid The course module ID.
 * @return string The prepared prompt text with conversation history.
 */
function mod_aichatbot_prepare_prompt($prompttext, $cmid) {
    global $DB;

    $conversationid = mod_aichatbot_get_current_conversation($cmid);
    $conversationhistory = mod_aichatbot_get_conversation_history($conversationid);

    if (empty($conversationhistory)) {
        $cm = get_coursemodule_from_id('aichatbot', $cmid, 0, false, MUST_EXIST);
        $aichatbot = $DB->get_record('aichatbot', ['id' => $cm->instance], '*', MUST_EXIST);
        return $aichatbot->prompttext . "\n" . $prompttext;
    }

    return $prompttext;
}

/**
 * Triggers the course_module_viewed event for the AI chatbot module.
 *
 * This function is called when a user views an AI chatbot instance.
 *
 * @param stdClass $aichatbot The AI chatbot instance.
 * @param stdClass $course The course object.
 * @param stdClass $cm The course module object.
 * @param context $context The context of the course module.
 */
function mod_aichatbot_view($aichatbot, $course, $cm, $context) {
    // Trigger course_module_viewed event.
    $params = [
        'context' => $context,
        'objectid' => $aichatbot->id,
    ];

    $event = \mod_aichatbot\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('aichatbot', $aichatbot);
    $event->trigger();

    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
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

/**
 * Removes emojis from a given text.
 *
 * This function uses regular expressions to remove various types of emojis,
 * including keycap emojis and those from extended Unicode ranges.
 *
 * @param string $text The input text from which emojis will be removed.
 * @return string The text with emojis removed.
 */
function mod_aichatbot_remove_emojis($text) {
    // Remove emoji sequences (ZWJ, variation selectors, etc.).
    $text = preg_replace('/\x{200D}|\x{FE0F}/u', '', $text);

    // Remove keycap emojis like 1️⃣ 2️⃣ 3️⃣.
    $text = preg_replace('/[0-9]\x{20E3}/u', '', $text);

    // Remove emojis from extended Unicode ranges.
    $text = preg_replace('/[\x{1F000}-\x{1FFFF}]/u', '', $text);
    $text = preg_replace('/[\x{2100}-\x{27BF}]/u', '', $text);

    return $text;
}
