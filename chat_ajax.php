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
 * AJAX script to handle AI Chat Bot requests.
 *
 * @package    mod_aichatbot
 * @copyright  2025 think modular <support@think-modular.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_ai\manager;
use core_ai\aiactions\generate_text;
use core_ai\aiactions\responses\response_generate_text;

define('AJAX_SCRIPT', true);

require(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

$action       = optional_param('action', '', PARAM_ALPHANUM);
$prompttext   = optional_param('prompttext', '', PARAM_TEXT);
$contextid    = optional_param('contextid', '', PARAM_INT);
$cmid         = optional_param('cmid', '', PARAM_INT);
$conversationid = optional_param('conversationid', '', PARAM_INT);
$comment = optional_param('comment', '', PARAM_TEXT);

require_login();

if (!confirm_sesskey()) {
    throw new moodle_exception('invalidsesskey', 'error');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    throw new moodle_exception('invalidrequest', 'error', '', null, 'Only POST requests are allowed.');
}

switch ($action) {
    case 'sendrequest':
        if (mod_aichatbot_get_remaining_interactions($cmid) < 0) {
            echo json_encode([
                'error' => get_string('noattemptsremaining', 'mod_aichatbot'),
            ]);
        } else {
            $manager = new manager();

            // Checks if there is any conversation history. If not, concatenate the user input with the system prompt.
            $finalprompt = mod_aichatbot_prepare_prompt($prompttext, $cmid);

            $action = new generate_text(
                contextid: $contextid,
                userid: $USER->id,
                prompttext: $finalprompt,
            );

            // Get response.
            $response = $manager->process_action($action);
            $success = $response->get_success();

            // Success.
            if ($success) {
                $responsetext = $response->get_response_data();
                echo json_encode($responsetext, JSON_PRETTY_PRINT);

                // Log.
                mod_aichatbot_log_conversation($prompttext, $responsetext['generatedcontent'], $cmid);

                // Reduce remaining interactions/attempts.
                $responsetext['remaininginteractions'] = mod_aichatbot_get_remaining_interactions($cmid);
                $responsetext['remainingattempts'] = mod_aichatbot_get_remaining_attempts($cmid);
                if ($responsetext['remaininginteractions'] < 1) {
                    mod_aichatbot_set_conversation_complete($cmid, $USER->id);
                }
            } else {
                // Error.
                $error = $response->get_errormessage();
                echo json_encode(['error' => $error], JSON_PRETTY_PRINT);
            }
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
