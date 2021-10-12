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
 * Manages the generation of advice for an intervention.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\retention;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/motbot/locallib.php');

/**
 * Manages the generation of advice for an intervention.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class advice_manager {
    private $user;
    private $course;
    private $target;
    private $advices = null;

    public function __construct($user, $course, $target) {
        $this->user = $user;
        $this->course = $course;
        $this->target = $target;
    }

    private function generate_advice() {
        if($this->target == '\mod_motbot\analytics\target\low_social_presence') {
            if(!$advice = $this->get_advice_if_available('\mod_motbot\retention\advice\recommended_discussion')) {
                $advice = $this->get_advice_if_available('\mod_motbot\retention\advice\recent_forum_activity');
            }
            $this->advices[] = $advice;
        } else if($this->target == '\mod_motbot\analytics\target\no_recent_accesses') {
            $this->advices[] = $this->get_advice_if_available('\mod_motbot\retention\advice\course_completion');
            $this->advices[] = $this->get_advice_if_available('\mod_motbot\retention\advice\visit_course');
            $this->advices[] = $this->get_advice_if_available('\mod_motbot\retention\advice\recent_activities');
        }

        $this->advices[] = $this->get_advice_if_available('\mod_motbot\retention\advice\feedback');
    }

    public function get_advice_if_available($class) {
        $advice = null;
        try{
            $advice = new $class($this->user, $this->course);
        } catch (\moodle_exception $e) {
            print_r($e->getMessage());
            return null;
        }
        return $advice;
    }

    public function render() {
        if($this->advices == null) {
            $this->generate_advice();
        }
        $message = '';

        foreach($this->advices as $advice) {
            if($advice == null) {
                continue;
            }
            $message .= PHP_EOL . PHP_EOL . $advice->render();
        }

        return $message;
    }

    public function render_html() {
        global $OUTPUT;

        if($this->advices == null) {
            $this->generate_advice();
        }

        $html_rendered_advices = [];
        $message = "<h3>Suggestions:</h3><div>";

        foreach($this->advices as $advice) {
            if($advice == null) {
                continue;
            }
            $rendered = $advice->render_html();
            $html_rendered_advices[] = $rendered;
            $message .= $rendered . "<br/>";
        }

        $message .= "</div><br/>";

        $context = [
            "advices" => $html_rendered_advices,
        ];

        return $message;

        // return $OUTPUT->render_from_template('mod_motbot/advices', $context);
    }

    // private function get_new_forum_activity_suggestion($user, $course) {
    //     global $DB, $CFG;


    //     $suggestions = array();

    //     $sql = 'SELECT cm.id as id, f.name as name
    //     FROM mdl_course_modules cm
    //     JOIN mdl_modules m ON m.id = cm.module
    //     JOIN mdl_forum f ON f.id = cm.instance
    //     WHERE cm.course = :courseid
    //     AND m.name = "forum";';
    //     $activities = $DB->get_records_sql($sql, array('courseid' => $course->id));
    //     if(empty($activities)) {
    //         return $suggestions;
    //     }

    //     $buttons = array();
    //     foreach($activities as $activity) {
    //         $buttons[] = [
    //             'url' => $CFG->wwwroot . '/mod/forum/view.php?id=' . $activity->id,
    //             'url_text' => 'Go to ' . $activity->name,
    //         ];
    //     }

    //     if (!$logstore = \core_analytics\manager::get_analytics_logstore()) {
    //         throw new \coding_exception('No available log stores');
    //     }

    //     $endtime = time();
    //     // One week earlier.
    //     $lastaccess = $DB->get_record('user_lastaccess', ['courseid' => $course->id, 'userid' => $user->id]);
    //     $starttime = $lastaccess->timeaccess;
    //     $select = "courseid = :courseid AND eventname = :eventname AND timecreated > :starttime AND timecreated <= :endtime";

    //     $params = array('courseid' => $course->id, 'eventname' => '\mod_forum\event\post_created', 'starttime' => $starttime, 'endtime' => $endtime);
    //     $any_new_forum_posts = $logstore->get_events_select_count($select, $params);

    //     $params = array('courseid' => $course->id, 'eventname' => '\mod_forum\event\discussion_created', 'starttime' => $starttime, 'endtime' => $endtime);
    //     $any_new_forum_dicussions = $logstore->get_events_select_count($select, $params);

    //     if($any_new_forum_posts || $any_new_forum_dicussions) {
    //         $title = 'See what other people have written in a forum recently!';
    //     } else {
    //         $title = 'Ask a question or reply to a post in a forum!';
    //     }

    //     $suggestions[] = [
    //         'title' => $title,
    //         'buttonrow' => $buttons,
    //     ];

    //     return $suggestions;
    // }
}