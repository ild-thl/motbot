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
 * Advice - Link to activity the user last accessed. Only considering activities that have completion tracking enabled.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\retention\advice;

defined('MOODLE_INTERNAL') || die();

/**
 * Advice - Link to activity the user last accessed. Only considering activities that have completion tracking enabled.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class last_stop extends \mod_motbot\retention\advice\action {
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
        return new \lang_string('advice:last_stop', 'motbot');
    }

    /**
     * Contstructor.

     * @param \core\user $user
     * @param \core\course $course
     * @return void
     */
    public function __construct($user, $course) {
        global $CFG, $DB;



        // Stop initialization, if $user is unset.
        if (!$user) {
            throw new \moodle_exception('No user given.');
        }

        // If there is no course given, get last accesed course instead.
        if ($course) {
            $last_course = $course->id;
        } else {
            $last_course = $DB->get_field('user_lastaccess', 'courseid', array('userid' => $user->id), IGNORE_MISSING);
        }

        // Stop initialization, if the user dosn't have any last access records.
        if (!$last_course) {
            throw new \moodle_exception('No last access records available.');
        }

        // Init logstore.
        if (!$logstore = \core_analytics\manager::get_analytics_logstore()) {
            throw new \coding_exception('No available log stores');
        }

        // Get the latest course_module_viewed events.
        $select = "eventname LIKE :eventname AND userid = :userid AND courseid = :courseid";
        $params = array('eventname' => '%course_module_viewed', 'userid' => $user->id, 'courseid' => $last_course);
        $last_activities = $logstore->get_events_select($select, $params, 'timecreated DESC', 0, 25);

        $checked_activites = array();
        foreach ($last_activities as $activity_log) {
            if (in_array($activity_log->contextinstanceid, $checked_activites)) {
                // Skip activities we already checked.
                continue;
            }
            $checked_activites[] = $activity_log->contextinstanceid;

            // Get more activity data, only for activities that have completion tracking enabled.
            $sql = 'SELECT m.name, cccc.id AS completed, cccc.userid AS user
                FROM {' . $activity_log->objecttable . '} AS m, {course_modules} AS cm
                JOIN {course_completion_criteria} AS ccc
                ON ccc.moduleinstance = cm.id
                LEFT JOIN (SELECT id, userid, criteriaid
                    FROM  mdl_course_completion_crit_compl
                    WHERE userid = 3) AS cccc
                ON cccc.criteriaid = ccc.id
                WHERE m.id = :module
                AND cm.id = :course_module
                AND cm.completion = 1';
            $params = array('module' => $activity_log->objectid, 'course_module' => $activity_log->contextinstanceid, 'user' => $user->id);
            $activity = $DB->get_record_sql($sql, $params, IGNORE_MISSING);
            \print_r($activity);
            if (!$activity) {
                // Activity dosn't have completion tracking enabled. Therefore check next activity record.
                continue;
            }

            // Recommend this activity, if it wasn't yet completed by the user.
            if (!$activity->completed) {
                $this->title = \get_string('advice:laststop_title', 'motbot');
                $this->action_url = $CFG->wwwroot . '/mod/' . $activity_log->objecttable . '/view.php?id=' . $activity_log->contextinstanceid;
                $this->action = \get_string('motbot:goto', 'motbot', $activity->name);
                return;
            }

            // If the latest accessed activity was completed, recommend another uncompleted activity in the same course.
            $sql = 'SELECT ccc.moduleinstance AS cmid, cccc.id
                FROM {course_completion_criteria} AS ccc
                LEFT JOIN (SELECT a.id, a.criteriaid
                    FROM {course_completion_crit_compl} AS a
                    WHERE a.userid = :user) AS cccc ON cccc.criteriaid = ccc.id
                WHERE ccc.course = :course
                AND cccc.id IS NULL';
            $params = array('course' => $last_course, 'user' => $user->id);
            $uncompleted_criteria = $DB->get_records_sql($sql, $params, IGNORE_MISSING);

            if (count($uncompleted_criteria) < 1) {
                // If there is no other uncompleted activity.
                break;
            }
            if (count($uncompleted_criteria) > 0) {
                // Preferably we want to suggest an uncompleted activity that comes after the last accesed activity.
                foreach ($uncompleted_criteria as $crit) {
                    if ($crit->cmid > $activity_log->contextinstanceid) {
                        $uncompleted = $crit;
                        break;
                    }
                }
                // If there is no uncompleted criteria after the last accessed, we use the first uncompleted instead.
                $uncompleted = reset($uncompleted_criteria);
            }

            // Get the name of table were the we can get fata about the activity instance.
            $sql = 'SELECT m.name AS name, cm.instance AS instance
                        FROM {course_modules} AS cm
                        JOIN {modules} AS m
                        ON m.id = cm.module
                        WHERE cm.id = :cmid';
            $params = array('cmid' => $uncompleted->cmid);
            $table = $DB->get_record_sql($sql, $params, IGNORE_MISSING);
            if (!$table) {
                break;
            }

            // Get the name of the activity.
            $activity_name = $DB->get_field($table->name, 'name', array('id' => $table->instance), IGNORE_MISSING);
            if ($activity_name) {
                $this->title = \get_string('advice:laststop_title_newchallenge');
                $this->action_url = $CFG->wwwroot . '/mod/' . $table->name . '/view.php?id=' . $uncompleted->cmid;
                $this->action = \get_string('motbot:goto', 'motbot', $activity->name);
                return;
            }
        }

        // If there are no activities to recommend, recommend to vist the last accessed course instead.
        $course_name = $DB->get_field('course', 'shortname', array('id' => $last_course), IGNORE_MISSING);
        $activity = (object) [
            'url' => '/course/view.php?id=' . $last_course,
            'name' => $course_name,
        ];

        $this->title = \get_string('advice:laststop_title');
        $this->action_url = $CFG->wwwroot . $activity->url;
        $this->action = \get_string('motbot:goto', 'motbot', $activity->name);
    }
}
