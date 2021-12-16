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
require_once(__DIR__. '/course_settings_form.php');
require_once(__DIR__. '/delete_intervention_data_form.php');
require_once($CFG->dirroot.'/mod/motbot/locallib.php');

$id = required_param('id', PARAM_INT);
list ($course, $cm) = get_course_and_cm_from_cmid($id, 'motbot');
$moduleinstance = $DB->get_record('motbot', array('id'=> $cm->instance), '*', MUST_EXIST);

// Only looged in user should be able to view this page.
$modulecontext = context_module::instance($cm->id);
$coursecontext = context_course::instance($course->id);

// Redirect teachers and managers.
if(has_capability('mod/motbot:addinstance', $coursecontext)) {
    $url = $CFG->wwwroot.'/course/modedit.php?update=' . $id;
    redirect($url);
    die;
}

// Get prevoious settings.
$motbot_user = $DB->get_record('motbot_user', array('user' => $USER->id), '*');
$motbot_course_user = $DB->get_record('motbot_course_user', array('motbot' => $moduleinstance->id, 'user' => $USER->id), '*');

// In case there are no previous settings, set default options.
if(!$motbot_user) {
    $time = time();
    $motbot_user = (object) [
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
if(!$motbot_course_user) {
    $motbot_course_user = (object) [
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
    'authorized' => $motbot_course_user->authorized,
    'allow_teacher_involvement' => $motbot_course_user->allow_teacher_involvement,
    'disabled_models' => $motbot_course_user->disabled_models,
    'disabled_advice' => $motbot_course_user->disabled_advice,
    // 'pref_time' => $motbot_course_user->pref_time,
    // 'only_weekdays' => $motbot_course_user->only_weekdays,
    'allow_course_completion' => true,
    'allow_feedback' => true,
    'allow_recent_activities' => true,
    'allow_recent_forum_activity' => true,
    'allow_recommended_discussion' => true,
    'allow_visit_course' => true,
];

$disabled_models = json_decode($motbot_course_user->disabled_models);
foreach($disabled_models as $d) {
    $targetname = mod_motbot_get_name_of_target($d);
    $toform->$targetname = 0;
}
$disabled_advice = json_decode($motbot_course_user->disabled_advice);
foreach($disabled_advice as $d) {
    $toform->$d = 0;
}

// Set default values delete_intervention_records_form.
$todeleteform = (object) [
    'id' => $id,
    'recipient' => $USER->id,
    'contextid' => $coursecontext->id,
];

// User has to be logged in.
require_login($course, true, $cm);

$PAGE->set_url('/mod/motbot/course_settings.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name . ': ' . $course->fullname));
$PAGE->set_heading(format_string($course->fullname));

// Instantiate forms.
$mform = new mod_motbot_course_settings_form();
$deletedataform = new mod_motbot_delete_intervention_data_form();

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    // Handle form cancel operation.

    if($motbot_course_user->authorized) {
        $url = $CFG->wwwroot.'/mod/motbot/view.php?id=' . $id;
    } else {
        $url = $CFG->wwwroot.'/course/view.php?id=' . $course->id;
    }
    redirect($url);
} else if ($fromform = $mform->get_data()) {
    // Process validated data. $mform->get_data() returns data posted in form.
    $time = time();
    $form_data = $mform->get_data();
    $motbot_course_user->authorized = $form_data->authorized;
    $motbot_course_user->allow_teacher_involvement = $form_data->allow_teacher_involvement;
    $motbot_course_user->disabled_models = $form_data->disabled_models;
    $motbot_course_user->disabled_advice = $form_data->disabled_advice;
    // $motbot_course_user->pref_time = $form_data->pref_time;
    // if($motbot_course_user->pref_time == -1) {
    //     $auto_period = motbot_calc_user_active_period($motbot_course_user->user);
    //     $motbot_course_user->pref_time = $auto_period;
    // }
    // $motbot_course_user->only_weekdays = $form_data->only_weekdays;
    $motbot_course_user->usermodified = $USER->id;
    $motbot_course_user->timemodified = $time;
    if(!$motbot_course_user->timecreated) {
        $motbot_course_user->timecreated = $time;
    }

    $motbot_user->authorized = $form_data->authorized;
    $motbot_user->allow_teacher_involvement = $form_data->allow_teacher_involvement;
    $motbot_user->usermodified = $USER->id;
    $motbot_user->timemodified = $time;
    if(!$motbot_user->timecreated) {
        $motbot_user->timecreated = $time;
    }
    // Update user records.
    if(!$motbot_course_user->id) {
        $DB->insert_record('motbot_course_user', $motbot_course_user);
    } else {
        $DB->update_record('motbot_course_user', $motbot_course_user);
    }

    if(!$motbot_user->id) {
        $DB->insert_record('motbot_user', $motbot_user);
    }

    if($motbot_course_user->authorized) {
        $url = $CFG->wwwroot.'/mod/motbot/view.php?id=' . $id;
    } else {
        $url = $CFG->wwwroot.'/course/view.php?id=' . $course->id;
    }
    redirect($url);
} else if ($fromform = $deletedataform->get_data()) {
    // Process validated data. $mform->get_data() returns data posted in form.
    // Delete users intervention records blonging to this module.
    $DB->delete_records('motbot_intervention', array('recipient' => $USER->id, 'contextid' => $coursecontext->id));

    $url = $CFG->wwwroot.'/mod/motbot/view.php?id=' . $id;
    redirect($url);
} else {
    // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('pluginname', 'motbot') . ' preferences for ' . $course->fullname);

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