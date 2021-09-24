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
 * Interaction.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\retention\advice;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot."/lib/completionlib.php");

class course_completion extends \mod_motbot\retention\advice\base {
    protected $title = null;
    protected $user_progress = null;
    protected $avg_progress = null;
    protected $desc = null;

    /**
    * Returns a lang_string object representing the name for the indicator or target.
    *
    * Used as column identificator.
    *
    * If there is a corresponding '_help' string this will be shown as well.
    *
    * @return \lang_string
    */
    public static function get_name() : \lang_string{
        return new \lang_string('advice:course_completion', 'motbot');
    }

    public function render() {
        $message = $this->title . PHP_EOL;
        $message .= PHP_EOL;
        $message .= 'Your progress: ' . PHP_EOL;
        $prog10 = round($this->user_progress * 10, 0);
        $prog100 = round($this->user_progress * 100, 0);

        $message .= " |";
        for($i = 0; $i < 10; $i++) {
            if($i < $prog10) {
                $message .= "=";
            } else {
                $message .= "  ";
            }
        }
        $message .= "| *" . $prog100 . '%*' . PHP_EOL;
        $message .= PHP_EOL;
        $message .= 'Average progress: ' . PHP_EOL;
        $avg_prog10 = round($this->avg_progress * 10, 0);
        $avg_prog100 = round($this->avg_progress * 100, 0);
        $message .= " |";
        for($i = 0; $i < 10; $i++) {
            if($i < $avg_prog10) {
                $message .= "=";
            } else {
                $message .= "  ";
            }
        }
        $message .= "| *" . $avg_prog100 . '%*' . PHP_EOL;
        $message .= PHP_EOL;
        $message .= $this->desc;
        return $message;
    }

    public function render_html() {
        global $OUTPUT;

        $context = [
            "title" => $this->title,
            "user_progress" => $this->user_progress*100,
            "avg_progress" => $this->avg_progress*100,
            "user_prog_less_or_equal" => $this->user_progress<=$this->avg_progress,
            "desc" => $this->desc
        ];

        return $OUTPUT->render_from_template('mod_motbot/course_completion', $context);
    }

    public function __construct($user, $course) {
        global $CFG, $DB;

        $usercount_sql = "SELECT
            COUNT(u.id) as count
            FROM mdl_role_assignments ra
            JOIN mdl_user u ON u.id = ra.userid
            JOIN mdl_role r ON r.id = ra.roleid
            JOIN mdl_context cxt ON cxt.id = ra.contextid
            JOIN mdl_course c ON c.id = cxt.instanceid
            WHERE ra.userid = u.id
            AND ra.contextid = cxt.id
            AND cxt.contextlevel =50
            AND cxt.instanceid = c.id
            AND  roleid = 5
            AND c.id= :course";
        if(!$users = $DB->get_record_sql($usercount_sql, array("course" => $course->id), IGNORE_MISSING)) {
            throw new \dml_exception('Couldnt retrieve course users.');
        }
        if($users->count == 0) {
            throw new \moodle_exception('No users in course.');
        }

        $user_sql = "SELECT c.id, COUNT(c.id) as total, cc.completed
            FROM  mdl_course_completion_criteria c
            JOIN (SELECT c.id, c.course, cc.userid, COUNT(c.id) as completed
                FROM mdl_course_completion_criteria c
                JOIN mdl_course_completion_crit_compl cc
                ON c.id = cc.criteriaid
                WHERE cc.userid = :userid) cc
            ON c.course = cc.course
            WHERE c.course = :course;";
        if(!$user_completion = $DB->get_record_sql($user_sql, array("userid" => $user->id, "course" => $course->id), IGNORE_MISSING)) {
            throw new \dml_exception('Couldnt retrieve user completion.');
        }
        if($user_completion->total == 0) {
            throw new \moodle_exception('No course completion criteria found.');
        }

        $this->user_progress = round($user_completion->completed/$user_completion->total, 3);

        if($this->user_progress == 1) {
            throw new \moodle_exception("The user already completed the course.");
        }

        // TODO: NEED to count amount of users diffrently, not working correctly
        $avg_sql = "SELECT c.id, cc.completed
            FROM  mdl_course_completion_criteria c
            JOIN (SELECT c.id, c.course, cc.userid, COUNT(c.id) as completed
                FROM mdl_course_completion_criteria c
                JOIN mdl_course_completion_crit_compl cc
                ON c.id = cc.criteriaid) cc
            ON c.course = cc.course
            WHERE c.course = :course
            GROUP BY c.course;";
        if(!$avg_completion = $DB->get_record_sql($avg_sql, array("course" => $course->id), IGNORE_MISSING)) {
            throw new \dml_exception('Couldnt retrieve average completion.');
        }

        $this->title = "Do you want to know how your course completion is going?";
        $this->avg_progress = round(($avg_completion->completed/$users->count)/$user_completion->total, 3);

        $difference = $this->user_progress - $this->avg_progress;

        if($difference < 0) {
            if($difference >= -0.25) {
                $this->desc = "Your progress is only " . abs(round($difference * 100, 0)) . "% behind course average. You can easily catch up!";
            } else {
                $this->desc = "You ara quite a bit behind. But nothing is lost. Try catching up. Please don't hesitate to ask your fellow students or teachers for help!";
            }
        } else {
            if($difference <= 0.25) {
                $this->desc = "Your progress is looking fine. But there is no time to rest. A regular interaction with the course content is only recommended!";
            } else {
                $this->desc = "You are far ahead! But don't rest on your laurels!";
            }
        }
    }
}