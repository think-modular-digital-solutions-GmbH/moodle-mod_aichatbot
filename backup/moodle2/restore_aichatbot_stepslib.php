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
 * Define all the restore steps that will be used by the restore_aichatbot_activity_task
 *
 * @package    mod_aichatbot
 * @copyright  2025 think modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Structure step to restore one aichatbot activity
 */
class restore_aichatbot_activity_structure_step extends restore_activity_structure_step {
    /**
     * Define the structure of the aichatbot activity to be restored
     * @return restore_path_element[] array of path elements
     */
    protected function define_structure() {
        $paths = [];

        $paths[] = new restore_path_element('aichatbot', '/activity/aichatbot');

        // Return the paths wrapped into standard activity structure.
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Process aichatbot information
     * @param array $data information
     */
    protected function process_aichatbot($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        // Any changes to the list of dates that needs to be rolled should be same during course restore and course reset.
        // See MDL-9367.

        $newitemid = $DB->insert_record('aichatbot', $data);
        $this->apply_activity_instance($newitemid);
    }

    /**
     * After execute actions
     */
    protected function after_execute() {
        // Add aichatbot related files, no need to match by itemname (just internally handled context).
    }
}
