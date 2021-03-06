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
 * Write actions in a feedback activity indicator.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\analytics\indicator;

defined('MOODLE_INTERNAL') || die();

/**
 * Write actions in a feedback activity indicator.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class any_write_action_in_course_feedback_yet extends \core\analytics\indicator\any_write_action_in_course {

    /**
     * Returns the name.
     *
     * If there is a corresponding '_help' string this will be shown as well.
     *
     * @return \lang_string
     */
    public static function get_name() : \lang_string {
        return new \lang_string('indicator:anywriteincoursefeedbackyet', 'motbot');
    }


    /**
     * calculate_sample
     *
     * @param int $sampleid
     * @param string $sampleorigin
     * @param int $starttime
     * @param int $endtime
     * @return float
     */
    protected function calculate_sample($sampleid, $sampleorigin, $starttime = false, $endtime = false) {
        global $DB;

        if (!$logstore = \core_analytics\manager::get_analytics_logstore()) {
            throw new \coding_exception('No available log stores');
        }

        // Filter by context to use the logstore_standard_log db table index.
        $course = $this->retrieve('course', $sampleid);
        $select = "courseid = :courseid AND (crud = 'c' OR crud = 'u') AND component = :component";
        $params = array('courseid' => $course->id, 'component' => 'mod_feedback');

        if ($user = $this->retrieve('user', $sampleid)) {
            $select .= " AND userid = :userid";
            $params['userid'] = $user->id;
        }

        if ($endtime) {
            $select .= " AND timecreated <= :endtime";
            $params['endtime'] = $endtime;
        }

        $nlogs = $logstore->get_events_select_count($select, $params);
        if ($nlogs) {
            return self::get_max_value();
        } else {
            return self::get_min_value();
        }
    }
}
