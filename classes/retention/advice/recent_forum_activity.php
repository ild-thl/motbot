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
 * Advice that suggests to check out recent forum activities.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\retention\advice;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/motbot/locallib.php');

/**
 * Advice that suggests to check out recent forum activities.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class recent_forum_activity extends \mod_motbot\retention\advice\title_and_actionrow {
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
        return new \lang_string('advice:recent_forum_activity', 'motbot');
    }

    /**
     * Contsructor.
     *
     * TODO: Update to get recent forum activities instead of recently created course modules.
     *
     * @param \core\user $user
     * @param \core\course $course
     * @return void
     */
    public function __construct($user, $course) {
        global $DB, $CFG;
        $this->user = $user;
        $this->course = $course;

        if (!$logstore = \core_analytics\manager::get_analytics_logstore()) {
            throw new \coding_exception('No available log stores');
        }

        $endtime = time();
        $lastaccesscondition = array('userid' => $this->user->id);
        if ($this->course) {
            $lastaccesscondition['courseid'] = $this->course->id;
        }
        $lastaccess = $DB->get_record('user_lastaccess', $lastaccesscondition, '*', IGNORE_MISSING);
        $starttime = $lastaccess->timeaccess;
        $sql = "SELECT d.id, d.course, p.userid, p.created, p.subject
                     FROM {forum_discussions} d
                LEFT JOIN {forum_posts} p
                          ON d.id = p.discussion
                    WHERE p.userid != :userid
                      AND p.parent = 0
                      AND p.deleted = 0
                      AND p.created > :starttime
                      AND p.created <= :endtime";
        $params = array('userid' => $this->user->id, 'starttime' => $starttime, 'endtime' => $endtime);
        if ($this->course) {
            $sql .= " AND d.course = :courseid";
            $params['courseid'] = $this->course->id;
        }
        $sql .= " ORDER BY p.created DESC LIMIT 5";
        $newdiscussions = $DB->get_records_sql($sql, $params, IGNORE_MISSING);

        if (empty($newdiscussions)) {
            throw new \moodle_exception('No recent forum discussions.');
        }

        foreach ($newdiscussions as $discussion) {
            $this->actions[] = [
                'action_title' => $discussion->subject .
                ' (' .
                    userdate(
                        $discussion->created,
                        (new \lang_string('strftimedate', 'langconfig'))->out($this->user->lang)
                    ) .
                ')',
                'action_url' => (new \moodle_url('/mod/forum/discuss.php', array('id' => $discussion->id)))->out(),
                'action' => (new \lang_string('motbot:goto', 'motbot', $discussion->subject))->out($this->user->lang)
                . ' (' . userdate($discussion->created, (new \lang_string('strftimedate', 'langconfig'))->out($this->user->lang)) . ')',
            ];
        }

        $this->title = (new \lang_string('advice:recentforumactivity_title', 'motbot'))->out($this->user->lang);
    }
}
