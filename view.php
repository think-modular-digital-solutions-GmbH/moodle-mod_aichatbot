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
 * Entry point to the aichatbot module. All pages are rendered from here
 *
 * @package    mod_aichatbot
 * @copyright  2025 think modular <support@think-modular.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

use mod_aichatbot\aichatbot;

global $DB, $PAGE, $OUTPUT, $CFG;

// Parameters.
$id = required_param('id', PARAM_INT); // Course module ID.
[$course, $cm] = get_course_and_cm_from_cmid($id, 'aichatbot');
$aichatbot = $DB->get_record('aichatbot', ['id' => $cm->instance], '*', MUST_EXIST);

// Permissions.
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/aichatbot:view', $context);

// Page setup.
$PAGE->set_url('/mod/aichatbot/view.php', ['id' => $id]);
$PAGE->requires->css('/mod/aichatbot/style.css');
$PAGE->requires->jquery();
$PAGE->requires->js('/mod/aichatbot/js/scripts.js');
$PAGE->requires->strings_for_js(
    [
        'sharedsuccess',
        'publicsuccess',
        'privatesuccess',
        'commentupdated',
        'warningfinished',
    ],
    'mod_aichatbot'
);

// Log the view.
aichatbot::log_view($aichatbot, $course, $cm, $context);

// Output.
echo $OUTPUT->header();
echo aichatbot::get_chat_view();
echo $OUTPUT->footer();
