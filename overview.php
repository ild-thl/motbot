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
 * Prints an overview of all certificates a student has reached.
 *
 * @package     mod_ilddigitalcert
 * @copyright   2020 ILD TH Lübeck <dev.ild@th-luebeck.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once(__DIR__. '/overview_form.php');
require_once($CFG->dirroot.'/mod/motbot/locallib.php');

require_login();
$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url('/mod/motbot/overview.php');
$PAGE->set_pagelayout('admin');
$PAGE->set_title(format_string(get_string('pluginname', 'motbot')));
$PAGE->set_heading(get_string('pluginname', 'motbot'));

require_login();

if (isguestuser()) {
    redirect($CFG->wwwroot.'/login/');
}


$motbot_user = $DB->get_record('motbot_user', array('user' => $USER->id), '*');

if(!$motbot_user) {
    $motbot_user = (object) [
        'id' => null,
        'user' => $USER->id,
        'authorized' => 0,
        'allow_teacher_involvement' => 0,
        'usermodified' => null,
        'timecreated' => null,
        'timemodified' => null,
    ];
}

$toform = (object) [
    'authorized' => $motbot_user->authorized,
    'allow_teacher_involvement' => $motbot_user->allow_teacher_involvement,
];

//Instantiate simplehtml_form
$mform = new mod_motbot_overview_form();

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
    $url = $CFG->wwwroot.'/my/';
    redirect($url);
} else if ($fromform = $mform->get_data()) {
    //In this case you process validated data. $mform->get_data() returns data posted in form.
    $time = time();
    $form_data = $mform->get_data();
    $motbot_user->authorized = $form_data->authorized;
    $motbot_user->allow_teacher_involvement = $form_data->allow_teacher_involvement;
    $motbot_user->usermodified = $USER->id;
    $motbot_user->timemodified = $time;

    if(!$motbot_user->timecreated) {
        $motbot_user->timecreated = $time;
    }

    if(!$motbot_user->id) {
        $DB->insert_record('motbot_user', $motbot_user);
    } else {
        $DB->update_record('motbot_user', $motbot_user);
    }

    $url = $CFG->wwwroot.'/my/';
    redirect($url);
} else {
    // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('pluginname', 'motbot'));

    //Set default data (if any)
    $mform->set_data($toform);
    //displays the form
    $mform->display();

    echo html_writer::tag('br', '');
    echo html_writer::tag('h2', 'Interventions');
    echo mod_motbot_get_interventions_table($USER->id);



    // echo html_writer::tag('br', '');
    // echo html_writer::tag('h2', 'Predictions');
    // echo mod_motbot_get_predictions_table($predicitons);


    echo $OUTPUT->footer();
}

