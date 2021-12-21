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

use core_analytics\prediction;

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
            $this->get_motbot_models();
        }

        foreach ($this->motbot_models as $motbot_model) {
            $this->add_intervention_settings($motbot_model);
        }

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    /**
     * Adds a new section to the form. This section allows the use of specific analytics models in a course and to edit the message templates of corresponding interventions.
     *
     * @param object $model
     * @return void
     */
    private function add_intervention_settings($model) {
        $mform = &$this->_form;

        $target_name = \mod_motbot_get_name_of_target($model->target);

        $mform->addElement('header', $target_name . '_header' . $model->prediction, get_string('mod_form:' . $target_name . '_header', 'motbot') . $model->prediction_description);

        $mform->addElement('hidden', $target_name . '_id' . $model->prediction);
        $mform->setType($target_name . '_id' . $model->prediction, PARAM_INT);

        $mform->addElement('hidden', $target_name . '_motbot' . $model->prediction);
        $mform->setType($target_name . '_motbot' . $model->prediction, PARAM_INT);

        $mform->addElement('hidden', $target_name . '_model' . $model->prediction);
        $mform->setType($target_name . '_model' . $model->prediction, PARAM_INT);

        $mform->addElement('hidden', $target_name . '_target' . $model->prediction);
        $mform->setType($target_name . '_target' . $model->prediction, PARAM_TEXT);

        $mform->addElement('selectyesno', $target_name . '_active' . $model->prediction, get_string('mod_form:active', 'motbot'));
        $mform->addHelpButton($target_name . '_active' . $model->prediction, 'mod_form:active', 'motbot');

        $mform->addElement('selectyesno', $target_name . '_custom' . $model->prediction, get_string('mod_form:custom', 'motbot'));
        $mform->addHelpButton($target_name . '_custom' . $model->prediction, 'mod_form:custom', 'motbot');


        $mform->addElement('text', $target_name . '_subject' . $model->prediction, get_string('mod_form:subject', 'motbot'), array('size' => '64'));
        $mform->setType($target_name . '_subject' . $model->prediction, PARAM_TEXT);
        $mform->disabledIf($target_name . '_subject' . $model->prediction, $target_name . '_custom' . $model->prediction, 'eq', 0);

        $mform->addElement('textarea', $target_name . '_fullmessage' . $model->prediction, get_string('mod_form:fullmessage', 'motbot'), 'wrap="virtual" rows="10" cols="150"');
        $mform->setType($target_name . '_fullmessage' . $model->prediction, PARAM_TEXT);
        $mform->disabledIf($target_name . '_fullmessage' . $model->prediction, $target_name . '_custom' . $model->prediction, 'eq', 0);

        $mform->addElement('editor', $target_name . '_fullmessagehtml' . $model->prediction, get_string('mod_form:fullmessagehtml', 'motbot'), array('rows' => 15), mod_motbot_get_editor_options($this->context));
        $mform->setType($target_name . '_fullmessagehtml' . $model->prediction, PARAM_RAW);
        $mform->disabledIf($target_name . '_fullmessagehtml' . $model->prediction, $target_name . '_custom' . $model->prediction, 'eq', 0);

        $mform->addElement('hidden', $target_name . '_usermodified' . $model->prediction);
        $mform->setType($target_name . '_usermodified' . $model->prediction, PARAM_INT);

        $mform->addElement('hidden', $target_name . '_timemodified' . $model->prediction);
        $mform->setType($target_name . '_timemodified' . $model->prediction, PARAM_INT);

        $mform->addElement('hidden', $target_name . '_timecreated' . $model->prediction);
        $mform->setType($target_name . '_timecreated' . $model->prediction, PARAM_INT);

        $mform->addElement('hidden', $target_name . '_prediction' . $model->prediction);
        $mform->setType($target_name . '_prediction' . $model->prediction, PARAM_INT);
    }



    /**
     * Creates default values for valid motbot models or gets prevoius settings, if they exist.
     *
     * @return array
     */
    private function get_motbot_models() {
        global $DB;

        // Get previous model settings for this specific motbot.
        $motbot_models = $DB->get_records('motbot_model', array('motbot' => $this->current->instance));

        // Get all available motbot models.
        $sql = "SELECT *
                FROM mdl_analytics_models
                WHERE enabled = 1
                AND target LIKE '%mod_motbot%';";
        $models = $DB->get_records_sql($sql);

        // Create deault values for models, that are valid for this course.
        foreach ($models as $model) {
            if (!$model->target::custom_intervention()) {
                continue;
            }
            // Get variants for possible prediction results.
            /** @var \mod_motbot\analytics\target\motbot_target $target */
            $target = \core_analytics\manager::get_target($model->target);
            $classes = $model->target::get_classes();
            $predictions = array();
            $ignored = array();
            if ($target instanceof \core_analytics\local\target\discrete) {
                $ignored = $target->ignored_predicted_classes();

                foreach ($classes as $class) { // Skip classes, for which there won't be any predictions made in the future.
                    if (!in_array($class, $ignored)) {
                        $predictions[] = $class;
                    }
                }
            } else {
                $predictions = $classes;
            }

            foreach ($predictions as $count => $prediction) {
                // Skip models, for which there are already previous records.
                $exists = false;
                foreach ($motbot_models as $motbot_model) {
                    if ($model->target == $motbot_model->target && $prediction == $motbot_model->prediction) {
                        $exists = true;
                        $motbot_model->prediction_description = count($predictions) > 1 ? '(' . (((int)$count) + 1) . '/' . count($predictions) . ')' : '';
                        $this->motbot_models[] = $motbot_model;
                        break;
                    }
                }

                if ($exists) {
                    continue;
                }

                $target_name = \mod_motbot_get_name_of_target($model->target);

                // Set default values.
                $this->motbot_models[] = (object) [
                    'id' => null,
                    'motbot' => $this->current->instance,
                    'model' => $model->id,
                    'active' => 1,
                    'custom' => 0,
                    'target' => $model->target,
                    'targetname' => null,
                    'prediction' => $prediction,
                    'prediction_description' => count($predictions) > 1 ? ' (' . (((int)$count) + 1) . '/' . count($predictions) . ')' : '',
                    'subject' => \get_string('mod_form:' . $target_name . '_subject', 'motbot'),
                    'fullmessage' => \get_string('mod_form:' . $target_name . '_fullmessage', 'motbot'),
                    'fullmessagehtml' => \get_string('mod_form:' . $target_name . '_fullmessagehtml', 'motbot'),
                    'attachementuri' => null,
                    'usermodified' => null,
                    'timecreated' => null,
                    'timemodified' => null,
                ];
            }
        }

        // Set targetname, and prediction_name property where it isn't already set.
        foreach ($this->motbot_models as $motbot_model) {
            if (property_exists($motbot_model, 'targetname') && $motbot_model->targetname) {
                continue;
            }

            $target_name = \mod_motbot_get_name_of_target($motbot_model->target);
            $motbot_model->targetname = $target_name;
        }

        return $this->motbot_models;
    }

    /**
     * Enforce defaults here.
     *
     * @param array $defaultvalues Form defaults
     * @return void
     **/
    public function data_preprocessing(&$defaultvalues) {
        if (!$this->motbot_models || empty($this->motbot_models)) {
            $this->get_motbot_models();
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
