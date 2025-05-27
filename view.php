<?php

require_once('../../config.php');
global $DB, $PAGE, $OUTPUT, $CFG;

$id = required_param('id', PARAM_INT); // Course module ID.
list($course, $cm) = get_course_and_cm_from_cmid($id, 'aichatbot');
$aichatbot = $DB->get_record('aichatbot', ['id' => $cm->instance], '*', MUST_EXIST);
require_login($course, true, $cm);
$context = context_module::instance($cm->id);

require_capability('mod/aichatbot:view', $context);


$PAGE->set_url('/mod/aichatbot/view.php', ['id' => $id]);
$PAGE->requires->css('/mod/aichatbot/style.css');
$PAGE->requires->jquery();
$PAGE->requires->js('/mod/aichatbot/js/scripts.js');
$PAGE->requires->strings_for_js(['sharedsuccess', 'publicsuccess', 'privatesuccess', 'commentupdated', 'warningfinished'], 'mod_aichatbot');

mod_aichatbot_view($aichatbot, $course, $cm, $context);
$completion = new \completion_info($course);
$completion->update_state($cm, true);

echo $OUTPUT->header();
echo mod_aichatbot_get_chat_view();
echo $OUTPUT->footer();
