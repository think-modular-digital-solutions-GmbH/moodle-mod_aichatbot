<?php

use core_ai\manager;
use core_ai\aiactions\generate_text;
use core_ai\aiactions\responses\response_generate_text;

define('AJAX_SCRIPT', true);

require(__DIR__.'/../../config.php');
require_once(__DIR__ . '/lib.php');

$action       = optional_param('action', '', PARAM_ALPHANUM);
$prompttext   = optional_param('prompttext', '', PARAM_TEXT);
$contextid    = optional_param('contextid', '', PARAM_INT);
$cmid         = optional_param('cmid', '', PARAM_INT);
$conversationid = optional_param('conversationid', '', PARAM_INT);
$comment = optional_param('comment', '', PARAM_TEXT);

//require_login();

if (!confirm_sesskey()) {
    throw new moodle_exception('invalidsesskey', 'error');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    throw new moodle_exception('invalidrequest', 'error', '', null, 'Only POST requests are allowed.');
}

switch($action) {
    case 'sendrequest':
        if (mod_aichatbot_get_remaining_interactions($cmid) < 0) {
            echo json_encode([
                'error' => get_string('noattemptsremaining', 'mod_aichatbot')
            ]);
        } else {
            $manager = new manager();

            //Checks if there is any conversation history. If not, concatenate the user input with the system prompt.
            $finalprompt = mod_aichatbot_prepare_prompt($prompttext, $cmid);

            $action = new generate_text(
                contextid: $contextid,
                userid: $USER->id,
                prompttext: $finalprompt,
            );

            $response = $manager->process_action($action);
            $responsetext = $response->get_response_data();

            mod_aichatbot_log_conversation($prompttext, $responsetext['generatedcontent'], $cmid);
            $responsetext['remaininginteractions'] = mod_aichatbot_get_remaining_interactions($cmid);
            $responsetext['remainingattempts'] = mod_aichatbot_get_remaining_attempts($cmid);
            if ($responsetext['remaininginteractions'] < 1) {
                mod_aichatbot_set_conversation_complete($cmid, $USER->id);
            }
            print_r(json_encode($responsetext));
        }
        break;
    case 'confirmfinish':
        mod_aichatbot_set_conversation_complete($cmid, $USER->id);
        break;
    case 'shareconversation':
        mod_aichatbot_share_conversation($conversationid, $USER->id, $cmid);
        break;
    case 'togglepublic':
        echo mod_aichatbot_toggle_conversation_public($conversationid, $USER->id);
        break;
    case 'revokeshare':
        echo mod_aichatbot_revoke_share($conversationid);
        break;
    case 'getcomment':
        echo mod_aichatbot_get_comment($conversationid);
        break;
    case 'savecomment':
        mod_aichatbot_save_comment($conversationid, $comment);
        break;
    default:
        throw new moodle_exception('invalidaction', 'error');
        break;
}
