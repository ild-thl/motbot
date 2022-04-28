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
 * Moodle page that displays a form to the looged in user,
 * that enables him/her to set options about how the module works.
 * The kind of form shown depends on the role of the user in the course.
 * Students get to enable Motbot activity for themselves and get to delete their intervention reords.
 * While tachers get redirected to the modedit form.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once('lib.php');
require_once(__DIR__ . '/course_settings_form.php');
require_once(__DIR__ . '/delete_intervention_data_form.php');
require_once($CFG->dirroot . '/mod/motbot/locallib.php');

$id = required_param('id', PARAM_INT);
list($course, $cm) = get_course_and_cm_from_cmid($id, 'motbot');
$moduleinstance = $DB->get_record('motbot', array('id' => $cm->instance), '*', MUST_EXIST);

// Only looged in user should be able to view this page.
$modulecontext = context_module::instance($cm->id);
$coursecontext = context_course::instance($course->id);

// Redirect teachers and managers.
if (has_capability('mod/motbot:addinstance', $coursecontext)) {
    $url = $CFG->wwwroot . '/course/modedit.php?update=' . $id;
    redirect($url);
    die;
}

// User has to be logged in.
require_login($course, true, $cm);

$PAGE->set_url('/mod/motbot/course_settings.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name . ': ' . $course->fullname));
$PAGE->set_heading(format_string($course->fullname));

// Get prevoious settings.
$motbotuser = $DB->get_record('motbot_user', array('user' => $USER->id), '*');
$motbotcourseuser = $DB->get_record('motbot_course_user', array('motbot' => $moduleinstance->id, 'user' => $USER->id), '*');

// In case there are no previous settings, set default options.
if (!$motbotuser) {
    $time = time();
    $motbotuser = (object) [
        'id' => null,
        'user' => $USER->id,
        'authorized' => 0,
        'pref_time' => -1,
        'only_weekdays' => 0,
        'usermodified' => null,
        'timecreated' => null,
        'timemodified' => null,
    ];
}

// In case there are no previous settings, set default options.
if (!$motbotcourseuser) {
    $motbotcourseuser = (object) [
        'id' => null,
        'motbot' => $moduleinstance->id,
        'user' => $USER->id,
        'authorized' => 0,
        'allow_teacher_involvement' => 0,
        'disabled_models' => [],
        'disabled_advice' => [],
        // 'pref_time' => -1,
        // 'only_weekdays' => 0,
        'usermodified' => null,
        'timecreated' => null,
        'timemodified' => null,
    ];
}

// Set default values for course_settings_form.
$toform = (object) [
    'id' => $id,
    'authorized' => $motbotcourseuser->authorized,
    'allow_teacher_involvement' => $motbotcourseuser->allow_teacher_involvement,
    'disabled_models' => $motbotcourseuser->disabled_models,
    'disabled_advice' => $motbotcourseuser->disabled_advice,
    // 'pref_time' => $motbot_course_user->pref_time,
    // 'only_weekdays' => $motbot_course_user->only_weekdays,
];

$disabledmodels = json_decode($motbotcourseuser->disabled_models);
foreach ($disabledmodels as $d) {
    $targetname = mod_motbot_get_name_of_target($d);
    $toform->$targetname = 0;
}
$disabledadvice = json_decode($motbotcourseuser->disabled_advice);
foreach ($disabledadvice as $d) {
    $toform->$d = 0;
}

// Set default values delete_intervention_records_form.
$todeleteform = (object) [
    'id' => $id,
    'recipient' => $USER->id,
    'contextid' => $coursecontext->id,
];

$models = $DB->get_records('motbot_model', array('motbot' => $moduleinstance->id), '', 'id, target, active');
$advice = $DB->get_records('motbot_advice', array());
// Instantiate forms.
$mform = new mod_motbot_course_settings_form(null, array('models' => $models, 'advice' => $advice));
$deletedataform = new mod_motbot_delete_intervention_data_form();

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    // Handle form cancel operation.

    if ($motbotcourseuser->authorized) {
        $url = $CFG->wwwroot . '/mod/motbot/view.php?id=' . $id;
    } else {
        $url = $CFG->wwwroot . '/course/view.php?id=' . $course->id;
    }
    redirect($url);
} else if ($fromform = $mform->get_data()) {
    // Process validated data. $mform->get_data() returns data posted in form.
    $time = time();
    $formdata = $mform->get_data();
    $motbotcourseuser->authorized = $formdata->authorized;
    $motbotcourseuser->allow_teacher_involvement = $formdata->allow_teacher_involvement;
    $motbotcourseuser->disabled_models = $formdata->disabled_models;
    $motbotcourseuser->disabled_advice = $formdata->disabled_advice;
    $motbotcourseuser->usermodified = $USER->id;
    $motbotcourseuser->timemodified = $time;
    if (!$motbotcourseuser->timecreated) {
        $motbotcourseuser->timecreated = $time;
    }

    $motbotuser->authorized = $formdata->authorized;
    $motbotuser->allow_teacher_involvement = $formdata->allow_teacher_involvement;
    $motbotuser->usermodified = $USER->id;
    $motbotuser->timemodified = $time;
    if (!$motbotuser->timecreated) {
        $motbotuser->timecreated = $time;
    }
    // Update user records.
    if (!$motbotcourseuser->id) {
        $DB->insert_record('motbot_course_user', $motbotcourseuser);
    } else {
        $DB->update_record('motbot_course_user', $motbotcourseuser);
    }

    if (!$motbotuser->id) {
        $DB->insert_record('motbot_user', $motbotuser);
    }

    if ($motbotcourseuser->authorized) {
        $url = $CFG->wwwroot . '/mod/motbot/view.php?id=' . $id;
    } else {
        $url = $CFG->wwwroot . '/course/view.php?id=' . $course->id;
    }
    redirect($url);
} else if ($fromform = $deletedataform->get_data()) {
    // Process validated data. $mform->get_data() returns data posted in form.
    // Delete users intervention records blonging to this module.
    $DB->delete_records('motbot_intervention', array('recipient' => $USER->id, 'contextid' => $coursecontext->id));

    $url = $CFG->wwwroot . '/mod/motbot/view.php?id=' . $id;
    redirect($url);
} else {
    // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('settings:course_settings_header', 'motbot', array('pluginname' => get_string('pluginname', 'motbot'), 'coursename' => $course->fullname)));

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
