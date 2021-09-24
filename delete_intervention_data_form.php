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
 * Form that lets user choose their preffered motbot settings.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//moodleform is defined in formslib.php
require_once("$CFG->libdir/formslib.php");

class mod_motbot_delete_intervention_data_form extends moodleform {

    //Add elements to form
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'recipient');
        $mform->setType('recipient', PARAM_INT);
        $mform->addElement('hidden', 'contextid');
        $mform->setType('contextid', PARAM_INT);

        $mform->addElement('header', 'delete_header', 'Advanced_options');
        $mform->setExpanded('delete_header', false);
        $mform->addElement('submit', 'submitbutton', 'Delete intervention data');
    }

    //Custom validation
    function validation($data, $files) {
        return array();
    }
}