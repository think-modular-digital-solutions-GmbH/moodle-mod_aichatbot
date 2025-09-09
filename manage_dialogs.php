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
 * @package    mod_aichatbot
 * @copyright  2025 think modular <support@think-modular.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

// Get parameters
$cmid = required_param('id', PARAM_INT); // Course module ID.
list($course, $cm) = get_course_and_cm_from_cmid($cmid, 'aichatbot');

// Get context
require_login($course, true, $cm);
$context = context_module::instance($cmid);

// Setup page
$PAGE->set_url('/mod/aichatbot/manage_dialogs.php', ['id' => $cmid]);
$PAGE->set_title(get_string('manage_dialogs', 'mod_aichatbot'));
$PAGE->set_heading($course->fullname);
$PAGE->set_context($context);

// Include CSS and JS
$PAGE->requires->css('/mod/aichatbot/style.css');
$PAGE->requires->jquery();
$PAGE->requires->js('/mod/aichatbot/js/scripts.js');
$PAGE->requires->js('/mod/aichatbot/js/pagination.js');
$PAGE->requires->strings_for_js(['sharedsuccess', 'publicsuccess', 'privatesuccess', 'commentupdated', 'warningfinished'], 'mod_aichatbot');

echo $OUTPUT->header();

// Teacher view
if (has_capability('mod/aichatbot:manage', $context)) {
    echo $OUTPUT->heading(get_string('teachersection', 'mod_aichatbot'), 3);
    echo mod_aichatbot_get_manage_dialogs_teacher_view($cmid);
}

// Student view
if (has_capability('mod/aichatbot:view', $context)) {
    echo $OUTPUT->heading(get_string('studentsection', 'mod_aichatbot'), 3);
    echo mod_aichatbot_get_manage_dialogs_student_view($cmid);
}

$completion = new \completion_info($course);
$completion->update_state($cm, true);
echo $OUTPUT->footer();