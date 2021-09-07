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
 * Library of interface functions and constants.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/motbot/lib.php');
require_once($CFG->dirroot.'/mod/motbot/locallib.php');
require_once($CFG->dirroot.'/mod/motbot/db/analytics.php');

class mod_motbot_mod_form extends moodleform_mod {

    private $messages = array();

    public function definition() {
        global $CFG, $DB, $OUTPUT;

        $mform =& $this->_form;

        $mform->addElement('text', 'name', get_string('mod_form:motbot_name', 'motbot'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->setDefault('name', 'Motbot');
        $mform->addRule('name', null, 'required', null, 'client');

        $ynoptions = array(0 => get_string('mod_form:paused', 'motbot'),
                           1 => get_string('mod_form:active', 'motbot'));
        $mform->addElement('select', 'usecode', get_string('mod_form:usecode', 'motbot'), $ynoptions);
        $mform->setDefault('usecode', 0);
        $mform->addHelpButton('usecode', 'mod_form:usecode', 'motbot');

        $this->standard_intro_elements();
        $mform->setDefault('intro', array('text' => \get_string('mod_form:intro', 'motbot'), 'format' => FORMAT_HTML));

        if(!$this->messages || empty($this->messages)) {
            $this->get_messages();
        }

        foreach ($this->messages as $message) {
            $this->add_intervention_settings($message);
        }

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    private function add_intervention_settings($message) {
        $mform =& $this->_form;

        $target_name = \mod_motbot_get_name_of_target($message->target);

        $mform->addElement('header', $target_name.'_header', get_string('mod_form:' . $target_name . '_header', 'motbot'));

        $mform->addElement('hidden', $target_name.'_id');
        $mform->setType($target_name.'_id', PARAM_INT);

        $mform->addElement('hidden', $target_name.'_motbot');
        $mform->setType($target_name.'_motbot', PARAM_INT);

        $mform->addElement('hidden', $target_name.'_target');
        $mform->setType($target_name.'_target', PARAM_TEXT);

        $mform->addElement('selectyesno', $target_name.'_active', get_string('mod_form:active', 'motbot'));
        $mform->addHelpButton($target_name.'_active', 'mod_form:active', 'motbot');

        $mform->addElement('text', $target_name.'_subject', get_string('mod_form:subject', 'motbot'), array('size'=>'64'));
        $mform->setType($target_name.'_subject', PARAM_TEXT);
        $mform->addElement('textarea', $target_name.'_fullmessage', get_string('mod_form:fullmessage', 'motbot'), 'wrap="virtual" rows="10" cols="150"');
        $mform->setType($target_name.'_fullmessage', PARAM_TEXT);


        $mform->addElement('editor', $target_name.'_fullmessagehtml', get_string('mod_form:fullmessagehtml', 'motbot'), array('rows' => 15), mod_motbot_get_editor_options($this->context));
        $mform->setType($target_name.'_fullmessagehtml', PARAM_RAW);

        $mform->addElement('textarea', $target_name.'_smallmessage', get_string('mod_form:smallmessage', 'motbot'), 'wrap="virtual" rows="5" cols="150"');
        $mform->setType($target_name.'_smallmessage', PARAM_TEXT);

        $mform->addElement('hidden', $target_name.'_usermodified');
        $mform->setType($target_name.'_usermodified', PARAM_INT);

        $mform->addElement('hidden', $target_name.'_timemodified');
        $mform->setType($target_name.'_timemodified', PARAM_INT);

        $mform->addElement('hidden', $target_name.'_timecreated');
        $mform->setType($target_name.'_timecreated', PARAM_INT);
    }

    private function get_messages() {
        global $DB;

        $this->messages = $DB->get_records('motbot_message', array('motbot' => $this->current->instance));

        $sql = "SELECT *
                FROM mdl_analytics_models
                WHERE enabled = 1
                AND target LIKE '%mod_motbot%';";
        $models = $DB->get_records_sql($sql);

        foreach ($models as $model) {
            $exists = false;
            foreach ($this->messages as $message) {
                if($model->target == $message->target) {
                    $exists = true;
                    break;
                }
            }

            if($exists || $model->target::uses_insights()) {
                continue;
            }

            $target_name = \mod_motbot_get_name_of_target($model->target);

            $this->messages[] = (object) [
                'id' => null,
                'motbot' => $this->current->instance,
                'active' => 1,
                'target' => $model->target,
                'targetname' => null,
                'subject' => \get_string('mod_form:' . $target_name . '_subject', 'motbot'),
                'fullmessage' => \get_string('mod_form:' . $target_name . '_fullmessage', 'motbot'),
                'fullmessageformat' => FORMAT_HTML,
                'fullmessagehtml' => \get_string('mod_form:' . $target_name . '_fullmessagehtml', 'motbot'),
                'smallmessage' => null,
                'attachementuri' => null,
                'usermodified' => null,
                'timecreated' => null,
                'timemodified' => null,
            ];

        }


        foreach($this->messages as $message) {
            if(property_exists($message, 'targetname') && $message->targetname) {
                continue;
            }

            $target_name = \mod_motbot_get_name_of_target($message->target);
            $message->targetname = $target_name;
        }

        return $this->messages;
    }

        /**
     * Enforce defaults here.
     *
     * @param array $defaultvalues Form defaults
     * @return void
     **/
    public function data_preprocessing(&$defaultvalues) {
        $this->get_messages();

        foreach($this->messages as $message) {
            $target_name = $message->targetname;
            $draftitemid = file_get_submitted_draft_itemid($target_name . '_fullmessagehtml');
            $defaultvalues[$target_name . '_id'] = $message->id;
            $defaultvalues[$target_name . '_motbot'] = $message->motbot;
            $defaultvalues[$target_name . '_active'] = $message->active;
            $defaultvalues[$target_name . '_target'] = $message->target;
            $defaultvalues[$target_name . '_subject'] = $message->subject;
            $defaultvalues[$target_name . '_fullmessage'] = $message->fullmessage;
            $defaultvalues[$target_name . '_fullmessagehtml']['format'] = $message->fullmessageformat;
            $defaultvalues[$target_name . '_fullmessagehtml']['text']   = file_prepare_draft_area($draftitemid, $this->context->id, 'mod_motbot',
                'attachment', 0, mod_motbot_get_editor_options($this->context), $message->fullmessagehtml);
            $defaultvalues[$target_name . '_fullmessagehtml']['itemid'] = $draftitemid;
            $defaultvalues[$target_name . '_fullmessageformat'] = $message->fullmessageformat;
            $defaultvalues[$target_name . '_smallmessage'] = $message->smallmessage;
            $defaultvalues[$target_name . '_usermodified'] = $message->usermodified;
            $defaultvalues[$target_name . '_timemodified'] = $message->timemodified;
            $defaultvalues[$target_name . '_timecreated'] = $message->timecreated;
        }
    }

    public function get_data() {
        $data = parent::get_data();

        if (empty($data)) {
            return false;
        }

        $data->messages = array();

        foreach($this->messages as $message) {
            $data->messages[] = $this->get_message_data($message->targetname, $data);
        }

        return $data;
    }

    private function get_message_data($target_name, $data) {
        return (object) [
            'id' => $data->{$target_name . '_id'},
            'motbot' => $data->{$target_name . '_motbot'},
            'active' => $data->{$target_name . '_active'},
            'target' => $data->{$target_name . '_target'},
            'targetname' => $target_name,
            'subject' => $data->{$target_name . '_subject'},
            'fullmessage' => $data->{$target_name . '_fullmessage'},
            'fullmessageformat' => $data->{$target_name . '_fullmessagehtml'}['format'],
            'fullmessagehtml' => $data->{$target_name . '_fullmessagehtml'}['text'],
            'smallmessage' => $data->{$target_name . '_smallmessage'},
            'attachementuri' => null,
            'usermodified' => $data->{$target_name . '_usermodified'},
            'timemodified' => $data->{$target_name . '_timemodified'},
            'timecreated' => $data->{$target_name . '_timecreated'},
        ];
    }
}