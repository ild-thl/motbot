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
     * @param $event
     * @return bool
     */
    public static function course_viewed($event) {
        global $DB;

        $context = \context_course::instance($event->courseid);

        $conditions_array = array(
            'recipient' => $event->userid,
            'contextid' => $context->id,
            'state' => \mod_motbot\retention\intervention::INTERVENED,
        );
        $records = $DB->get_records('motbot_intervention', $conditions_array);
        foreach($records as $record) {
            if(in_array($event->eventname, json_decode($record->desired_events))) {
                $intervention = \mod_motbot\retention\intervention::from_db($record);
                $intervention->set_state(\mod_motbot\retention\intervention::SUCCESSFUL);
            }
        }
    }

    /**
     * Event processor - discussion or post created
     *
     * @param $event
     * @return bool
     */
    public static function discussion_or_post_created($event) {
        global $DB;

        $cmid = $DB->get_record('context', array('id' => $event->contextid), 'instanceid', IGNORE_MISSING)->instanceid;
        $sql = 'SELECT c.id as cid FROM mdl_course_modules cm
            JOIN mdl_context c ON c.instanceid = cm.course
            WHERE cm.id = :cmid
            AND c.contextlevel = 50;';
        $contextid = $DB->get_record_sql($sql, array('cmid' => $cmid))->cid;

        $conditions_array = array(
            'recipient' => $event->userid,
            'contextid' => $contextid,
            'state' => \mod_motbot\retention\intervention::INTERVENED,
        );

        $records = $DB->get_records('motbot_intervention', $conditions_array);

        if(!$records || empty($records)) {
            return;
        }
        foreach($records as $record) {
            if(in_array($event->eventname, json_decode($record->desired_events))) {
                $intervention = \mod_motbot\retention\intervention::from_db($record);
                $intervention->set_state(\mod_motbot\retention\intervention::SUCCESSFUL);
            }
        }
    }
}