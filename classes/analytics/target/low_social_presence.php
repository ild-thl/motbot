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
 * Low social presence target.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\analytics\target;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/motbot/locallib.php');

/**
 * Low social presence target.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class low_social_presence extends \core_course\analytics\target\course_enrolments {

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
    public static function get_name(): \lang_string {
        return new \lang_string('target:lowsocialpresence', 'motbot');
    }

    /**
     * Only past stuff whose start matches the course start.
     *
     * @param  \core_analytics\local\time_splitting\base $timesplitting
     * @return bool
     */
    public function can_use_timesplitting(\core_analytics\local\time_splitting\base $timesplitting): bool {
        return ($timesplitting instanceof \core_analytics\local\time_splitting\past_periodic);
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

        if (!$instances) {
            return get_string('motbot:noinstance', 'motbot');
        }

        if (count($instances) > 1) {
            return get_string('tomanyinstances', 'motbot');
        }

        $motbot = reset($instances);
        if ($motbot->active == 0) {
            return get_string('motbot:paused', 'motbot');
        }

        $message = $DB->get_record('motbot_model', array('motbot' => $motbot->id, 'target' => '\mod_motbot\analytics\target\low_social_presence'));
        if (!$message || !$message->active) {
            return get_string('motbot:modelinactive', 'motbot');
        }

        if (!$course->was_started()) {
            return get_string('coursenotyetstarted', 'course');
        }

        if (!$fortraining && !$course->get_course_data()->visible) {
            return get_string('hiddenfromstudents');
        }

        if (!$this->students = $course->get_students()) {
            return get_string('nocoursestudents', 'course');
        }

        if (!course_format_uses_sections($course->get_course_data()->format)) {
            // We can not split activities in time ranges.
            return get_string('nocoursesections', 'course');
        }

        if ($course->get_end() && ($course->get_end() < $course->get_start())) {
            return get_string('errorendbeforestart', 'course', $course->get_end());
        }

        // Finished courses can not be used to get predictions.
        if (!$fortraining && $course->is_finished()) {
            return get_string('coursealreadyfinished', 'course');
        }

        if ($fortraining) {
            // Ongoing courses data can not be used to train.
            if (!$course->is_finished()) {
                return get_string('coursenotyetfinished', 'course');
            }
        }

        return true;
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

        if (!$motbot = $DB->get_record('motbot', array('course' => $course->get_id()))) {
            return false;
        }

        if (!$course_user = $DB->get_record('motbot_course_user', array('motbot' => $motbot->id, 'user' => $userid, 'authorized' => 1))) {
            return false;
        }

        $disabled_models = json_decode($course_user->disabled_models);
        if (!empty($disabled_models) && \in_array('\\' . \get_class($this), $disabled_models)) {
            return false;
        }

        return parent::is_valid_sample($sampleid, $course, $fortraining);
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
     * @return float|null 0 -> enough social presence, 1 -> low social presence
     */
    protected function calculate_sample($sampleid, \core_analytics\analysable $course, $starttime = false, $endtime = false) {
        if (!$this->enrolment_active_during_analysis_time($sampleid, $starttime, $endtime)) {
            // We should not use this sample as the analysis results could be misleading.
            return null;
        }
        echo ('Samplid: ' . $sampleid);

        $potential = 0;
        $score = 0;

        if (mod_motbot_get_mod_count('forum', $course->get_id()) > 0) {
            $potential++;
            $forumscore = $this->retrieve('\mod_motbot\analytics\indicator\social_presence_in_course_forum', $sampleid);
            if ($forumscore) {
                $score += $forumscore;
            }
        }

        if (mod_motbot_get_mod_count('chat', $course->get_id()) > 0) {
            $potential++;
            $chatscore = $this->retrieve('\mod_motbot\analytics\indicator\social_presence_in_course_chat', $sampleid);
            if ($chatscore) {
                $score += $chatscore;
            }
        } else {
            echo ('no chat in course: ' . $course->get_id());
        }


        // if(mod_motbot_get_mod_count('feedback', $course->get_id()) > 0) {
        //     $potential++;
        //     $feedbackscore = $this->retrieve('\mod_motbot\analytics\indicator\any_write_action_in_course_feedback_yet', $sampleid);
        //     if ($feedbackscore > 0) {
        //         $score += $feedbackscore;
        //     }
        // }

        echo ('potential: ' . $potential . ' and score: ' . $score . '_____');

        return 1;
        if ($score <= 0) {
            return 1;
        }
        return 0;
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
     * @param float|int $scalar_prediction
     * @param float $predictionscore
     * @return void
     */
    public function prediction_callback($modelid, $sampleid, $rangeindex, \context $samplecontext, $scalar_prediction, $predictionscore) {
        \mod_motbot\manager::log_prediction($modelid, $sampleid, $rangeindex, $samplecontext, $scalar_prediction, $predictionscore);
        return;
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
        return array(
            '\mod_chat\event\message_sent', '\mod_forum\event\assessable_uploaded', '\mod_forum\event\discussion_created',
            '\mod_forum\event\post_created'
        );
    }
}
