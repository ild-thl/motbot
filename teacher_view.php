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
 * Shows an overview of a motbots activity meant for teachers.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/motbot/locallib.php');

/**
 * Shows an overview of a motbots activity meant for teachers.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_motbot_teacher_view {
    /**
     * @var int int $motbot Id of the motbot activity.
     */
    private $motbotid;

    /**
     * @var int  Id of the motbot context.
     */
    private $contextid;

    /**
     * @var int  Id of the current user.
     */
    private $userid;

    /**
     * @var string URL to a settings page.
     */
    public $settings_url;

    /**
     * Object definition.
     *
     * @param int $moduleid
     * @param int $motbotid
     * @param int $contextid
     * @param int $userid
     * @return void
     */
    public function __construct($moduleid, $motbotid, $contextid, $userid) {
        global $DB, $CFG;

        $this->settings_url = $CFG->wwwroot.'/course/modedit.php?update=' . $moduleid;
        $this->motbotid = $motbotid;
        $this->contextid = $contextid;
        $this->userid = $userid;
    }

    /**
     * Returns html for this page.
     *
     * @return string
     */
    public function render() {
        global $OUTPUT;

        return $OUTPUT->render_from_template('mod_motbot/teacher_view', $this->get_contextinfo());
    }

    /**
     * Gets placeholder information for a mustache template.
     *
     * @return array
     */
    private function get_contextinfo() {
        global $DB;

        $models = array();

        $motbot_models = $DB->get_records('motbot_model', array('motbot' => $this->motbotid), '', 'target, active');
        foreach($motbot_models as $motbot_model) {
            $models[] = $this->get_model_data($motbot_model);
        }

        // Sort models so inactive models come last.
        function sort_models_by_enable($a, $b) {
            if($a["enabled"] == $b["enabled"]) return 0;
            return (!$b["enabled"] && $b["enabled"]) ? -1 : 1;
        }
        usort($models, "sort_models_by_enable");


        $contextinfo = [
            'settings_url' => $this->settings_url,
            'models' => $models,
        ];
        return $contextinfo;
    }


    /**
     * Gets data, that is supposed to be displayed per model.
     *
     * @return array
     */
    public function get_model_data($message) {
        global $DB;

        $target_name = mod_motbot_get_name_of_target($message->target);
        // Default values.
        $model = [
            "name" => \get_string('target:' . $target_name . '_short', 'motbot'),
            "enabled" => $message->active,
            "count" => 0,
            "helpful" => 0,
            "unhelpful" => 0,
            "last_intervention" => null,
            "image" => 'disabled_motbot'
        ];

        $count = $DB->count_records('motbot_intervention', array('contextid' => $this->contextid, 'target' => $message->target));
        $helpful = $DB->count_records('motbot_intervention', array('contextid' => $this->contextid, 'target' => $message->target, 'helpful' => true));
        $unhelpful = $DB->count_records('motbot_intervention', array('contextid' => $this->contextid, 'target' => $message->target, 'helpful' => false));

        $model["count"] = $count;
        $model["helpful"] = $helpful;
        $model["unhelpful"] = $unhelpful;

        if($message->active) {
            if($helpful >= $unhelpful) {
                $model["image"] = 'happy_motbot';
            } else {
                $model["image"] = 'unhappy_motbot';
            }
        }

        if($count < 1) {
            return $model;
        }

        $sql = "SELECT timecreated
            FROM mdl_motbot_intervention
            WHERE contextid = :contextid
            AND target = :target
            ORDER BY timecreated DESC
            LIMIT 1";
        $last_intervention = $DB->get_record_sql($sql, array('contextid' => $this->contextid, 'target' => $message->target), IGNORE_MISSING);

        if($last_intervention) {
            $model["last_intervention"] = userdate($last_intervention->timecreated);
        }

        return $model;
    }

}