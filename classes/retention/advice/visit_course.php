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
 * Advice - Visit course.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\retention\advice;

defined('MOODLE_INTERNAL') || die();

/**
 * Advice - Visit course.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class visit_course extends \mod_motbot\retention\advice\title_and_action {
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
        return new \lang_string('advice:visit_course', 'motbot');
    }

    /**
     * Contstructor.

     * @param \core\user $user
     * @param \core\course $course
     * @return void
     */
    public function __construct($user, $course) {
        global $CFG;
        $this->user = $user;
        $this->course = $course;

        // Stop initialization, if $course is unset.
        if (!$this->course) {
            throw new \moodle_exception('No course given.');
        }

        $this->title = (new \lang_string('advice:visitcourse_title', 'motbot'))->out($this->user->lang);
        $this->action_url = $CFG->wwwroot . '/course/view.php?id=' . $this->course->id;
        $this->action = (new \lang_string('motbot:goto', 'motbot', $this->course->shortname))->out($this->user->lang);
    }
}
