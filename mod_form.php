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
 * Form definition, used to create and update a motbot activity.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/motbot/lib.php');
require_once($CFG->dirroot . '/mod/motbot/locallib.php');
require_once($CFG->dirroot . '/mod/motbot/db/analytics.php');

/**
 * Form definition, used to create and update a motbot activity.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_motbot_mod_form extends moodleform_mod {

    /**
     * @var array Array of motbot models, for which there are supposed to be individual setting sections in the form.
     */
    private $motbot_models = array();

    /**
     * Form definition.
     * @return void
     */
    public function definition() {
        $mform = &$this->_form;

        $mform->addElement('text', 'name', get_string('mod_form:motbot_name', 'motbot'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', 'MotBot');
        $mform->addRule('name', null, 'required', null, 'client');

        $mform->addElement('selectyesno', 'active', get_string('mod_form:active', 'motbot'));
        $mform->setDefault('active', 1);
        $mform->addHelpButton('active', 'mod_form:active', 'motbot');

        $this->standard_intro_elements();
        $mform->setDefault('intro', array('text' => \get_string('mod_form:intro', 'motbot'), 'format' => FORMAT_HTML));

        if (!$this->motbot_models || empty($this->motbot_models)) {
            $this->motbot_models = \mod_motbot\manager::get_motbot_models($this->current->instance);
        }

        foreach ($this->motbot_models as $motbot_model) {
            \mod_motbot\manager::add_intervention_settings($mform, $this->context, $motbot_model);
        }

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    /**
     * Enforce defaults here.
     *
     * @param array $defaultvalues Form defaults
     * @return void
     **/
    public function data_preprocessing(&$defaultvalues) {
        if (!$this->motbot_models || empty($this->motbot_models)) {
            $this->motbot_models = \mod_motbot\manager::get_motbot_models();
        }

        foreach ($this->motbot_models as $motbot_model) {
            $target_name = $motbot_model->targetname;
            $draftitemid = file_get_submitted_draft_itemid($target_name . '_fullmessagehtml' . $motbot_model->prediction);
            $defaultvalues[$target_name . '_id' . $motbot_model->prediction] = $motbot_model->id;
            $defaultvalues[$target_name . '_motbot' . $motbot_model->prediction] = $motbot_model->motbot;
            $defaultvalues[$target_name . '_model' . $motbot_model->prediction] = $motbot_model->model;
            $defaultvalues[$target_name . '_active' . $motbot_model->prediction] = $motbot_model->active;
            $defaultvalues[$target_name . '_custom' . $motbot_model->prediction] = $motbot_model->custom;
            $defaultvalues[$target_name . '_target' . $motbot_model->prediction] = $motbot_model->target;
            $defaultvalues[$target_name . '_prediction' . $motbot_model->prediction] = $motbot_model->prediction;
            $defaultvalues[$target_name . '_subject' . $motbot_model->prediction] = $motbot_model->subject;
            $defaultvalues[$target_name . '_fullmessage' . $motbot_model->prediction] = $motbot_model->fullmessage;
            $defaultvalues[$target_name . '_fullmessagehtml' . $motbot_model->prediction]['text']   = file_prepare_draft_area(
                $draftitemid,
                $this->context->id,
                'mod_motbot',
                'attachment',
                0,
                mod_motbot_get_editor_options($this->context),
                $motbot_model->fullmessagehtml
            );
            $defaultvalues[$target_name . '_fullmessagehtml' . $motbot_model->prediction]['itemid'] = $draftitemid;
            $defaultvalues[$target_name . '_usermodified' . $motbot_model->prediction] = $motbot_model->usermodified;
            $defaultvalues[$target_name . '_timemodified' . $motbot_model->prediction] = $motbot_model->timemodified;
            $defaultvalues[$target_name . '_timecreated' . $motbot_model->prediction] = $motbot_model->timecreated;
        }
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

        $data->motbot_models = array();

        foreach ($this->motbot_models as $motbot_model) {
            $data->motbot_models[] = $this->get_message_data($motbot_model->targetname, $motbot_model->prediction, $data);
        }

        return $data;
    }

    /**
     * Get input data of a submitted motbot_model section.
     *
     * @param string $target_name Name of corresponfing model target.
     * @param object $data Form input data.
     * @return object
     **/
    private function get_message_data($target_name, $prediction, $data) {
        // Filter out badly formatted break lines.
        $fullmessagehtml = $data->{$target_name . '_fullmessagehtml' . $prediction}['text'];
        $fullmessagehtml = str_replace("<p><br></p>", "<br>", $fullmessagehtml);
        $fullmessagehtml = str_replace("<p></p>", "<br>", $fullmessagehtml);

        return (object) [
            'id' => $data->{$target_name . '_id' . $prediction},
            'motbot' => $data->{$target_name . '_motbot' . $prediction},
            'model' => $data->{$target_name . '_model' . $prediction},
            'active' => $data->{$target_name . '_active' . $prediction},
            'custom' => $data->{$target_name . '_custom' . $prediction},
            'target' => $data->{$target_name . '_target' . $prediction},
            'prediction' => $data->{$target_name . '_prediction' . $prediction},
            'targetname' => $target_name,
            'subject' => $data->{$target_name . '_subject' . $prediction},
            'fullmessage' => $data->{$target_name . '_fullmessage' . $prediction},
            'fullmessagehtml' => $fullmessagehtml,
            'usermodified' => $data->{$target_name . '_usermodified' . $prediction},
            'timemodified' => $data->{$target_name . '_timemodified' . $prediction},
            'timecreated' => $data->{$target_name . '_timecreated' . $prediction},
        ];
    }
}
