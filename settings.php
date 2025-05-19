<?php
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext(
        'mod_aichatbot/maxattempts',
        get_string('maxattempts', 'mod_aichatbot'),
        get_string('maxattempts_desc', 'mod_aichatbot'),
        5,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configtext(
        'mod_aichatbot/maxinteractions',
        get_string('maxinteractions', 'mod_aichatbot'),
        get_string('maxinteractions_desc', 'mod_aichatbot'),
        10,
        PARAM_INT
    ));
}