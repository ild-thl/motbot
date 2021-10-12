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
 * Shows an overview of all motbot activity of a user.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/motbot/locallib.php');
require_once($CFG->dirroot . '/mod/motbot/db/analytics.php');

/**
 * Shows an overview of all motbot activity of a user.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_motbot_overview {
    /**
     * @var int Id of current user.
     */
    private $userid;

    /**
     * @var string URL for a settings page.
     */
    public $settings_url;

    /**
     * Object definition.
     *
     * @param int $userid
     * @return void
     */
    public function __construct($userid) {
        global $DB, $CFG;

        $this->settings_url = $CFG->wwwroot.'/mod/motbot/overview_settings.php';
        $this->userid = $userid;
    }

    /**
     * Returns html of this page.
     *
     * @return string
     */
    public function render() {
        global $OUTPUT;

        return $OUTPUT->render_from_template('mod_motbot/user_view', $this->get_contextinfo());
    }

    /**
     * Gets placeholder information for a mustache template.
     *
     * @return array
     */
    private function get_contextinfo() {
        global $DB;

        $amodels = [
            '\mod_motbot\analytics\target\no_recent_accesses',
            '\mod_motbot\analytics\target\low_social_presence',
            '\mod_motbot\analytics\target\course_dropout',
            ];
        $models = array();

        foreach($amodels as $target) {
            $models[] = $this->get_model_data($target);
        }


        function sort_models_by_enable($a, $b) {
            if($a["enabled"] == $b["enabled"]) return 0;
            return (!$b["enabled"] && $b["enabled"]) ? -1 : 1;
        }

        usort($models, "sort_models_by_enable");


        $contextinfo = [
            'settings_url' => $this->settings_url,
            'models' => $models,
            'interventions_table' => mod_motbot_get_interventions_table($this->userid, null, true),
        ];

        return $contextinfo;
    }


    /**
     * Gets data, that is supposed to be displayed per model.
     *
     * @return array
     */
    public function get_model_data($target) {
        global $DB;

        $target_name = mod_motbot_get_name_of_target($target);
        $model = [
            "name" => \get_string('target:' . $target_name . '_neutral', 'motbot'),
            "enabled" => true,
            "hasdata" => false,
            "state" => '',
            "date" => null,
            "image" => 'disabled_motbot',
            "intervention_url" => null,
        ];

        $sql = "SELECT *
            FROM mdl_motbot_intervention
            WHERE recipient = :recipient
            AND target = :target
            ORDER BY timecreated DESC
            LIMIT 1";
        $latest_intervention = $DB->get_record_sql($sql, array('recipient' => $this->userid, 'target' => $target), IGNORE_MISSING);

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