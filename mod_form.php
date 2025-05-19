<?php
require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_aichatbot_mod_form extends moodleform_mod {
    public function definition() {
       $channelsarray = mod_aichatbot_get_channels();

        $mform = $this->_form;
        $mform->addElement('text', 'name', get_string('name'));
        $mform->setType('name', PARAM_TEXT);

        $this->standard_intro_elements();

        $mform->addElement('header', 'aichatbotsettings', get_string('pluginname', 'mod_aichatbot'));
        $mform->addElement('textarea', 'prompttext', get_string('prompttext', 'mod_aichatbot'), 'wrap="virtual" rows="10" cols="80"');
        if (count($channelsarray)) {
            $mform->addElement('select', 'channel', get_string('channel', 'mod_aichatbot'), $channelsarray);
            $firstoption = count($channelsarray) ? reset($channelsarray) : null;
            $mform->setDefault('channel', $firstoption);
            $mform->addHelpButton('channel', 'channel', 'mod_aichatbot');
        }

        // Add a field for the number of attempts with a maximum limit from admin settings.
        $maxattempts = get_config('mod_aichatbot', 'maxattempts'); // Fetch the max attempts from admin settings.
        $mform->addElement('text', 'attempts', get_string('attempts', 'mod_aichatbot'), array('size' => 2));
        $mform->setType('attempts', PARAM_INT);
        $mform->addRule('attempts', null, 'required', null, 'client');
        $mform->setDefault('attempts', $maxattempts); // Default value for attempts.
        $mform->addHelpButton('attempts', 'attempts', 'mod_aichatbot');

        // Add a field for the maximum number of interactions.
        $maxinteractions = get_config('mod_aichatbot', 'maxinteractions'); // Fetch the max interactions from admin settings.
        $mform->addElement('text', 'interactions', get_string('interactions', 'mod_aichatbot'), array('size' => 2));
        $mform->setType('interactions', PARAM_INT);
        $mform->addRule('interactions', null, 'required', null, 'client');
        $mform->setDefault('interactions', $maxinteractions); // Default value for interactions.
        $mform->addHelpButton('interactions', 'interactions', 'mod_aichatbot');

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $maxattempts = get_config('mod_aichatbot', 'maxattempts');
        $maxinteractions = get_config('mod_aichatbot', 'maxinteractions');

        // Validation for attempts
        if (!is_numeric($data['attempts'])) {
            $errors['attempts'] = get_string('err_numeric', 'mod_aichatbot');
        } else if ($data['attempts'] < 1 || $data['attempts'] > $maxattempts) {
            $errors['attempts'] = get_string('err_maxattempts', 'mod_aichatbot', $maxattempts);
        }

        // Validation for interactions
        if (!is_numeric($data['interactions'])) {
            $errors['interactions'] = get_string('err_numeric', 'mod_aichatbot');
        } else if ($data['interactions'] < 1 || $data['interactions'] > $maxinteractions) {
            $errors['interactions'] = get_string('err_maxattempts', 'mod_aichatbot', $maxinteractions);
        }

        // Validation for completion attempts
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

    public function add_completion_rules() {

        $mform =& $this->_form;

        // Completion rule: Sharing is required
        $mform->addElement('advcheckbox', $this->get_suffixed_name('completionshare'), '', get_string('completionshare', 'aichatbot'));

        // Completion rule: All attempts must be done
        $mform->addElement('advcheckbox', $this->get_suffixed_name('completionattempts'), '', get_string('completionattemptsenabled', 'aichatbot'));

        // add a text field for entering the number of attempts, hidden if completionattempts is not checked
        $mform->addElement('text', $this->get_suffixed_name('completionattemptscount'), get_string('attempts', 'aichatbot'), array('size' => 2));
        $mform->setType($this->get_suffixed_name('completionattemptscount'), PARAM_INT);
        $mform->addHelpButton($this->get_suffixed_name('completionattemptscount'), 'completionattemptscount', 'aichatbot');
        $mform->hideIf($this->get_suffixed_name('completionattemptscount'), $this->get_suffixed_name('completionattempts'), 'notchecked');

        return [$this->get_suffixed_name('completionattempts'), $this->get_suffixed_name('completionshare')];
    }

    protected function get_suffixed_name(string $fieldname): string {
        return $fieldname . $this->get_suffix();
    }

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