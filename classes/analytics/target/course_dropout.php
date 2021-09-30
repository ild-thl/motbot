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
 * Drop out course target.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\analytics\target;

defined('MOODLE_INTERNAL') || die();

/**
 * Drop out course target.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_dropout extends \core_course\analytics\target\course_dropout {
    /**
     * Machine learning backends are not required to predict.
     *
     * @return bool
     */
    public static function based_on_assumptions() {
        return true;
    }

    /**
     * Returns the name.
     *
     * If there is a corresponding '_help' string this will be shown as well.
     *
     * @return \lang_string
     */
    public static function get_name() : \lang_string {
        return new \lang_string('target:coursedropout', 'motbot', null, 'en');
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

        $motbot = reset($instances);
        if($motbot->usecode == 0) {
            return get_string('motbotpaused', 'motbot');
        }

        $message = $DB->get_record('motbot_message', array('motbot' => $motbot->id, 'target' => '\mod_motbot\analytics\target\course_dropout'));
        if(!$message || !$message->active) {
            return get_string('motbotmodelinactive', 'motbot');
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

        $userid = \mod_motbot\manager::get_prediction_subject($sampleid);
        $motbot = $DB->get_record('motbot', array('course' => $course->get_id()));

        if($motbot) {
            if(!$DB->get_record('motbot_course_user', array('motbot' => $motbot->id, 'user' => $userid, 'authorized' => 1))) {
                // echo('motbot not authorized for user: ' . $userid);
                return false;
            }
        }


        $return = parent::is_valid_sample($sampleid, $course, $fortraining);
        // if($return) {
        //     echo('Valid sample: ' . $userid);
        // } else {
        //     echo('Inalid sample: ' . $userid);
        // }

        return $return;
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
        \mod_motbot\manager::log_prediction($modelid, $sampleid, $rangeindex, $samplecontext, $scalar_prediction, $predictionscore);
        return;
    }


    /**
     * calculate_sample
     *
     * The meaning of a drop out changes depending on the settings enabled in the course. Following these priorities order:
     * 1.- Course completion
     * 2.- No logs during the last quarter of the course
     *
     * @param int $sampleid
     * @param \core_analytics\analysable $course
     * @param int $starttime
     * @param int $endtime
     * @return float|null 0 -> not at risk, 1 -> at risk
     */
    protected function calculate_sample($sampleid, \core_analytics\analysable $course, $starttime = false, $endtime = false) {
        echo('Calculate sample: ' . $sampleid);

        $potential_cognitive_depth = $this->retrieve('\core_course\analytics\indicator\potential_cognitive_depth', $sampleid);
        print_r('Cognitive Depth: ' . $potential_cognitive_depth);
        $potential_social_breadth = $this->retrieve('\core_course\analytics\indicator\potential_social_breadth', $sampleid);
        print_r('Social Breadth: ' . $potential_social_breadth);
        return 0;

        // ----------------

        if (!$this->enrolment_active_during_analysis_time($sampleid, $starttime, $endtime)) {
            // We should not use this sample as the analysis results could be misleading.
            echo("no active erol during analysis time");
            return null;
        }

        $userenrol = $this->retrieve('user_enrolments', $sampleid);

        // We use completion as a success metric only when it is enabled.
        $completion = new \completion_info($course->get_course_data());
        if ($completion->is_enabled() && $completion->has_criteria()) {
            $ccompletion = new \completion_completion(array('userid' => $userenrol->userid, 'course' => $course->get_id()));
            if ($ccompletion->is_complete()) {
                echo('comletion complete');
                return 0;
            } else {
                return 1;
            }
        }

        if (!$logstore = \core_analytics\manager::get_analytics_logstore()) {
            throw new \coding_exception('No available log stores');
        }

        // No logs during the last quarter of the course.
        $courseduration = $course->get_end() - $course->get_start();
        $limit = intval($course->get_end() - ($courseduration / 4));
        $select = "courseid = :courseid AND userid = :userid AND timecreated > :limit";
        $params = array('userid' => $userenrol->userid, 'courseid' => $course->get_id(), 'limit' => $limit);
        $nlogs = $logstore->get_events_select_count($select, $params);
        if ($nlogs == 0) {
            return 1;
        }
        echo('logs during last quarter');
        return 0;
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

    /**
     * Does this target allow a custom intervention message?
     *
     * @return bool
     */
    public static function custom_intervention() {
        return true;
    }

    /**
     * Is a prediction of this target considered critical?
     *
     * @return bool
     */
    public static function is_critical() {
        return true;
    }

    /**
     * Should a motbot always intervene or only in certain circumstances?
     *
     * @return bool
     */
    public static function always_intervene() {
        return true;
    }

    /**
     * Defines an array of events, that can help
     * prevent another negative prediction.
     *
     * @return string[]
     */
    public static function get_desired_events() {
        return null;
    }
}