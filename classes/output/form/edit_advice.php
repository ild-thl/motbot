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
 * Form for editting an advice defintion
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\output\form;

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * Form for editting an advice defintion
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edit_advice extends \moodleform {

    /**
     * Form definition
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('html', '<h2>' . get_string('advice:edit', 'mod_motbot', $this->_customdata['advice']) . '</h2><p>' . $this->_customdata['adviceclass'] . '</p></br>');

        $mform->addElement('advcheckbox', 'enabled', get_string('enabled', 'tool_analytics'));

        // Targets.
        $targets = array();
        foreach ($this->_customdata['targets'] as $classname => $target) {
            $optionname = \tool_analytics\output\helper::class_to_option($classname);
            $targets[] = $optionname;
        }

        $availabletargets = array();
        foreach ($this->_customdata['available_targets'] as $classname => $target) {
            $optionname = \tool_analytics\output\helper::class_to_option($classname);
            $availabletargets[$optionname] = $target->get_name();
        }

        $mform->addElement('select', 'targets', get_string('advice:targets', 'mod_motbot'), $availabletargets);
        $mform->getElement('targets')->setMultiple(true);
        $mform->setDefault('targets', $targets);

        if (!empty($this->_customdata['id'])) {
            $mform->addElement('hidden', 'id', $this->_customdata['id']);
            $mform->setType('id', PARAM_INT);

            $mform->addElement('hidden', 'action', 'edit');
            $mform->setType('action', PARAM_ALPHANUMEXT);
        }

        $this->add_action_buttons();
    }

    /**
     * Form validation
     *
     * @param array $data data from the form.
     * @param array $files files uploaded.
     *
     * @return array of errors.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (empty($data['targets'])) {
            $errors['targets'] = get_string('errornotargets', 'analytics');
        } else {
            foreach ($data['targets'] as $target) {
                $realtargetname = \tool_analytics\output\helper::option_to_class($target);
                if (\core_analytics\manager::is_valid($realtargetname, '\core_analytics\local\target\base') === false) {
                    $errors['targets'] = get_string('errorinvalidtarget', 'analytics', $realtargetname);
                }
            }
        }

        return $errors;
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

        $callable = array('\tool_analytics\output\helper', 'option_to_class');
        $data->targets = array_map($callable, $data->targets);

        return $data;
    }
}
