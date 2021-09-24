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

class recent_activities extends \mod_motbot\retention\advice\title_and_actionlist {
    /**
    * Returns a lang_string object representing the name for the indicator or target.
    *
    * Used as column identificator.
    *
    * If there is a corresponding '_help' string this will be shown as well.
    *
    * @return \lang_string
    */
    public static function get_name() : \lang_string {
        return new \lang_string('advice:recent_activities', 'motbot');
    }

    public function __construct($user, $course) {
        global $DB, $CFG;

        if (!$logstore = \core_analytics\manager::get_analytics_logstore()) {
            throw new \coding_exception('No available log stores');
        }

        $endtime = time();
        $lastaccess = $DB->get_record('user_lastaccess', ['courseid' => $course->id, 'userid' => $user->id]);
        $starttime = $lastaccess->timeaccess;
        $select = "courseid = :courseid AND eventname = :eventname AND timecreated > :starttime AND timecreated <= :endtime";
        $params = array('courseid' => $course->id, 'eventname' => '\core\event\course_module_created', 'starttime' => $starttime, 'endtime' => $endtime);
        $new_activities = $logstore->get_events_select($select, $params, null, null, null);

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