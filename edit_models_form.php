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

//moodleform is defined in formslib.php
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
    private $motbot_models = null;

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

        foreach ($this->motbot_models as $motbot_model) {
            \mod_motbot\manager::add_intervention_settings($mform, \context_system::instance(), $motbot_model);
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

        $message = (object) [
            'id' => $data->{$target_name . '_id' . $prediction},
            'motbot' => $data->{$target_name . '_motbot' . $prediction},
            'model' => $data->{$target_name . '_model' . $prediction},
            'active' => $data->{$target_name . '_active' . $prediction},
            'custom' => $data->{$target_name . '_custom' . $prediction},
            'target' => $data->{$target_name . '_target' . $prediction},
            'prediction' => $data->{$target_name . '_prediction' . $prediction},
            'targetname' => $target_name,
            'fullmessagehtml' => $fullmessagehtml,
            'usermodified' => $data->{$target_name . '_usermodified' . $prediction},
            'timemodified' => $data->{$target_name . '_timemodified' . $prediction},
            'timecreated' => $data->{$target_name . '_timecreated' . $prediction},
            'itemid' => $data->{$target_name . '_fullmessagehtml' . $prediction}['itemid'],
        ];

        if ($message->prediction === '') {
            $message->prediction = null;
        }

        if ($message->motbot === '') {
            $message->motbot = null;
        }

        if ($message->custom) {
            $message->subject = $data->{$target_name . '_subject' . $prediction};
            $message->fullmessage = $data->{$target_name . '_fullmessage' . $prediction};
        } else {
            if (is_scalar($prediction)) {
                $message->subject = \get_string('mod_form:' . $target_name . '_subject' . '_' . $prediction, 'motbot');
                $message->fullmessage = \get_string('mod_form:' . $target_name . '_fullmessage' . '_' . $prediction, 'motbot');
            } else {
                $message->subject = \get_string('mod_form:' . $target_name . '_subject', 'motbot');
                $message->fullmessage = \get_string('mod_form:' . $target_name . '_fullmessage', 'motbot');
            }
        }

        return $message;
    }
}
