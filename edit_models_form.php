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
 * Form that lets user choose their preffered motbot settings for a specific course.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");
require_once("locallib.php");
/**
 * Form that lets user choose their preffered motbot settings for a specific course.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_motbot_edit_models_form extends moodleform {
    /**
     * @var array Array of motbot models, for which there are supposed to be individual setting sections in the form.
     */
    private $motbotmodels = null;

    /**
     * Form definition.
     * @return void
     */
    public function definition() {
        $mform = $this->_form;
        // $this->models = $this->_customdata['models'];
        // $this->advice = $this->_customdata['advice'];

        if (!$this->motbot_models) {
            $this->motbot_models = \mod_motbot\manager::get_motbot_models();
        }

        foreach ($this->motbot_models as $motbotmodel) {
            \mod_motbot\manager::add_intervention_settings($mform, \context_system::instance(), $motbotmodel);
        }

        $this->add_action_buttons();
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

        $message = (object) [
            'id' => $data->{$targetname . '_id' . $prediction},
            'motbot' => $data->{$targetname . '_motbot' . $prediction},
            'model' => $data->{$targetname . '_model' . $prediction},
            'active' => $data->{$targetname . '_active' . $prediction},
            'custom' => $data->{$targetname . '_custom' . $prediction},
            'target' => $data->{$targetname . '_target' . $prediction},
            'prediction' => $data->{$targetname . '_prediction' . $prediction},
            'targetname' => $targetname,
            'fullmessagehtml' => $fullmessagehtml,
            'usermodified' => $data->{$targetname . '_usermodified' . $prediction},
            'timemodified' => $data->{$targetname . '_timemodified' . $prediction},
            'timecreated' => $data->{$targetname . '_timecreated' . $prediction},
            'itemid' => $data->{$targetname . '_fullmessagehtml' . $prediction}['itemid'],
        ];

        if ($message->prediction === '') {
            $message->prediction = null;
        }

        if ($message->motbot === '') {
            $message->motbot = null;
        }

        if ($message->custom) {
            $message->subject = $data->{$targetname . '_subject' . $prediction};
            $message->fullmessage = $data->{$targetname . '_fullmessage' . $prediction};
        } else {
            if (is_scalar($prediction)) {
                $message->subject = get_string('mod_form:' . $targetname . '_subject' . '_' . $prediction, 'motbot');
                $message->fullmessage = get_string('mod_form:' . $targetname . '_fullmessage' . '_' . $prediction, 'motbot');
            } else {
                $message->subject = get_string('mod_form:' . $targetname . '_subject', 'motbot');
                $message->fullmessage = get_string('mod_form:' . $targetname . '_fullmessage', 'motbot');
            }
        }

        return $message;
    }
}
