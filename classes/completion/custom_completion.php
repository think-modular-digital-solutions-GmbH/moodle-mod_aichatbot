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
 * Custom completion rules for the aichatbot activity.
 *
 * @package    mod_aichatbot
 * @copyright  2025 think modular <support@think-modular.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

declare(strict_types=1);

namespace mod_aichatbot\completion;

use core_completion\activity_custom_completion;

/**
 * Activity custom completion subclass for the aichatbot activity.
 *
 * @package   mod_aichatbot
 * @copyright
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_completion extends activity_custom_completion {
    /**
     * Fetches the completion state for a given completion rule.
     *
     * @param string $rule The completion rule.
     * @return int The completion state.
     */
    public function get_state(string $rule): int {
        global $DB;

        $this->validate_rule($rule);

        $userid = $this->userid;
        $cm = $this->cm;
        $aichatbotid = $cm->instance;
        $aichatbot = $DB->get_record('aichatbot', ['id' => $aichatbotid], '*', MUST_EXIST);
        $completionattemptscount = $aichatbot->completionattemptscount ?? null;

        switch ($rule) {
            case 'completionattempts':
                // Example: check if the user made at least one attempt.
                $attempts = $DB->count_records('aichatbot_conversations', [
                    'userid' => $userid,
                    'finished' => 1,
                    'instanceid' => $cm->id,
                ]);
                return (!empty($completionattemptscount) && ($attempts >= (int)$completionattemptscount))
                    ? COMPLETION_COMPLETE
                    : COMPLETION_INCOMPLETE;
            case 'completionshare':
                // Check if the user has any conversation where isshared is set to 1.
                $shared = $DB->record_exists('aichatbot_conversations', [
                    'userid' => $userid,
                    'isshared' => 1,
                    'instanceid' => $cm->id,
                ]);
                return $shared ? COMPLETION_COMPLETE : COMPLETION_INCOMPLETE;
            default:
                return COMPLETION_INCOMPLETE;
        }
    }

    /**
     * Defines the custom completion rules available in this module.
     *
     * @return array
     */
    public static function get_defined_custom_rules(): array {
        return ['completionattempts', 'completionshare'];
    }

    /**
     * Provides descriptions of the custom completion rules.
     *
     * @return array
     */
    public function get_custom_rule_descriptions(): array {
        global $DB;

        $aichatbot = $DB->get_record('aichatbot', ['id' => $this->cm->instance], '*', MUST_EXIST);

        return [
            'completionattempts' => get_string(
                'completiondetail:attempts',
                'aichatbot',
                ['attempts' => $aichatbot->completionattemptscount]
            ),
            'completionshare' => get_string('completiondetail:share', 'aichatbot'),
        ];
    }

    /**
     * Specifies the display order of all completion rules.
     *
     * @return array
     */
    public function get_sort_order(): array {
        return [
            'completionview',
            'completionattempts',
            'completionshare',
        ];
    }
}
