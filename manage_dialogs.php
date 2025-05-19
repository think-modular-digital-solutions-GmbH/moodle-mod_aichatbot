<?php

require_once('../../config.php');
global $DB, $PAGE, $OUTPUT, $CFG;

$cmid = required_param('id', PARAM_INT); // Course module ID.
list($course, $cm) = get_course_and_cm_from_cmid($cmid, 'aichatbot');
require_login($course, true, $cm);

$PAGE->set_url('/mod/aichatbot/manage_dialogs.php', ['id' => $cmid]);
$PAGE->requires->css('/mod/aichatbot/style.css');
$PAGE->requires->jquery();
$PAGE->requires->js('/mod/aichatbot/js/scripts.js');
$PAGE->requires->js('/mod/aichatbot/js/pagination.js');

echo $OUTPUT->header();
$context = context_module::instance($cmid);
if (has_capability('mod/aichatbot:manage', $context)) {
    echo mod_aichatbot_get_manage_dialogs_teacher_view($cmid);
} else {
    echo mod_aichatbot_get_manage_dialogs_student_view($cmid);
}
$completion = new \completion_info($course);
$completion->update_state($cm, true);
echo $OUTPUT->footer();