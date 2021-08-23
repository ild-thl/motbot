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
 * No recent accesses.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\analytics\target;

defined('MOODLE_INTERNAL') || die();

/**
 * No recent accesses.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class no_recent_accesses extends \core_course\analytics\target\no_recent_accesses {
    /**
     * Returns the name.
     *
     * If there is a corresponding '_help' string this will be shown as well.
     *
     * @return \lang_string
     */
    public static function get_name() : \lang_string {
        return new \lang_string('target:norecentaccesses', 'motbot', null, 'en');
    }

        /**
     * Discards courses that are not yet ready to be used for prediction.
     *
     * @param \core_analytics\analysable $course
     * @param bool $fortraining
     * @return true|string
     */
    public function is_valid_analysable(\core_analytics\analysable $course, $fortraining = true) {
        global $DB;

        $instances = $DB->get_records('motbot', array('course' => $course->get_id()));

        if(!$instances) {
            return get_string('nomotbotinstance', 'motbot');
        }

        if(count($instances) > 1) {
            return get_string('tomanyinstances', 'motbot');
        }

        if(reset($instances)->usecode == 0) {
            return get_string('motbotpaused', 'motbot');
        }

        return parent::is_valid_analysable($course, $fortraining);
    }

    /**
     * Discard student enrolments that are invalid.
     *
     * Note that this method assumes that the target is only interested in enrolments that are/were active
     * between the current course start and end times. Targets interested in predicting students at risk before
     * their enrolment start and targets interested in getting predictions for students whose enrolment already
     * finished should overwrite this method as these students are discarded by this method.
     *
     * @param int $sampleid
     * @param \core_analytics\analysable $course
     * @param bool $fortraining
     * @return bool
     */
    public function is_valid_sample($sampleid, \core_analytics\analysable $course, $fortraining = true) {
        global $DB;

        $userenrol = $this->retrieve('user_enrolments', $sampleid);

        $userid = \mod_motbot\manager::get_prediction_subject($sampleid);
        $motbot = $DB->get_record('motbot', array('course' => $course->get_id()));

        if($motbot) {
            if(!$DB->get_record('motbot_course_user', array('motbot' => $motbot->id, 'user' => $userid, 'authorized' => 1))) {
                return false;
            }
        }

        return parent::is_valid_sample($sampleid, $course, $fortraining);
    }


    /**
     * Callback to execute once a prediction has been returned from the predictions processor.
     *
     * Note that the analytics_predictions db record is not yet inserted.
     *
     * @param int $modelid
     * @param int $sampleid
     * @param int $rangeindex
     * @param \context $samplecontext
     * @param float|int $prediction
     * @param float $predictionscore
     * @return void
     */
    public function prediction_callback($modelid, $sampleid, $rangeindex, \context $samplecontext, $scalar_prediction, $predictionscore) {
        \mod_motbot\retention\bot::log_prediction($modelid, $sampleid, $rangeindex, $samplecontext, $scalar_prediction, $predictionscore);
        return;
    }


    /**
     * Do the user has any read action in the course?
     *
     * @param int $sampleid
     * @param \core_analytics\analysable $analysable
     * @param int $starttime
     * @param int $endtime
     * @return float|null 0 -> accesses, 1 -> no accesses.
     */
    protected function calculate_sample($sampleid, \core_analytics\analysable $analysable, $starttime = false, $endtime = false) {
        $readactions = $this->retrieve('\core\analytics\indicator\any_course_access', $sampleid);
        if ($readactions == \core\analytics\indicator\any_course_access::get_min_value()) {
            return 1;
        }
        return 1;
    }


    /**
     * Is this target generating insights?
     *
     * Defaults to true.
     *
     * @return bool
     */
    public static function uses_insights() {
        return false;
    }

    public static function is_critical() {
        return true;
    }

    public static function always_intervene() {
        return true;
    }


    public static function get_desired_events() {
        return array('\core\event\course_viewed');
    }

}
