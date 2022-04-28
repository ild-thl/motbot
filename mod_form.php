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
    die('Direct access to this script is forbidden.');    // It must be included from a Moodle page
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
    private $motbotmodels = array();

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
        $mform->setDefault('intro', array('text' => get_string('mod_form:intro', 'motbot'), 'format' => FORMAT_HTML));

        if (!$this->motbot_models || empty($this->motbot_models)) {
            $this->motbot_models = \mod_motbot\manager::get_motbot_models($this->current->instance);
        }

        foreach ($this->motbot_models as $motbotmodel) {
            \mod_motbot\manager::add_intervention_settings($mform, $this->context, $motbotmodel);
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

        foreach ($this->motbot_models as $motbotmodel) {
            $targetname = $motbotmodel->targetname;
            $draftitemid = file_get_submitted_draft_itemid($targetname . '_fullmessagehtml' . $motbotmodel->prediction);
            $defaultvalues[$targetname . '_id' . $motbotmodel->prediction] = $motbotmodel->id;
            $defaultvalues[$targetname . '_motbot' . $motbotmodel->prediction] = $motbotmodel->motbot;
            $defaultvalues[$targetname . '_model' . $motbotmodel->prediction] = $motbotmodel->model;
            $defaultvalues[$targetname . '_active' . $motbotmodel->prediction] = $motbotmodel->active;
            $defaultvalues[$targetname . '_custom' . $motbotmodel->prediction] = $motbotmodel->custom;
            $defaultvalues[$targetname . '_target' . $motbotmodel->prediction] = $motbotmodel->target;
            $defaultvalues[$targetname . '_prediction' . $motbotmodel->prediction] = $motbotmodel->prediction;
            $defaultvalues[$targetname . '_subject' . $motbotmodel->prediction] = $motbotmodel->subject;
            $defaultvalues[$targetname . '_fullmessage' . $motbotmodel->prediction] = $motbotmodel->fullmessage;
            $defaultvalues[$targetname . '_fullmessagehtml' . $motbotmodel->prediction]['text']   = file_prepare_draft_area(
                $draftitemid,
                $this->context->id,
                'mod_motbot',
                'attachment',
                0,
                mod_motbot_get_editor_options($this->context),
                $motbotmodel->fullmessagehtml
            );
            $defaultvalues[$targetname . '_fullmessagehtml' . $motbotmodel->prediction]['itemid'] = $draftitemid;
            $defaultvalues[$targetname . '_usermodified' . $motbotmodel->prediction] = $motbotmodel->usermodified;
            $defaultvalues[$targetname . '_timemodified' . $motbotmodel->prediction] = $motbotmodel->timemodified;
            $defaultvalues[$targetname . '_timecreated' . $motbotmodel->prediction] = $motbotmodel->timecreated;
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

        foreach ($this->motbot_models as $motbotmodel) {
            $data->motbot_models[] = $this->get_message_data($motbotmodel->targetname, $motbotmodel->prediction, $data);
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
    private function get_message_data($targetname, $prediction, $data) {
        // Filter out badly formatted break lines.
        $fullmessagehtml = $data->{$targetname . '_fullmessagehtml' . $prediction}['text'];
        $fullmessagehtml = str_replace("<p><br></p>", "<br>", $fullmessagehtml);
        $fullmessagehtml = str_replace("<p></p>", "<br>", $fullmessagehtml);

        return (object) [
            'id' => $data->{$targetname . '_id' . $prediction},
            'motbot' => $data->{$targetname . '_motbot' . $prediction},
            'model' => $data->{$targetname . '_model' . $prediction},
            'active' => $data->{$targetname . '_active' . $prediction},
            'custom' => $data->{$targetname . '_custom' . $prediction},
            'target' => $data->{$targetname . '_target' . $prediction},
            'prediction' => $data->{$targetname . '_prediction' . $prediction},
            'targetname' => $targetname,
            'subject' => $data->{$targetname . '_subject' . $prediction},
            'fullmessage' => $data->{$targetname . '_fullmessage' . $prediction},
            'fullmessagehtml' => $fullmessagehtml,
            'usermodified' => $data->{$targetname . '_usermodified' . $prediction},
            'timemodified' => $data->{$targetname . '_timemodified' . $prediction},
            'timecreated' => $data->{$targetname . '_timecreated' . $prediction},
        ];
    }
}
