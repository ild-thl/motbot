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
 * Advice that recommends to post to a negelected diccusion.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\retention\advice;

defined('MOODLE_INTERNAL') || die();

/**
 * Advice that recommends to post to a negelected diccusion.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class recommended_discussion extends \mod_motbot\retention\advice\forum_quote {
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
        return new \lang_string('advice:recommended_discussion', 'motbot');
    }

    /**
     * Constructor.

     * @param \core\user $user
     * @param \core\course $course
     * @return void
     */
    public function __construct($user, $course) {
        global $CFG, $DB;

        // Stop initialization, if $course is unset.
        if (!$course) {
            throw new \moodle_exception('No course given.');
        }

        $sql = "SELECT d.id, d.course, d.userid, d.firstpost, pp.subject, pp.message, MIN(pp.created) as timecreated, p.replycount
            FROM mdl_forum_discussions d
            JOIN (SELECT rd.id, COUNT(rp.id) as replycount
                  FROM mdl_forum_discussions rd
                  LEFT JOIN mdl_forum_posts rp
                  ON rp.discussion = rd.id AND rp.id != rd.firstpost
                  GROUP BY rd.id) p
            ON d.id = p.id
            JOIN (SELECT rd.id, rp.subject, rp.message, rp.created
                  FROM mdl_forum_discussions rd
                  LEFT JOIN mdl_forum_posts rp
                  ON rp.discussion = rd.id AND rp.id = rd.firstpost) pp
            ON d.id = pp.id
            WHERE d.course = :course
            AND p.replycount = 0";
        $neglected_discussion = $DB->get_record_sql($sql, array('course' => $course->id), IGNORE_MISSING);
        if ($neglected_discussion && $neglected_discussion->id) {
            $author = $DB->get_record('user', array('id' => $neglected_discussion->userid), 'firstname, lastname', IGNORE_MISSING);
            $this->title = \get_string('advice:recommendeddiscussion_title', 'motbot');
            $this->subject = $neglected_discussion->subject;
            $this->message = $neglected_discussion->message;
            $this->author = $author->firstname . ' ' . $author->lastname;
            $this->date = userdate($neglected_discussion->timecreated);
            $this->action_url = $CFG->wwwroot . '/mod/forum/discuss.php?d=' . $neglected_discussion->id;
            $this->action =  \get_string('advice:recommendeddiscussion_action', 'motbot');
        } else {
            throw new \moodle_exception('No recommended discussion.');
        }
    }
}
