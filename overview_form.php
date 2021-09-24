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

class mod_motbot_overview_form extends moodleform {

    //Add elements to form
    public function definition() {
        global $CFG;

        $mform = $this->_form;

        $ynoptions = array(0 => get_string('course_settings_form:prohibit', 'motbot'),
                           1 => get_string('course_settings_form:authorize', 'motbot'));
        $mform->addElement('select', 'authorized', get_string('course_settings_form:authorized', 'motbot'), $ynoptions);
        $mform->setDefault('authorized', 0);
        $mform->addHelpButton('authorized', 'course_settings_form:authorized', 'motbot');

        $mform->addElement('selectyesno', 'allow_teacher_involvement', get_string('course_settings_form:allow_teacher_involvement', 'motbot'));
        $mform->addHelpButton('allow_teacher_involvement', 'course_settings_form:allow_teacher_involvement', 'motbot');

        $mform->addElement('header', 'advice_settings', get_string('user_settings_form:advice_settings', 'motbot'), '', array('group' => 1, 'checked' => true), array(0, 1));
        $mform->addElement('checkbox', 'allow_course_completion', get_string('user_settings_form:allow_course_completion', 'motbot'), '', array('group' => 1), array(0, 1));
        $mform->addElement('checkbox', 'allow_feedback', get_string('user_settings_form:allow_feedback', 'motbot'), '', array('group' => 1), array(0, 1));
        $mform->addElement('checkbox', 'allow_recent_activities', get_string('user_settings_form:allow_recent_activities', 'motbot'), '', array('group' => 1), array(0, 1));
        $mform->addElement('checkbox', 'allow_recent_forum_activity', get_string('user_settings_form:allow_recent_forum_activity', 'motbot'), '', array('group' => 1), array(0, 1));
        $mform->addElement('checkbox', 'allow_recommended_discussion', get_string('user_settings_form:allow_recommended_discussion', 'motbot'), '', array('group' => 1), array(0, 1));
        $mform->addElement('checkbox', 'allow_visit_course', get_string('user_settings_form:allow_visit_course', 'motbot'), '', array('group' => 1), array(0, 1));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons();
    }

    //Custom validation
    function validation($data, $files) {
        return array();
    }
}