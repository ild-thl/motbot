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
 * Advice to take part in a feedback survey.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\retention\advice;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/motbot/locallib.php');

/**
 * Advice to take part in a feedback survey.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class feedback extends \mod_motbot\retention\advice\title_and_actionrow {
    /**
     * Returns a lang_string object representing the name for the indicator or target.
     *
     * Used as column identificator.
     *
     * If there is a corresponding '_help' string this will be shown as well.
     *
     * @return \lang_string
     */
    public static function get_name(): \lang_string {
        return new \lang_string('advice:feedback', 'motbot');
    }

    /**
     * Constructor.

     * @param \core\user $user
     * @param \core\course $course
     * @return void
     */
    public function __construct($user, $course) {
        global $DB, $CFG;

        // Stop initialization, if $course is unset.
        if (!$course) {
            throw new \moodle_exception('No course given.');
        }

        // Stop initialization if user already took part in a feedback
        // or if ther is no feedback option available.
        if (mod_motbot_has_completed_feedback($user->id, $course->id)) {
            throw new \moodle_exception('Feedback already given.');
        }

        $sql = 'SELECT cm.id as id, f.name as name
            FROM mdl_course_modules cm
            JOIN mdl_modules m ON m.id = cm.module
            JOIN mdl_feedback f ON f.id = cm.instance
            WHERE cm.course = :courseid
            AND m.name = "feedback";';
        $activities = $DB->get_records_sql($sql, array('courseid' => $course->id));

        $this->title = \get_string('advice:feedback_title', 'motbot');

        if (!$activities || empty($activities)) {
            throw new \moodle_exception('No feedback activity available.');
        }

        foreach ($activities as $feedback) {
            $this->actions[] = [
                'action_url' => $CFG->wwwroot . '/mod/feedback/view.php?id=' . $feedback->id,
                'action' => \get_string('motbot:goto', 'motbot', $feedback->name),
            ];
        }
    }
}
