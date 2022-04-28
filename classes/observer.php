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
     * Event processor - course summary viewed.
     *
     * @param \core\event $event
     * @return void
     */
    public static function course_viewed($event) {
        global $USER, $DB;
        if ($event->courseid <= 1) {
            return;
        }
        $context = \context_course::instance($event->courseid);

        // Update intervention state.
        $success = self::update_intervention_success($event->userid, $context->id, $event->eventname);
        if ($success) {
            try {
                $course = $DB->get_record('course', array('id' => $event->courseid), '*', IGNORE_MISSING);
                $suggestion = new \mod_motbot\retention\advice\last_stop($USER, $course);
                \core\notification::success(get_string('reaction:' . \str_replace('\\', '', $event->eventname), 'motbot', $suggestion->render_html()));
            } catch (\moodle_exception $e) {
                print_r($e->getMessage());
            }
        }
    }

    /**
     * Event processor - discussion or post created.
     *
     * @param \core\event $event
     * @return void
     */
    public static function discussion_or_post_created($event) {
        global $DB;

        $cmid = $DB->get_record('context', array('id' => $event->contextid), 'instanceid', IGNORE_MISSING)->instanceid;
        $sql = 'SELECT c.id as cid FROM mdl_course_modules cm
            JOIN mdl_context c ON c.instanceid = cm.course
            WHERE cm.id = :cmid
            AND c.contextlevel = 50;';
        $contextid = $DB->get_record_sql($sql, array('cmid' => $cmid))->cid;

        $success = self::update_intervention_success($event->userid, $contextid, $event->eventname);
        if ($success) {
            try {
                \core\notification::success(get_string('reaction:' . \str_replace('\\', '', $event->eventname), 'motbot'));
            } catch (\moodle_exception $e) {
                print_r($e->getMessage());
            }
        }
    }

    /**
     * Event processor - user logged in.
     *
     * @param \core\event $event
     * @return void
     */
    public static function user_loggedin($event) {
        global $USER;
        // Update intervention state.
        $success = self::update_intervention_success($event->userid, null, $event->eventname);

        // Give positive feedback and suggestion in case of success.
        if ($success) {
            try {
                $suggestion = new \mod_motbot\retention\advice\last_stop($USER, null);
                \core\notification::success(get_string('reaction:' . \str_replace('\\', '', $event->eventname), 'motbot', $suggestion->render_html()));
            } catch (\moodle_exception $e) {
                print_r($e->getMessage());
            }
        }
    }

    /**
     * Set all interventions as successful, that have listed this event as desired.
     *
     * @param int $recipient
     * @param int $contextid
     * @param string $eventname
     * @return bool
     */
    private static function update_intervention_success($recipient, $contextid, $eventname) {
        global $DB;

        $conditionsarray = array(
            'recipient' => $recipient,
            'state' => \mod_motbot\retention\intervention::INTERVENED,
        );

        if ($contextid) {
            $conditionsarray['contextid'] = $contextid;
        }

        $records = $DB->get_records('motbot_intervention', $conditionsarray);
        $success = false;
        foreach ($records as $record) {
            if (in_array($eventname, json_decode($record->desired_events))) {
                $success = $eventname;
                $intervention = \mod_motbot\retention\intervention::from_db($record);
                $intervention->set_state(\mod_motbot\retention\intervention::SUCCESSFUL);
            }
        }

        return $success;
    }
}
