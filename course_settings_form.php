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

/**
 * Form that lets user choose their preffered motbot settings for a specific course.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_motbot_course_settings_form extends moodleform {

    /**
     * Add elements to form.
     */
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        // Yes, No selector to enable motbot, as default disabled.
        $mform->addElement('selectyesno', 'authorized', get_string('course_settings_form:authorized', 'motbot'));
        $mform->setDefault('authorized', 0);
        $mform->addHelpButton('authorized', 'course_settings_form:authorized', 'motbot');

        // Yes, No Selector to enable involvement of teachers if needed.
        $mform->addElement('selectyesno', 'allow_teacher_involvement', get_string('course_settings_form:allow_teacher_involvement', 'motbot'));
        $mform->addHelpButton('allow_teacher_involvement', 'course_settings_form:allow_teacher_involvement', 'motbot');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        // Submit and cancel button.
        $this->add_action_buttons();
    }
}