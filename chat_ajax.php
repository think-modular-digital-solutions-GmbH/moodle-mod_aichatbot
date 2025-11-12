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
use moodle_exception;
use mod_aichatbot\aichatbot;

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
        if (aichatbot::get_remaining_interactions($cmid) < 0) {
            echo json_encode([
                'error' => get_string('noattemptsremaining', 'mod_aichatbot'),
            ]);
        } else {
            $manager = new manager();

            // Checks if there is any conversation history. If not, concatenate the user input with the system prompt.
            $finalprompt = aichatbot::prepare_prompt($prompttext, $cmid);

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
                $responsedata = $response->get_response_data();

                // Log.
                aichatbot::log_conversation($prompttext, $responsedata['generatedcontent'], $cmid);

                // Reduce remaining interactions/attempts.
                $responsedata['remaininginteractions'] = aichatbot::get_remaining_interactions($cmid);
                $responsedata['remainingattempts'] = aichatbot::get_remaining_attempts($cmid);
                if ($responsedata['remaininginteractions'] < 1) {
                    aichatbot::set_conversation_complete($cmid, $USER->id);
                }

                echo json_encode($responsedata, JSON_PRETTY_PRINT);
            } else {
                // Error.
                $error = $response->get_errormessage();
                echo json_encode(['error' => $error], JSON_PRETTY_PRINT);
            }
        }
        break;
    case 'confirmfinish':
        aichatbot::set_conversation_complete($cmid, $USER->id);
        break;
    case 'shareconversation':
        aichatbot::share_conversation($conversationid, $USER->id, $cmid);
        break;
    case 'togglepublic':
        echo aichatbot::toggle_conversation_public($conversationid, $USER->id);
        break;
    case 'revokeshare':
        echo aichatbot::revoke_share($conversationid);
        break;
    case 'getcomment':
        echo aichatbot::get_comment($conversationid);
        break;
    case 'savecomment':
        aichatbot::save_comment($conversationid, $comment);
        break;
    default:
        throw new moodle_exception('invalidaction', 'error');
        break;
}
