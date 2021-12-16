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
 * Recent cognitive presence of a user
 *
 * @package   core
 * @copyright 2019 David Monllao {@link http://www.davidmonllao.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\analytics\target;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/enrollib.php');

/**
 * Recent cognitive presence of a user.
 *
 * @package   core
 * @copyright 2019 David Monllao {@link http://www.davidmonllao.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class recent_cognitive_presence extends \core_analytics\local\target\discrete {

    /**
     * Machine learning backends are not required to predict.
     *
     * @return bool
     */
    public static function based_on_assumptions() {
        return true;
    }

    /**
     * Are this target calculations linear values?
     *
     * @return bool
     */
    public function is_linear() {
        return false;
    }

    /**
     * Only update last analysis time when analysables are processed.
     * @return bool
     */
    public function always_update_analysis_time(): bool {
        return false;
    }

    /**
     * Only upcoming stuff.
     *
     * @param  \core_analytics\local\time_splitting\base $timesplitting
     * @return bool
     */
    public function can_use_timesplitting(\core_analytics\local\time_splitting\base $timesplitting): bool {
        return ($timesplitting instanceof \core_analytics\local\time_splitting\past_periodic);
    }

    /**
     * Returns the name.
     *
     * If there is a corresponding '_help' string this will be shown as well.
     *
     * @return \lang_string
     */
    public static function get_name(): \lang_string {
        return new \lang_string('target:recentcognitivepresence', 'motbot');
    }

    /**
     * Returns the target discrete values.
     *
     * Only useful for targets using discrete values, must be overwriten if it is the case.
     *
     * @return array
     */
    public static final function get_classes() {
        return array(0, 1, 2, 3);
    }

    /**
     * classes_description
     *
     * @return string[]
     */
    protected static function classes_description() {
        return array(
            get_string('No Recent Accesses'),
            get_string('Recent Accesses'),
            get_string('Recent Write Activity'),
            get_string('Recent Completions'),
        );
    }

    /**
     * Returns the predicted classes that will be ignored.
     *
     * @return array
     */
    public function ignored_predicted_classes() {
        // No need to process users that have been active.
        return array(2, 3);
    }

    /**
     * get_analyser_class
     *
     * @return string
     */
    public function get_analyser_class() {
        return '\core\analytics\analyser\users';
    }

    /**
     * All users are ok.
     *
     * @param \core_analytics\analysable $analysable
     * @param mixed $fortraining
     * @return true|string
     */
    public function is_valid_analysable(\core_analytics\analysable $user, $fortraining = true) {
        global $DB;
        if (!$DB->get_record('motbot_user', array('user' => $user->get_id(), 'authorized' => 1))) {
            return get_string('userdisabledmotbot', 'motbot');
        }

        return true;
    }

    /**
     * Samples are users and all of them are ok.
     *
     * @param int $sampleid
     * @param \core_analytics\analysable $analysable
     * @param bool $fortraining
     * @return bool
     */
    public function is_valid_sample($sampleid, \core_analytics\analysable $analysable, $fortraining = true) {
        return true;
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
     * Calculation based on activities due indicator.
     *
     * @param int $sampleid
     * @param \core_analytics\analysable $analysable
     * @param int $starttime
     * @param int $endtime
     * @return float
     */
    protected function calculate_sample($sampleid, \core_analytics\analysable $analysable, $starttime = false, $endtime = false) {
        $any_write_action = $this->retrieve('\mod_motbot\analytics\indicator\any_write_action', $sampleid);
        if ($any_write_action == \mod_motbot\analytics\indicator\any_write_action::get_max_value()) {
            return 2;
        }
        $any_accesses = $this->retrieve('\mod_motbot\analytics\indicator\any_access', $sampleid);
        if ($any_accesses == \mod_motbot\analytics\indicator\any_access::get_max_value()) {
            return 1;
        }
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
        return false;
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
        return false;
    }

    /**
     * Defines an array of events, that can help
     * prevent another negative prediction.
     *
     * @return string[]
     */
    public static function get_desired_events() {
        // TODO: Rethink the way we track success of an intervention. Loggedin event might be to low effort?
        return array('\core\event\user_loggedin');
    }
}
