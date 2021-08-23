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
 * Event observers.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot;

defined('MOODLE_INTERNAL') || die();

/**
 * Event observers.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {

    /**
     * Event processor - course summary viewed
     *
     * @param \core\event\course_information_viewed $event
     * @return bool
     */
    public static function course_viewed(\core\event\course_viewed $event) {
        global $DB;

        $context = \context_course::instance($event->courseid);

        $conditions_array = array(
            'recipient' => $event->userid,
            'contextid' => $context->id,
            'state' => \mod_motbot\retention\intervention::INTERVENED,
        );

        $records = $DB->get_records('motbot_intervention', $conditions_array);
        foreach($records as $record) {
            print_r($record);
            if(in_array($event->name, $record->desired_events)) {
                $intervention = \mod_motbot\retention\intervention::from_db($record);
                $intervention->set_state(\mod_motbot\retention\intervention::SUCCESSFUL);
            }
        }
    }
}