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

require_once($CFG->dirroot.'/mod/motbot/locallib.php');

/**
 * Advice that suggests to check out recent forum activities.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class recent_forum_activity extends \mod_motbot\retention\advice\title_and_actionlist {
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

        if (!$logstore = \core_analytics\manager::get_analytics_logstore()) {
            throw new \coding_exception('No available log stores');
        }

        $endtime = time();
        $lastaccess_condition = array('userid' => $user->id);
        if($course) {
            $lastaccess_condition['courseid'] = $course->id;
        }
        $lastaccess = $DB->get_record('user_lastaccess', $lastaccess_condition, '*', IGNORE_MISSING);
        $starttime = $lastaccess->timeaccess;
        $select = "eventname = :eventname AND timecreated > :starttime AND timecreated <= :endtime";
        $params = array('eventname' => '\core\event\course_module_created', 'starttime' => $starttime, 'endtime' => $endtime);
        if($course) {
            $select .= " AND courseid = :courseid";
            $params['courseid'] = $course->id;
        }
        $new_activities = $logstore->get_events_select($select, $params, 'timecreated DESC', 0, 5);

        if(empty($new_activities)) {
            throw new \moodle_exception('No recent activities.');
        }

        $actions = array();
        foreach($new_activities as $activity) {
            $this->actions[] = [
                'action_title' => 'An activity or ressource of type ' . \get_string('modulename', 'mod_' . $activity->other['modulename']) . ' was added on ' . userdate($activity->timecreated),
                'action_url' => $CFG->wwwroot . '/mod/' . $activity->other['modulename'] . '/view.php?id=' . $activity->objectid,
                'action' => 'Go to ' . $activity->other['name'],
            ];
        }

        $this->title = 'These new ressources might be worth checking out:';
    }
}