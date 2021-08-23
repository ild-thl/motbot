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
 * Write actions in a course forum indicator.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\analytics\indicator;

defined('MOODLE_INTERNAL') || die();

/**
 * Write actions in a course indicator.
 *
 * @package   core
 * @copyright 2016 David Monllao {@link http://www.davidmonllao.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class social_presence_in_course_activity extends \core_analytics\local\indicator\linear {

     /**
     * Constant for this potential level.
     */
    const POTENTIAL_LEVEL_1 = 1;

    /**
     * Constant for this potential level.
     */
    const POTENTIAL_LEVEL_2 = 2;

    /**
     * Constant for this potential level.
     */
    const POTENTIAL_LEVEL_3 = 3;

    /**
     * Constant for this potential level.
     */
    const POTENTIAL_LEVEL_4 = 4;

    /**
     * required_sample_data
     *
     * @return string[]
     */
    public static function required_sample_data() {
        // User is not required, calculate_sample can handle its absence.
        return array('course');
    }


    /**
     * Returns the name of the activity.
     *
     * @return string
     */
    public function get_activity_name() {
        throw new \coding_exception('Overwrite get_activity_name method to set the name of the activity');
    }


    /**
     * Returns the potential level of social breadth.
     *
     * @return int
     */
    public function get_potential_level() {
        throw new \coding_exception('Overwrite get_social_breadth_level method to set your activity potential social ' .
            'breadth level');
    }

    /**
     * feedback_viewed_events
     *
     * @return string[]
     */
    public static function view_events() {
        throw new \coding_exception('Overwrite post_events method to set the events that should be considered as a view activity');
    }


    /**
     * feedback_viewed_events
     *
     * @return string[]
     */
    public static function post_events() {
        throw new \coding_exception('Overwrite post_events method to set the events that should be considered as a post activity');
    }


    /**
     * feedback_viewed_events
     *
     * @return bool
     */
    protected function any_post_volley() {
        throw new \coding_exception('Overwrite any_post_volley method to set how volley events are supposed to be calculated for this activity');
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

        $course = $this->retrieve('course', $sampleid);
        $user = $this->retrieve('user', $sampleid);

        if(!$course || !$user) {
            return null;
        }

        if(!$this->useractivity = $this->get_user_activity($course, $user, $starttime, $endtime)) {
            return self::MIN_VALUE;
        }

        // We need to adapt the limits to the time range duration.
        // $nweeks = $this->get_time_range_weeks_number($starttime, $endtime);


        $potentiallevel = $this->get_potential_level();
        $scoreperlevel = (self::get_max_value() - self::get_min_value()) / $potentiallevel;
        $score = self::get_min_value();

        for($i = $potentiallevel; $i > 0; $i--) {
            if ($i == self::POTENTIAL_LEVEL_4) {
                // The learner has interacted with participants in at least one "volley" of communications back and forth
                // Cognitive level 4 is to comment on feedback.
                if ($this->any_post_volley()) {
                    $score += $scoreperlevel * $i;
                    break;
                }
            }

            if ($i == self::POTENTIAL_LEVEL_3) {
                // The learner has interacted with multiple participants in this activity,
                // e.g. posting to a discussion forum, wiki, database, etc.
                // Cognitive level 4 is to comment on feedback.
                if ($this->any_post_log()) {
                    $score += $scoreperlevel * $i;
                    break;
                }
            }

            if ($i == self::POTENTIAL_LEVEL_2) {
                // The learner has interacted with at least one other participant
                // (e.g. they have submitted an assignment or attempted a self-grading quiz providing feedback)
                // Cognitive level 2 if the user has made any write action.
                if ($this->any_write_log()) {
                    $score += $scoreperlevel * $i;
                    break;
                }
            }

            if ($i == self::POTENTIAL_LEVEL_1) {
                // Cognitive level 4 is to comment on feedback.
                if ($this->any_log()) {
                    $score += $scoreperlevel * $i;
                    break;
                }
            }
        }

        echo('_' . $this->get_activity_name() . '_raw score: ' . $score);

        // To avoid decimal problems.
        if ($score > self::MAX_VALUE) {
            return self::MAX_VALUE;
        } else if ($score < self::MIN_VALUE) {
            return self::MIN_VALUE;
        }
        return $score;
    }


    private function get_user_activity($course, $user, $starttime, $endtime) {
        if (!$logstore = \core_analytics\manager::get_analytics_logstore()) {
            throw new \coding_exception('No available log stores');
        }

        $select = "courseid = :courseid AND userid = :userid AND component = :component
            AND timecreated > :starttime AND timecreated <= :endtime";
        $params = array('courseid' => $course->id, 'userid' => $user->id, 'starttime' => $starttime,
            'endtime' => $endtime, 'component' => 'mod_' . $this->get_activity_name());

        return $logstore->get_events_select($select, $params, null, null, null);
    }

    private function any_log() {
        return !empty($this->useractivity);
    }

    private function any_write_log() {
        foreach($this->useractivity as $id => $log) {
            if($log->crud == 'c' || $log->crud == 'u') {
                return true;
            }
        }
        return false;
    }

    private function any_post_log() {
        foreach($this->useractivity as $id => $log) {
            if($log->crud == 'c' && in_array($log->eventname, self::post_events())) {
                return true;
            }
        }
        return false;
    }
}
