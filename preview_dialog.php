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
 * Preview or download a conversation as a PDF
 *
 * @package    mod_aichatbot
 * @copyright  2025 think modular <support@think-modular.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once('vendor/autoload.php');
require_once('../../config.php');
require_once(__DIR__ . '/lib.php');

use Mpdf\Mpdf;
use mod_aichatbot\aichatbot;

global $DB, $USER, $PAGE;

$action = required_param('action', PARAM_ALPHANUM);
$conversationid = required_param('cid', PARAM_INT);
$cmid = required_param('cmid', PARAM_INT); // Course module ID.

require_login();
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/mod/aichatbot/view.php', ['cid' => $conversationid, 'action' => $action]);
$context = context_module::instance($cmid);

$cm = get_coursemodule_from_id('aichatbot', $cmid, 0, false, MUST_EXIST);
$aichatbot = $DB->get_record('aichatbot', ['id' => $cm->instance], '*', MUST_EXIST);
$description = $aichatbot->intro;
$activityname = $aichatbot->name;


$conversation = $DB->get_record('aichatbot_conversations', [
    'id' => $conversationid,
]);

if ($conversation->ispublic) {
    mod_aichatbot_show_conversation($conversation, $conversationid, $action, $activityname, $description);
} else if (aichatbot::user_has_access_to_conversation($conversationid, $USER->id)) {
    mod_aichatbot_show_conversation($conversation, $conversationid, $action, $activityname, $description);
} else if (has_capability('mod/aichatbot:manage', $context)) {
    if ($conversation->isshared) {
        mod_aichatbot_show_conversation($conversation, $conversationid, $action, $activityname, $description);
    } else {
        aichatbot::no_access();
    }
} else {
    aichatbot::no_access();
}

/**
 * Generate and show/download the conversation as a PDF
 *
 * @param stdClass $conversation The conversation record
 * @param int $conversationid The ID of the conversation
 * @param string $action 'preview' or 'download'
 * @param string $activityname The name of the activity
 * @param string $description The description of the activity
 * @return void
 *
 * @package mod_aichatbot
 */
function mod_aichatbot_show_conversation($conversation, $conversationid, $action, $activityname = '', $description = '') {
    global $DB;

    // We have to get the user becuase the teachers should see the name of the user who submitted the dialog in the document.
    $userid = $conversation->userid;
    $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

    $conversationhistory = $DB->get_records('aichatbot_history', [
        'conversationid' => $conversationid,
    ]);

    // Create new PDF document.
    $pdf = new Mpdf([
        'tempDir' => '/tmp',
    ]);

    $pdf->SetTitle('Conversation');
    $username = fullname($user);
    $switch = true;

    foreach ($conversationhistory as $c) {
        $timestamp = userdate($c->timestamp, '%d %b %Y, %H:%M');

        if ($switch) {
            $html = '<h2 style="margin-bottom: 0px; font-family: Lucida Console; color:rgb(56, 56, 56);">' . aichatbot::remove_emojis($activityname) . '</h2>';
            if (!empty($description)) {
                $html .= '<div style="margin-bottom: 20px; font-family: Lucida Console;">' . aichatbot::remove_emojis($description) . '</div>';
            }
            $html .= '<div style="max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px; font-family: Lucida Console; border: 1px solid #dde2eb;">';
            $html .= '<h5 style="text-align: center; margin-bottom: 30px;">' . get_string('submittedby', 'mod_aichatbot') . $username . ' ' . $timestamp . '</h5>';
            $switch = false;
        }

        if (!empty($c->request)) {
            $message = nl2br(htmlspecialchars($c->request));
            $message = aichatbot::remove_emojis($message);
            $html .= <<<EOD
            <div style="text-align: right;">
                <span style="font-size: 8pt; color: gray;">$username</span>
            </div>
            <div style="background-color: #0078FF; color: white; padding: 10px; margin: 10px 0 20px 0; border-radius: 8px 0 8px 8px; align-self: flex-start; max-width: 80%; text-align: right;">
                $message
            </div>
    EOD;
        }

        if (!empty($c->response)) {
            $message = nl2br(htmlspecialchars($c->response));
            $message = aichatbot::remove_emojis($message);
            $html .= <<<EOD
            <span style="font-size: 8pt; color: gray;">Bot</span>
            <div style="background-color: #dde2eb; padding: 10px; margin: 10px 0; border-radius: 0 8px 8px 8px; align-self: flex-end; max-width: 80%;">
                $message
            </div>
    EOD;
        }
    }

    // Close the main div.
    $html .= '</div>';

    // Write HTML to the PDF.
    $pdf->WriteHTML($html);

    if ($action == 'preview') {
        $pdf->Output('conversation.pdf', 'I');
    } else if ($action == 'download') {
        $pdf->Output('conversation.pdf', 'D');
    }
}
