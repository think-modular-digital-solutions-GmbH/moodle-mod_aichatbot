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
 * Form for mod_aichatbot settings.
 *
 * @package    mod_aichatbot
 * @copyright  2025 think modular <support@think-modular.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

use mod_aichatbot\aichatbot;

/**
 * Mod settings form.
 *
 * @package   mod_aichatbot
 * @copyright 2025 think modular <support@think-modular.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_aichatbot_mod_form extends moodleform_mod {
    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {

        $mform = $this->_form;

        // General settings.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Name.
        $mform->addElement(
            'text',
            'name',
            get_string('name'),
            ['size' => '64']
        );
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();

        // AI chatbot settings.
        $mform->addElement(
            'header',
            'aichatbotsettings',
            get_string('pluginname', 'mod_aichatbot')
        );
        $mform->addElement(
            'textarea',
            'prompttext',
            get_string('prompttext', 'mod_aichatbot'),
            'wrap="virtual" rows="10" cols="80"'
        );
        $channelsarray = aichatbot::get_channels();
        if (count($channelsarray)) {
            $mform->addElement('select', 'channel', get_string('channel', 'mod_aichatbot'), $channelsarray);
            $firstoption = count($channelsarray) ? reset($channelsarray) : null;
            $mform->setDefault('channel', $firstoption);
            $mform->addHelpButton('channel', 'channel', 'mod_aichatbot');
        }

        // Add a field for the number of attempts with a maximum limit from admin settings.
        $maxattempts = get_config('mod_aichatbot', 'maxattempts'); // Fetch the max attempts from admin settings.
        $mform->addElement(
            'text',
            'attempts',
            get_string('attempts', 'mod_aichatbot'),
            ['size' => 2]
        );
        $mform->setType('attempts', PARAM_INT);
        $mform->addRule('attempts', null, 'required', null, 'client');
        $mform->setDefault('attempts', $maxattempts); // Default value for attempts.
        $mform->addHelpButton('attempts', 'attempts', 'mod_aichatbot');

        // Add a field for the maximum number of interactions.
        $maxinteractions = get_config('mod_aichatbot', 'maxinteractions'); // Fetch the max interactions from admin settings.
        $mform->addElement(
            'text',
            'interactions',
            get_string('interactions', 'mod_aichatbot'),
            ['size' => 2]
        );
        $mform->setType('interactions', PARAM_INT);
        $mform->addRule('interactions', null, 'required', null, 'client');
        $mform->setDefault('interactions', $maxinteractions); // Default value for interactions.
        $mform->addHelpButton('interactions', 'interactions', 'mod_aichatbot');

        $this->standard_grading_coursemodule_elements();
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Form validation.
     *
     * @param array $data The form data.
     * @param array $files The form files.
     * @return array An array of errors.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $maxattempts = get_config('mod_aichatbot', 'maxattempts');
        $maxinteractions = get_config('mod_aichatbot', 'maxinteractions');

        // Validation for attempts.
        if (!is_numeric($data['attempts'])) {
            $errors['attempts'] = get_string('err_numeric', 'mod_aichatbot');
        } else if ($data['attempts'] < 1 || $data['attempts'] > $maxattempts) {
            $errors['attempts'] = get_string('err_maxattempts', 'mod_aichatbot', $maxattempts);
        }

        // Validation for interactions.
        if (!is_numeric($data['interactions'])) {
            $errors['interactions'] = get_string('err_numeric', 'mod_aichatbot');
        } else if ($data['interactions'] < 1 || $data['interactions'] > $maxinteractions) {
            $errors['interactions'] = get_string('err_maxattempts', 'mod_aichatbot', $maxinteractions);
        }

        // Validation for completion attempts.
        $completionattemptscountfield = $this->get_suffixed_name('completionattemptscount');
        $completionattemptsenabledfield = $this->get_suffixed_name('completionattempts');

        if (!empty($data[$completionattemptsenabledfield])) {
            $completionattemptscount = $data[$completionattemptscountfield] ?? null;
            if (!is_numeric($completionattemptscount) || $completionattemptscount < 1) {
                $errors[$completionattemptscountfield] = get_string('err_numeric', 'mod_aichatbot');
            } else if ($completionattemptscount > $data['attempts']) {
                $errors[$completionattemptscountfield] = get_string('err_maxattempts', 'mod_aichatbot', $data['attempts']);
            }
        }

        return $errors;
    }

    /**
     * Add custom completion rules.
     *
     * @return array The names of the fields added as completion rules.
     */
    public function add_completion_rules() {

        $mform =& $this->_form;

        // Completion rule: Sharing is required.
        $mform->addElement(
            'advcheckbox',
            $this->get_suffixed_name('completionshare'),
            '',
            get_string('completionshare', 'aichatbot')
        );

        // Completion rule: All attempts must be done.
        $mform->addElement(
            'advcheckbox',
            $this->get_suffixed_name('completionattempts'),
            '',
            get_string('completionattemptsenabled', 'aichatbot')
        );

        // Add a text field for entering the number of attempts, hidden if completionattempts is not checked.
        $mform->addElement(
            'text',
            $this->get_suffixed_name('completionattemptscount'),
            get_string('attempts', 'aichatbot'),
            ['size' => 2],
        );
        $mform->setType($this->get_suffixed_name('completionattemptscount'), PARAM_INT);
        $mform->addHelpButton(
            $this->get_suffixed_name('completionattemptscount'),
            'completionattemptscount',
            'aichatbot'
        );
        $mform->hideIf(
            $this->get_suffixed_name('completionattemptscount'),
            $this->get_suffixed_name('completionattempts'),
            'notchecked'
        );

        return [$this->get_suffixed_name('completionattempts'), $this->get_suffixed_name('completionshare')];
    }

    /**
     * Get the suffixed name for a field.
     *
     * @param string $fieldname The base field name.
     * @return string The suffixed field name.
     */
    protected function get_suffixed_name(string $fieldname): string {
        return $fieldname . $this->get_suffix();
    }

    /**
     * Check if completion rule is enabled.
     *
     * @param stdClass $data The form data.
     * @return bool True if the completion rule is enabled, false otherwise.
     */
    public function completion_rule_enabled($data) {
        $attemptsenabled = !empty($data[$this->get_suffixed_name('completionattempts')]);
        $attemptscountraw = $data[$this->get_suffixed_name('completionattemptscount')] ?? null;

        $attemptscount = is_numeric($attemptscountraw) ? (int) $attemptscountraw : null;

        return (
            ($attemptsenabled && $attemptscount !== null && $attemptscount > 0) ||
            !empty($data[$this->get_suffixed_name('completionshare')])
        );
    }
}
