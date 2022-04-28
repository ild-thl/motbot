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
 * Advice that suggests to check out recently added or modified course activities.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\retention\advice;

defined('MOODLE_INTERNAL') || die();

/**
 * Advice that suggests to check out recently added or modified course activities.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class recent_activities extends \mod_motbot\retention\advice\title_and_actionrow {
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
        return new \lang_string('advice:recent_activities', 'motbot');
    }

    /**
     * Constructor.
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
        $lastaccess = $DB->get_records('user_lastaccess', $lastaccesscondition, 'timeaccess DESC', 'timeaccess', 0, 1);
        $starttime = $lastaccess->timeaccess;
        $select = "(eventname = :create OR eventname = :update) AND timecreated > :starttime AND timecreated <= :endtime";
        $params = array('create' => '\core\event\course_module_created', 'update' => '\core\event\course_module_updated', 'starttime' => $starttime, 'endtime' => $endtime);
        if ($this->course) {
            $select .= " AND courseid = :courseid";
            $params['courseid'] = $this->course->id;
        }
        $newactivities = $logstore->get_events_select($select, $params, 'timecreated DESC', 0, 5);

        if (empty($newactivities)) {
            throw new \moodle_exception('No recent activities.');
        }

        foreach ($newactivities as $activity) {
            $status = '';
            if ($activity->eventname === '\core\event\course_module_created') {
                $status = (new \lang_string('new'))->out($this->user->lang);
            } else {
                $status = (new \lang_string('motbot:updated', 'motbot'))->out($this->user->lang);
            }
            $this->actions[] = [
                'action_title' => $status . ': ' . $activity->other['name'] .
                ' (' . userdate(
                    $activity->timecreated,
                    (new \lang_string('strftimedate', 'langconfig'))->out($this->user->lang)
                ) . ')',
                'action_url' => $CFG->wwwroot . '/mod/' . $activity->other['modulename'] . '/view.php?id=' . $activity->objectid,
                'action' => $status . ': ' .
                (new \lang_string('motbot:goto', 'motbot', $activity->other['name']))->out($this->user->lang) .
                ' (' . userdate(
                    $activity->timecreated,
                    (new \lang_string('strftimedate', 'langconfig'))->out($this->user->lang)
                ) . ')',
            ];
        }

        $this->title = (new \lang_string('advice:recentactivities_title', 'motbot'))->out($this->user->lang);
    }
}
