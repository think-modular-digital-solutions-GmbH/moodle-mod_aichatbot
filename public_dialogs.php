<?php
require_once('../../config.php');
global $DB, $PAGE, $OUTPUT;
$cmid = required_param('cmid', PARAM_INT);
list($course, $cm) = get_course_and_cm_from_cmid($cmid, 'aichatbot');
require_login($course, true, $cm);

$PAGE->set_url('/mod/aichatbot/public_dialogs.php', ['cmid' => $cmid]);
$PAGE->requires->css('/mod/aichatbot/style.css');
$PAGE->requires->jquery();
$PAGE->requires->js('/mod/aichatbot/js/pagination.js');

echo $OUTPUT->header();
echo mod_aichatbot_show_public_dialogs($cmid);
echo $OUTPUT->footer();