<?php
require_once('vendor/autoload.php'); // Make sure the path to mPDF autoload.php is correct
require_once('../../config.php'); // Moodle config
require_once(__DIR__ . '/lib.php');

use Mpdf\Mpdf;

global $DB, $USER, $PAGE;

$action = required_param('action', PARAM_ALPHANUM);
$conversationid = required_param('cid', PARAM_INT);
$cmid = required_param('cmid', PARAM_INT); // Course module ID.

require_login();
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/mod/aichatbot/view.php', ['cid' => $conversationid, 'action' => $action]);
$context = context_module::instance($cmid);

$conversation = $DB->get_record('aichatbot_conversations', [
    'id' => $conversationid
]);

if($conversation->ispublic) {
    mod_aichatbot_show_conversation($conversation, $conversationid, $action);
} else if(mod_aichatbot_user_has_access_to_conversation($conversationid, $USER->id)) {
    mod_aichatbot_show_conversation($conversation, $conversationid, $action);
} else if(has_capability('mod/aichatbot:manage', $context)) {
    if($conversation->isshared) {
        mod_aichatbot_show_conversation($conversation, $conversationid, $action);
    } else {
        mod_aichatbot_no_access();
    }
} else {
    mod_aichatbot_no_access();
}

function mod_aichatbot_show_conversation($conversation, $conversationid, $action) {
    global $DB;

    // we have to get the user becuase the teachers should see the name of the user who submitted the dialog in the document
    $userid = $conversation->userid;
    $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);

    $conversationhistory = $DB->get_records('aichatbot_history', [
        'conversationid' => $conversationid
    ]);

    // Create new PDF document
    $pdf = new Mpdf([
        'tempDir' => '/tmp'  // Make sure this path is correct
    ]);

    $pdf->SetTitle('Conversation');
    $username = fullname($user);
    $switch = true;

    // HTML content for the conversation
    $html = '<div style="max-width: 600px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px; font-family: Lucida Console; border: 1px solid #dde2eb;">';

    foreach ($conversationhistory as $c) {
        $timestamp = userdate($c->timestamp, '%d %b %Y, %H:%M'); // Format the timestamp

        if ($switch) {
            $html .= '<h5 style="text-align: center; margin-bottom: 30px;">Dialog submitted by ' . $username . ' ' . $timestamp . '</h5>';
            $switch = false;
        }

        if (!empty($c->request)) {
            $message = nl2br(htmlspecialchars($c->request));
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
            $html .= <<<EOD
            <span style="font-size: 8pt; color: gray;">Bot</span>
            <div style="background-color: #dde2eb; padding: 10px; margin: 10px 0; border-radius: 0 8px 8px 8px; align-self: flex-end; max-width: 80%;">
                $message
            </div>
    EOD;
        }
    }

    // Close the main div
    $html .= '</div>';

    // Write HTML to the PDF
    $pdf->WriteHTML($html);

    if ($action == 'preview') {
        $pdf->Output('conversation.pdf', 'I');
    } else if ($action == 'download') {
        $pdf->Output('conversation.pdf', 'D');
    }
}