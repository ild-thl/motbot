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
 * Advice that displays the course progress of the subject in relation to the average course progress.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\retention\advice;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . "/lib/completionlib.php");

/**
 * Advice that displays the course progress of the subject in relation to the average course progress.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
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
    public static function get_name(): \lang_string {
        return new \lang_string('advice:course_completion', 'motbot');
    }

    /**
     * Generates advices as text.
     *
     * @return string
     */
    public function render() {
        $message = $this->title . PHP_EOL;
        $message .= PHP_EOL;
        $message .= (new \lang_string('advice:yourprogress', 'mod_motbot', null))->out($this->user->lang) . ': ' . PHP_EOL;
        $prog10 = round($this->user_progress * 10, 0);
        $prog100 = round($this->user_progress * 100, 0);

        $message .= " |";
        for ($i = 0; $i < 10; $i++) {
            if ($i < $prog10) {
                $message .= "=";
            } else {
                $message .= "  ";
            }
        }
        $message .= "| *" . $prog100 . '%*' . PHP_EOL;
        $message .= PHP_EOL;
        $message .= (new \lang_string('advice:averageprogress', 'motbot'))->out($this->user->lang) . ': ' . PHP_EOL;
        $avg_prog10 = round($this->avg_progress * 10, 0);
        $avg_prog100 = round($this->avg_progress * 100, 0);
        $message .= " |";
        for ($i = 0; $i < 10; $i++) {
            if ($i < $avg_prog10) {
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

    /**
     * Generates advices as html.
     *
     * @return string
     */
    public function render_html() {
        global $OUTPUT;

        $context = [
            "title" => $this->title,
            "user_progress" => $this->user_progress * 100,
            "avg_progress" => $this->avg_progress * 100,
            "user_prog_less_or_equal" => $this->user_progress <= $this->avg_progress,
            "desc" => $this->desc
        ];

        return $OUTPUT->render_from_template('mod_motbot/course_completion', $context);
    }

    /**
     * Generates telegram message object.
     *
     * @return array
     */
    public function render_telegram() {
        return [
            'text' => $this->render(),
            'parse_mode' => 'Markdown'
        ];
    }

    /**
     * Constructor.

     * @param \core\user $user
     * @param \core\course $course
     * @return void
     */
    public function __construct($user, $course) {
        global $DB;
        $this->user = $user;
        $this->course = $course;

        // If the user already fully completed the course, do not generate advice.
        if ($this->course) {
            $is_course_completed_sql = "SELECT id
                FROM mdl_course_completions
                WHERE userid=:userid
                AND timecompleted IS NOT NULL";
            $is_course_completed_sql .= " AND course=:courseid";
            if ($DB->record_exists_sql($is_course_completed_sql, array('userid' => $this->user->id, 'courseid' => $this->course->id))) {
                throw new \moodle_exception("The user already completed the course.");
            }
        }

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
            AND  roleid = 5";
        if ($this->course) {
            $usercount_sql .= " AND c.id= :course";

            if (!$users = $DB->get_record_sql($usercount_sql, array("course" => $this->course->id), IGNORE_MISSING)) {
                throw new \dml_exception('Couldnt retrieve course users.');
            }
        } else if (!$users = $DB->get_record_sql($usercount_sql, array(), IGNORE_MISSING)) {
            throw new \dml_exception('Couldnt retrieve users.');
        }
        if ($users->count == 0) {
            throw new \moodle_exception('No users in course.');
        }

        $user_sql = "SELECT c.id, COUNT(c.id) as total, cc.completed
            FROM  mdl_course_completion_criteria c
            JOIN (SELECT c.id, c.course, cc.userid, COUNT(c.id) as completed
                FROM mdl_course_completion_criteria c
                JOIN mdl_course_completion_crit_compl cc
                ON c.id = cc.criteriaid
                WHERE cc.userid = :userid) cc
            ON c.course = cc.course";
        if ($this->course) {
            $user_sql .= " WHERE c.course = :course";

            if (!$user_completion = $DB->get_record_sql($user_sql, array("userid" => $this->user->id, "course" => $this->course->id), IGNORE_MISSING)) {
                throw new \dml_exception('Couldnt retrieve user completion.');
            }
        } else if (!$user_completion = $DB->get_record_sql($user_sql, array("userid" => $this->user->id), IGNORE_MISSING)) {
            throw new \dml_exception('Couldnt retrieve user completion.');
        }
        if ($user_completion->total == 0) {
            throw new \moodle_exception('No course completion criteria found.');
        }

        $this->user_progress = round($user_completion->completed / $user_completion->total, 3);

        if ($this->user_progress == 1) {
            throw new \moodle_exception("The user already completed the course.");
        }

        $avg_sql = "SELECT c.id, cc.completed
            FROM  mdl_course_completion_criteria c
            JOIN (SELECT c.id, c.course, cc.userid, COUNT(c.id) as completed
                FROM mdl_course_completion_criteria c
                JOIN mdl_course_completion_crit_compl cc
                ON c.id = cc.criteriaid) cc
            ON c.course = cc.course";

        if ($this->course) {
            $avg_sql .= " WHERE c.course = :course";
        }
        $avg_sql .= " GROUP BY c.course";

        if ($this->course) {
            if (!$avg_completion = $DB->get_record_sql($avg_sql, array("course" => $this->course->id), IGNORE_MISSING)) {
                throw new \dml_exception('Couldnt retrieve average completion.');
            }
        } else {
            if (!$avg_completion = $DB->get_record_sql($avg_sql, array(), IGNORE_MISSING)) {
                throw new \dml_exception('Couldnt retrieve average completion.');
            }
        }
        $this->avg_progress = round(($avg_completion->completed / $users->count) / $user_completion->total, 3);

        if ($this->course) {
            $this->title = (new \lang_string('advice:coursecompletion_title', 'mod_motbot', $this->course->shortname))->out($this->user->lang);
        } else {
            $this->title = (new \lang_string('advice:completion_title', 'mod_motbot', null))->out($this->user->lang);
        }

        // $difference = $this->user_progress - $this->avg_progress;

        // if ($difference < 0) {
        //     if ($difference >= -0.25) {
        //         $this->desc = (new \lang_string('advice:coursecompletion_desc_bad', 'motbot', abs(round($difference * 100, 0))))->out($this->user->lang);
        //     } else {
        //         $this->desc = (new \lang_string('advice:coursecompletion_desc_worst', 'motbot'))->out($this->user->lang);
        //     }
        // } else {
        //     if ($difference <= 0.25) {
        //         $this->desc = (new \lang_string('advice:coursecompletion_desc_good', 'motbot'))->out($this->user->lang);
        //     } else {
        //         $this->desc = (new \lang_string('advice:coursecompletion_desc_best', 'motbot'))->out($this->user->lang);
        //     }
        // }
    }
}
