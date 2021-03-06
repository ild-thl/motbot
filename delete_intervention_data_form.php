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
 * Form that lets user decide to delete their intervention records.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->libdir/formslib.php");

/**
 * Form that lets user decide to delete their intervention records.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_motbot_delete_intervention_data_form extends moodleform {

    /**
     * Form definition.
     * @return void
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'recipient');
        $mform->setType('recipient', PARAM_INT);
        $mform->addElement('hidden', 'contextid');
        $mform->setType('contextid', PARAM_INT);

        $mform->addElement('header', 'delete_header', get_string('settings:advanced_options', 'motbot'));
        $mform->setExpanded('delete_header', false);
        $mform->addElement('submit', 'submitbutton', get_string('settings:deleteinterventiondata', 'motbot'));
    }
}
