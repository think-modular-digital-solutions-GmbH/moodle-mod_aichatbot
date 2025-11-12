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

require_once('../../config.php');
require_once($CFG->libdir . '/pdflib.php');

defined('MOODLE_INTERNAL') || die();

use mod_aichatbot\aichatbot;

global $DB, $USER, $PAGE;

// Get parameters.
$action = required_param('action', PARAM_ALPHANUM);
$conversationid = required_param('cid', PARAM_INT);
$cmid = required_param('cmid', PARAM_INT); // Course module ID.

// Permissions and page setup.
require_login();
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/mod/aichatbot/view.php', ['cid' => $conversationid, 'action' => $action]);
$context = context_module::instance($cmid);

// Get activity name and description.
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
    $pdf = new pdf();
    $pdf->SetTitle('Conversation');
    $username = fullname($user);
    $pdf->SetAuthor($username);
    $pdf->SetFont('freesans', '', 12);
    $header = true;

    // Add styles and start main div.
    // Because Moodle's PDF library uses TCPDF which has limited CSS support, we have to use inline styles and tables for layout.
    $html = '<style>
        h2.activityname {
            margin-bottom: 0;
            color:rgb(56, 56, 56);
        }
        .description {
            margin-bottom: 20px
        }
        .spacer {
            margin-bottom: 10px;
        }
        .submittedby h5{
            text-align: center;
            margin-bottom: 30px;
        }
        .username {
            text-align: right;
            font-size: 8pt;
            color: gray;
            margin-top: 10px;
        }
        .botname {
            text-align: left;
            font-size: 8pt;
            color: gray;
            margin-top: 10px;
        }
        .chat-text {
            margin: 10px;
        }
    </style>';

    foreach ($conversationhistory as $c) {
        $timestamp = userdate($c->timestamp, '%d %b %Y, %H:%M');

        if ($header) {
            $html .= '<h2 class="activityname">' . aichatbot::remove_emojis($activityname) . '</h2>';
            if (!empty($description)) {
                $html .= '<div class="description">' . aichatbot::remove_emojis($description) . '</div>';
            }
            $html .= '<div class="submittedby">';
            $html .= '<h5>' . get_string('submittedby', 'mod_aichatbot') . $username . ' ' . $timestamp . '</h5>';
            $html .= '<hr><div class="spacer">&nbsp;</div>';
            $header = false;
        }

        if (!empty($c->request)) {
            $message = nl2br(htmlspecialchars($c->request));
            $message = aichatbot::remove_emojis($message);
            $html .= '<div class="username">' . $username . '</div>';

            // Outer table gives left margin.
            $html .= '
            <table cellpadding="0" cellspacing="0" border="0" width="100%">
            <tr>
                <td width="10%">&nbsp;</td>
                <td width="90%">
                <table cellpadding="8" cellspacing="0" border="0" width="100%" bgcolor="#0078FF">
                    <tr>
                    <td style="color:white;">' . $message . '</td>
                    </tr>
                </table>
                </td>
            </tr>
            </table>';
            $html .= '<div class="spacer">&nbsp;</div>';
        }

        if (!empty($c->response)) {
            $message = nl2br(htmlspecialchars($c->response));
            $message = aichatbot::remove_emojis($message);
            $html .= '<div class="botname">Bot</div>';

            // Outer table gives right margin.
            $html .= '
            <table cellpadding="0" cellspacing="0" border="0" width="100%">
            <tr>
                <td width="90%">
                <table cellpadding="8" cellspacing="0" border="0" width="100%" bgcolor="#dde2eb">
                    <tr>
                    <td style="color:black;">' . $message . '</td>
                    </tr>
                </table>
                </td>
                <td width="10%">&nbsp;</td>
            </tr>
            </table>';
            $html .= '<div class="spacer">&nbsp;</div>';
        }
    }

    // Write HTML to the PDF.
    $pdf->AddPage();
    $pdf->WriteHTML($html);

    // Output the PDF.
    $filename = $activityname . '_conversation_' . $conversationid . '.pdf';
    if ($action == 'preview') {
        $pdf->Output($filename, 'I');
    } else if ($action == 'download') {
        $pdf->Output($filename, 'D');
    }
}
