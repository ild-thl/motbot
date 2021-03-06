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
 * Shows form to make general non course specific motbot settings.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../config.php');
require_once(__DIR__.'/lib.php');
require_once(__DIR__. '/overview_form.php');
require_once(__DIR__. '/delete_intervention_data_form.php');
require_once($CFG->dirroot.'/mod/motbot/locallib.php');

require_login();
$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url('/mod/motbot/overview_settings.php');
$PAGE->set_pagelayout('admin');
$PAGE->set_title(format_string(get_string('pluginname', 'motbot')));
$PAGE->set_heading(get_string('pluginname', 'motbot'));

if (isguestuser()) {
    redirect($CFG->wwwroot.'/login/');
}

// Get db records.
$motbot_user = $DB->get_record('motbot_user', array('user' => $USER->id), '*');

if(!$motbot_user) {
    $motbot_user = (object) [
        'id' => null,
        'user' => $USER->id,
        'authorized' => 0,
        'pref_time' => null,
        'only_weekdays' => 0,
        'usermodified' => null,
        'timecreated' => null,
        'timemodified' => null,
    ];
}

// Create form default values.
$toform = (object) [
    'authorized' => $motbot_user->authorized,
    'pref_time' => $motbot_user->pref_time,
    'only_weekdays' => $motbot_user->only_weekdays,
];

$todeleteform = (object) [
    'recipient' => $USER->id,
];

// Instantiate simplehtml_form.
$mform = new mod_motbot_overview_form();
$deletedataform = new mod_motbot_delete_intervention_data_form();

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    // Handle form cancel operation, if cancel button is present on form.
    $url = $CFG->wwwroot.'/mod/motbot/overview.php';
    redirect($url);
} else if ($fromform = $mform->get_data()) {
    // In this case you process validated data. $mform->get_data() returns data posted in form.
    $time = time();
    $form_data = $mform->get_data();
    $motbot_user->authorized = $form_data->authorized;
    $motbot_user->pref_time = $form_data->pref_time;
    if($motbot_user->pref_time == -1) {
        $auto_period = motbot_calc_user_active_period($motbot_user->user);
        $motbot_user->pref_time = $auto_period;
    }
    $motbot_user->only_weekdays = $form_data->only_weekdays;
    // $motbot_user->allow_teacher_involvement = $form_data->allow_teacher_involvement;
    $motbot_user->usermodified = $USER->id;
    $motbot_user->timemodified = $time;

    if(!$motbot_user->timecreated) {
        $motbot_user->timecreated = $time;
    }

    // Update or insert new record.
    if(!$motbot_user->id) {
        $DB->insert_record('motbot_user', $motbot_user);
    } else {
        $DB->update_record('motbot_user', $motbot_user);
    }

    // Update course specific user settings with global settings.
    $motbot_course_users = $DB->get_records('motbot_course_user', array('user' => $motbot_user->user));
    foreach ($motbot_course_users as $user) {
        $user->authorized = $motbot_user->authorized;
        $DB->update_record('motbot_course_user', $user);
    }

    if($motbot_user->authorized) {
        $url = $CFG->wwwroot.'/mod/motbot/overview.php';
    } else {
        $url = $CFG->wwwroot.'/my/';
    }
    redirect($url);
}  else if ($fromform = $deletedataform->get_data()) {
    // In this case you process validated data. $mform->get_data() returns data posted in form.
    $DB->delete_records('motbot_intervention', array('recipient' => $USER->id));

    $url = $CFG->wwwroot.'/mod/motbot/overview.php';
    redirect($url);
} else {
    // This branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('pluginname', 'motbot'));

    // Set default data (if any).
    $mform->set_data($toform);
    // Displays the form.
    $mform->display();


    // Set default data (if any).
    $deletedataform->set_data($todeleteform);
    // Displays the form.
    $deletedataform->display();

    echo $OUTPUT->footer();
}

