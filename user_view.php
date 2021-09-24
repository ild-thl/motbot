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
 * Prints an instance of mod_motbot.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/motbot/locallib.php');

class mod_motbot_user_view {
    private $motbotid;
    private $contextid;
    private $userid;
    public $settings_url;

    public function __construct($moduleid, $motbotid, $contextid, $userid) {
        global $DB, $CFG;

        $this->settings_url = $CFG->wwwroot.'/mod/motbot/course_settings.php?id=' . $moduleid;
        $this->motbotid = $motbotid;
        $this->contextid = $contextid;
        $this->userid = $userid;
    }

    public function render() {
        global $OUTPUT;

        return $OUTPUT->render_from_template('mod_motbot/user_view', $this->get_contextinfo());
    }

    private function get_contextinfo() {
        global $DB;

        $models = array();

        $messages = $DB->get_records('motbot_message', array('motbot' => $this->motbotid), '', 'target, active');
        foreach($messages as $message) {
            $models[] = $this->get_model_data($message);
        }


        function sort_models_by_enable($a, $b) {
            if($a["enabled"] == $b["enabled"]) return 0;
            return (!$b["enabled"] && $b["enabled"]) ? -1 : 1;
        }

        usort($models, "sort_models_by_enable");


        $contextinfo = [
            'settings_url' => $this->settings_url,
            'models' => $models,
            'interventions_table' => mod_motbot_get_interventions_table($this->userid, $this->contextid, true),
        ];

        return $contextinfo;
    }


    public function get_model_data($message) {
        global $DB;

        $target_name = mod_motbot_get_name_of_target($message->target);
        $model = [
            "name" => \get_string('target:' . $target_name . '_neutral', 'motbot'),
            "enabled" => $message->active,
            "hasdata" => false,
            "state" => '',
            "date" => null,
            "image" => 'disabled_motbot',
            "intervention_url" => null,
        ];

        if(!$message->active) {
            return $model;
        }

        $sql = "SELECT *
            FROM mdl_motbot_intervention
            WHERE contextid = :contextid
            AND recipient = :recipient
            AND target = :target
            ORDER BY timecreated DESC
            LIMIT 1";
        $latest_intervention = $DB->get_record_sql($sql, array('contextid' => $this->contextid, 'recipient' => $this->userid, 'target' => $message->target), IGNORE_MISSING);

        if(!$latest_intervention) {
            $model["image"] = 'happy_motbot';
            return $model;
        }

        $model["state"] = \get_string('state:' . $latest_intervention->state, 'motbot');
        $model["hasdata"] = true;
        $model["date"] = userdate($latest_intervention->timemodified);
        if ($latest_intervention->state == \mod_motbot\retention\intervention::INTERVENED || $latest_intervention->state == \mod_motbot\retention\intervention::UNSUCCESSFUL) {
            $model["intervention_url"] = (new \moodle_url('/message/output/popup/notifications.php?notificationid=' . $latest_intervention->message))->out(false);
            $model["image"] = 'unhappy_motbot';
        } else {
            $model["image"] = 'happy_motbot';
        }
        return $model;
    }

}