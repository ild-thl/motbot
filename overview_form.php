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
 * Form that lets user choose their preffered global motbot settings.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

/**
 * Form that lets user choose their preffered global motbot settings.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_motbot_overview_form extends moodleform {
    /**
     * @var array Array of analytics models available for a course
     */
    private $models = null;

    /**
     * Form definition.
     * @return void
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Yes, No selector to enable motbot, as default disabled.
        $mform->addElement('selectyesno', 'authorized', get_string('course_settings_form:authorized', 'motbot'));
        $mform->setDefault('authorized', 0);
        $mform->addHelpButton('authorized', 'course_settings_form:authorized', 'motbot');

        // Pefered time selector.
        $mform->addElement('select', 'pref_time', get_string('course_settings_form:pref_time', 'motbot'), [-1 => 'auto', 0 => '0', 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7', 8 => '8', 9 => '9', 10 => '10', 11 => '11', 12 => '12', 13 => '13', 14 => '14', 15 => '15', 16 => '16', 17 => '17', 18 => '18', 19 => '19', 20 => '20', 21 => '21', 22 => '22', 23 => '23']);
        $mform->addHelpButton('pref_time', 'course_settings_form:pref_time', 'motbot');

        $mform->addElement('selectyesno', 'only_weekdays', get_string('course_settings_form:only_weekdays', 'motbot'));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons();
    }


    /**
     * Adds a checkbox for every analytics model.
     *
     * @param object $mform
     * @return void
     */
    private function add_model_settings($mform) {
        $models = $this->get_models();
        foreach ($models as $model) {
            $targetname = mod_motbot_get_name_of_target($model->target);
            $mform->addElement('checkbox', $targetname, get_string('target:' . $targetname . '_short', 'motbot'), '', array('group' => 1), array(0, 1));
            $mform->setDefault($targetname, 1);
        }
    }

    /**
     * Creates a json string, containing information about wich models
     * were disabled by the user in the submitted form.
     *
     * @param object $data Submitted form data.
     * @return string Json String.
     */
    private function get_disabled_models($data) {
        $disabled_models = array();
        $models = $this->get_models();
        foreach ($models as $model) {
            $targetname = mod_motbot_get_name_of_target($model->target);
            if (!property_exists($data, $targetname)) {
                $disabled_models[] = $model->target;
            }
        }
        return json_encode($disabled_models);
    }

    /**
     * Gets all analytics models from the db.
     *
     * @return array
     */
    private function get_models() {
        global $DB;
        if (!$this->models) {
            $sql = "SELECT *
                FROM mdl_analytics_models
                WHERE enabled = 1
                AND target LIKE '%mod_motbot%';";
            $this->models = $DB->get_records_sql($sql);
        }
        return $this->models;
    }


    /**
     * Gets input data of submitted form.
     *
     * @return object
     **/
    public function get_data() {
        $data = parent::get_data();

        if (empty($data)) {
            return false;
        }

        $data->disabled_models = $this->get_disabled_models($data);
        $data->disabled_advice = '[]';

        return $data;
    }
}
