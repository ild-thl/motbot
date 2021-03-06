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
 * Any access indicator.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\analytics\indicator;

defined('MOODLE_INTERNAL') || die();

/**
 * Any access indicator.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class any_access extends \core_analytics\local\indicator\binary {

    /**
     * Returns the name.
     *
     * If there is a corresponding '_help' string this will be shown as well.
     *
     * @return \lang_string
     */
    public static function get_name(): \lang_string {
        return new \lang_string('indicator:anyaccess', 'motbot');
    }

    /**
     * required_sample_data
     *
     * @return string[]
     */
    public static function required_sample_data() {
        return array('user');
    }

    /**
     * Store userid => timeaccess relation if the provided analysable is a user.
     *
     * @param  \core_analytics\analysable $analysable
     * @return null
     */
    public function fill_per_analysable_caches(\core_analytics\analysable $analysable) {
        global $DB;

        if ($analysable instanceof \core_analytics\user) {
            // Indexed by userid (there is a UNIQUE KEY at DB level).
            $this->lastaccess = $DB->get_field('user', 'lastaccess', ['id' => $analysable->get_id()], IGNORE_MISSING);
        }
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
        $user = $this->retrieve('user', $sampleid);

        // We first try using user_lastaccess as it is much faster than the log table.
        if (!$this->lastaccess) {
            // The user never accessed.
            return self::get_min_value();
        } else if (!$starttime && !$endtime) {
            // No time restrictions, so all good as long as there is a record.
            return self::get_max_value();
        } else if ($starttime && $this->lastaccess < $starttime) {
            // The last access is prior to $starttime.
            return self::get_min_value();
        } else if ($endtime && $this->lastaccess < $endtime) {
            // The last access is before the $endtime.
            return self::get_max_value();
        } else if ($starttime && !$endtime && $starttime <= $this->lastaccess) {
            // No end time, so max value as long as the last access is after $starttime.
            return self::get_max_value();
        }

        // If the last access is after $endtime we can not know for sure if the user accessed or not
        // between $starttime and $endtime, we need to check the logs table in this case. Note that
        // it is unlikely that we will reach this point as this indicator will be used in models whose
        // dates are in the past.

        if (!$logstore = \core_analytics\manager::get_analytics_logstore()) {
            throw new \coding_exception('No available log stores');
        }

        // Filter by context to use the logstore_standard_log db table index.
        $select = "userid = :userid";
        $params = ['userid' => $user->id];

        if ($starttime) {
            $select .= " AND timecreated > :starttime";
            $params['starttime'] = $starttime;
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
