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
 * aichatbot backup steps
 *
 * @package    mod_aichatbot
 * @subpackage backup-moodle2
 * @copyright  2025 think modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the backup steps that will be used by the backup_aichatbot_activity_task
 */

/**
 * Define the complete aichatbot structure for backup, with file and id annotations
 */
class backup_aichatbot_activity_structure_step extends backup_activity_structure_step {
    /**
     * Define the structure of the aichatbot activity to be backed up.
     */
    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $aichatbot = new backup_nested_element(
            'aichatbot',
            ['id'],
            [
                'name',
                'timecreated',
                'timemodified',
                'intro',
                'introformat',
                'completionattempts',
                'completionshare',
                'prompttext',
                'channel',
                'attempts',
                'interactions',
            ],
        );

        // Define sources.
        $aichatbot->set_source_table('aichatbot', ['id' => backup::VAR_ACTIVITYID]);

        // Define file annotations.
        $aichatbot->annotate_files('mod_aichatbot', 'intro', null);

        // Return the root element (aichatbot), wrapped into standard activity structure.
        return $this->prepare_activity_structure($aichatbot);
    }
}
