<?php

use core\http_client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;

use core_table\local\filter\filter;
use core_table\local\filter\integer_filter;
use core_table\local\filter\string_filter;

function aichatbot_supports($feature) {

    switch($feature) {
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        // case FEATURE_MOD_ARCHETYPE:
        //     return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return true;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        default: return null;
    }
}

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

function aichatbot_add_instance($data, $mform) {
    global $DB;
    $data->id = $DB->insert_record('aichatbot', $data);
    return $data->id;
}

function aichatbot_delete_instance($id) {
    global $DB;

    if (!$aichatbot = $DB->get_record('aichatbot', ['id' => $id])) {
        return false;
    }

    // Delete the main instance.
    $DB->delete_records('aichatbot', ['id' => $id]);

    return true;
}

function aichatbot_update_instance($data, $mform) {
    global $DB;
    $data->id = $data->instance;
    $DB->update_record('aichatbot', $data);
    return true;
}

function mod_aichatbot_get_chat_view() {

    global $USER, $DB, $PAGE, $OUTPUT, $SESSION;

    $cmid = $PAGE->cm->id;

    if (mod_aichatbot_get_remaining_attempts() < 1) {
        $allCompleted = !$DB->record_exists('aichatbot_conversations', ['userid' => $USER->id, 'finished' => 0, 'instanceid' => $cmid]);

        $data = [
            'finishbuttondisabled' => true,
            'cmid' => $cmid,
        ];
        if($allCompleted) {
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

        if(mod_aichatbot_get_user_attempts($USER->id, $cmid) === $aichatbot->attempts) {
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

function mod_aichatbot_get_channels() {
    // get active ai provider
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

function mod_aichatbot_get_enabled_provider() {
    $plugins = \core_plugin_manager::instance()->get_plugins_of_type('aiprovider');
    foreach ($plugins as $plugin) {
        if ($plugin->is_enabled()) {
            return $plugin->name;
        }
    }
    return null;
}

function mod_aichatbot_get_user_attempts($userid, $cmid) {
    global $DB;

    $attempts = $DB->get_records('aichatbot_conversations', [
        'userid' => $userid,
        'instanceid' => $cmid
    ]);

    return count($attempts);
}

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

function mod_aichatbod_get_user_interactions($conversationid) {
    global $DB;

    $interactions = $DB->get_records('aichatbot_history', [
        'conversationid' => $conversationid
    ]);

    return count($interactions);
}

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
    $interactions = mod_aichatbod_get_user_interactions($conversationid);

    return $maxinteractions - $interactions;
}

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

function mod_aichatbot_get_current_conversation($cmid) {
    global $DB, $USER;

    return $DB->get_field('aichatbot_conversations', 'id', [
        'userid' => $USER->id,
        'instanceid' => $cmid,
        'finished' => 0
    ]);
}

function mod_aichatbot_get_conversation_history($conversationid) {
    global $DB;

    $conversationhistory = $DB->get_records('aichatbot_history', [
        'conversationid' => $conversationid
    ]);

    return $DB->get_records('aichatbot_history', [
        'conversationid' => $conversationid
    ]);
}

function mod_aichatbot_set_conversation_complete($cmid, $userid) {
    global $DB;

    $conversation = $DB->get_record('aichatbot_conversations', [
        'userid' => $userid,
        'instanceid' => $cmid,
        'finished' => 0
    ]);

    if ($conversation) {
        $conversation->finished = 1;
        $DB->update_record('aichatbot_conversations', $conversation);
        purge_caches();
    }
}

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
        "instance" => $cmid
    ];

    $context = context_course::instance($course->id);
    $roleid = 5; // student role
    $students = get_role_users($roleid, $context);

    if (!empty($firstnameinitial) || !empty($lastnameinitial)) {
        $filteredstudents = [];
        foreach ($students as $student) {
            $firstnameMatch = empty($firstnameinitial) || stripos($student->firstname, $firstnameinitial) === 0;
            $lastnameMatch = empty($lastnameinitial) || stripos($student->lastname, $lastnameinitial) === 0;

            if ($firstnameMatch && $lastnameMatch) {
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
                    'lastmodified' => userdate($conversation->updated), // Replace with actual value if needed
                    'userfullname' => $student->firstname . ' ' . $student->lastname,
                    'useremail' => $student->email,
                ];
                $data['conversations'][] = $conversationdata;

                $sharedfound = true;
                break; // Move to next student
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

function mod_aichatbot_get_user_conversations($cmid) {
    global $DB, $USER;

    $conversations = $DB->get_records('aichatbot_conversations', [
        'userid' => $USER->id,
        'instanceid' => $cmid,
        'finished' => 1,
    ]);

    return $conversations;
}

function mod_aichatbot_get_student_conversations($userid, $cmid) {
    global $DB;

    $conversations = $DB->get_records('aichatbot_conversations', [
        'userid' => $userid,
        'instanceid' => $cmid,
    ]);

    return $conversations;
}

function mod_aichatbot_share_conversation($conversationid, $userid, $cmid) {
    if (mod_aichatbot_user_has_shared_conversation($cmid)) {
        echo json_encode([
            'error' => get_string('alreadyshared', 'mod_aichatbot')
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

function mod_aichatbot_user_has_access_to_conversation($conversationid, $userid) {
    global $DB;

    $conversationisavailable = $DB->get_record('aichatbot_conversations', [
        'id' => $conversationid,
        'userid' => $userid,
    ]);

    return $conversationisavailable;
}

function mod_aichatbot_user_has_shared_conversation($cmid) {

    $conversation = mod_aichatbot_get_shared_conversation($cmid);

    if ($conversation) {
        return true;
    }
    return false;
}

function mod_aichatbot_get_shared_conversation($cmid) {
    global $DB, $USER;

    $conversation = $DB->get_record('aichatbot_conversations', [
        'userid' => $USER->id,
        'isshared' => 1,
        'instanceid' => $cmid,
    ]);

    return $conversation;
}

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

function mod_aichatbot_revoke_share($conversationid) {
    global $DB;

    $conversation = $DB->get_record('aichatbot_conversations', [
        'id' => $conversationid
    ]);

    if ($conversation) {
        $conversation->isshared = 0;
        $DB->update_record('aichatbot_conversations', $conversation);
    }

    return get_string('nosubmission', 'mod_aichatbot');
}

function mod_aichatbot_get_comment($conversationid) {
    global $DB;

    $conversation = $DB->get_record('aichatbot_conversations', [
        'id' => $conversationid
    ]);

    if ($conversation) {
        return $conversation->comment;
    }

    return '';
}

function mod_aichatbot_save_comment($conversationid, $comment) {
    global $DB;

    $conversation = $DB->get_record('aichatbot_conversations', [
        'id' => $conversationid
    ]);

    if ($conversation) {
        $conversation->comment = $comment;
        $DB->update_record('aichatbot_conversations', $conversation);
    }
}

function mod_aichatbot_no_access() {
    global $OUTPUT;

    echo $OUTPUT->header();
    echo $OUTPUT->render_from_template('mod_aichatbot/noaccess', []);
    echo $OUTPUT->footer();
    return;
}

function mod_aichatbot_show_public_dialogs($cmid) {
    global $OUTPUT;

    $publicdialogs = mod_aichatbot_get_public_dialogs($cmid);

    $data = [
        'conversations' => array_values(array_map(function($dialog) {
            return [
                'id' => $dialog->id,
                'userid' => $dialog->userid,
                'userfullname' => mod_aichatbot_get_username($dialog->userid),
            ];
        }, $publicdialogs))
    ];

    $data['cmid'] = $cmid;

    return $OUTPUT->render_from_template('mod_aichatbot/public_dialogs', $data);
}

function mod_aichatbot_get_public_dialogs($cmid) {
    global $DB;

    $conversations = $DB->get_records('aichatbot_conversations', [
        'instanceid' => $cmid,
        'ispublic' => 1
    ]);

    return $conversations;
}

function mod_aichatbot_get_username($id) {
    global $DB;

    $user = $DB->get_record('user', ['id' => $id]);
    if ($user) {
        return $user->firstname . ' ' . $user->lastname;
    }
    return '';
}

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

function mod_aichatbot_view($aichatbot, $course, $cm, $context) {
    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $aichatbot->id
    );

    $event = \mod_aichatbot\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('aichatbot', $aichatbot);
    $event->trigger();

    // Completion.
    $completion = new completion_info($course);
    $completion->set_module_viewed($cm);
}

function aichatbot_completion_rule_enabled($data) {
    return !empty($data->completionattempts);
}

function mod_aichatbot_remove_emojis($text) {
    // Remove emoji sequences (ZWJ, variation selectors, etc.)
    $text = preg_replace('/\x{200D}|\x{FE0F}/u', '', $text);

    // Remove keycap emojis like 1️⃣ 2️⃣ 3️⃣
    $text = preg_replace('/[0-9]\x{20E3}/u', '', $text);

    // Remove emojis from extended Unicode ranges
    $text = preg_replace('/[\x{1F000}-\x{1FFFF}]/u', '', $text);
    $text = preg_replace('/[\x{2100}-\x{27BF}]/u', '', $text); // Symbols, Dingbats

    return $text;
}