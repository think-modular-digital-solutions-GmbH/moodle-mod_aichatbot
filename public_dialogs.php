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
 * Public dialogs page.
 *
 * @package    mod_aichatbot
 * @copyright  2025 think modular <support@think-modular.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');

global $DB, $PAGE, $OUTPUT;

$cmid = required_param('cmid', PARAM_INT);
[$course, $cm] = get_course_and_cm_from_cmid($cmid, 'aichatbot');
require_login($course, true, $cm);

$PAGE->set_url('/mod/aichatbot/public_dialogs.php', ['cmid' => $cmid]);
$PAGE->requires->css('/mod/aichatbot/style.css');
$PAGE->requires->jquery();
$PAGE->requires->js('/mod/aichatbot/js/pagination.js');
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

echo $OUTPUT->header();
echo mod_aichatbot_show_public_dialogs($cmid);
echo $OUTPUT->footer();
