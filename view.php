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
 * Prints an instance of mod_motbot.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once('lib.php');
require_once(__DIR__ . '/locallib.php');
require_once(__DIR__ . '/user_view.php');
require_once(__DIR__ . '/teacher_view.php');

$id = required_param('id', PARAM_INT);
list ($course, $cm) = get_course_and_cm_from_cmid($id, 'motbot');
$moduleinstance = $DB->get_record('motbot', array('id'=> $cm->instance), '*', MUST_EXIST);

require_login();
$modulecontext = context_module::instance($cm->id);
$coursecontext = context_course::instance($course->id);


if(has_capability('mod/motbot:addinstance', $coursecontext)) {
    $view = new mod_motbot_teacher_view($id, $moduleinstance->id, $coursecontext->id, $USER->id);
    if(!$DB->get_record('motbot', array('id' => $moduleinstance->id), 'usecode')->usecode) {
        redirect($view->settings_url, 'Please activate Motbot first.');
    }
} else {
    $motbot_user = $DB->get_record('motbot_user', array('user' => $USER->id), '*');
    $motbot_course_user = $DB->get_record('motbot_course_user', array('motbot' => $moduleinstance->id, 'user' => $USER->id), '*');

    $view = new mod_motbot_user_view($id, $moduleinstance->id, $coursecontext->id, $USER->id);
    if(!$motbot_course_user || !$motbot_course_user->authorized) {
        redirect($view->settings_url, 'Please activate your Motbot.');
    }
}



// $sql = "SELECT DISTINCT p.id, p.prediction, p.predictionscore
//         FROM mdl_user u
//         JOIN mdl_user_enrolments ue ON ue.userid = u.id
//         JOIN mdl_enrol e ON e.id = ue.enrolid
//         JOIN mdl_role_assignments ra ON ra.userid = u.id
//         JOIN mdl_context ct ON ct.id = ra.contextid AND ct.contextlevel = 50
//         JOIN mdl_course c ON c.id = ct.instanceid AND e.courseid = c.id
//         JOIN mdl_role r ON r.id = ra.roleid AND r.shortname = 'student'
//         JOIN mdl_analytics_predictions p ON p.sampleid = ue.id
//         WHERE e.status = 0 AND u.suspended = 0 AND u.deleted = 0
//         AND (ue.timeend = 0 OR ue.timeend > UNIX_TIMESTAMP(NOW())) AND ue.status = 0 AND u.id = :userid AND c.id = :courseid;";
// $params_array = array('userid' => $USER->id, 'courseid' => $course->id);
// $predicitons = $DB->get_records_sql($sql, $params_array);


// Wenn parameter $ueid aus overview.php übergeben ist kein kurslogin nötig um alte zertifikate auch zu sehen.
require_login($course, true, $cm);

$PAGE->set_url('/mod/motbot/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));
// $PAGE->set_context($modulecontext);


echo $OUTPUT->header();
// echo $OUTPUT->heading(get_string('pluginname', 'motbot'));

echo $view->render();

echo $OUTPUT->footer();


function mod_motbot_get_predictions_table($predictions) {
    $table = new html_table();
    $table->attributes['class'] = 'generaltable';

    $table->head  = array('id', 'prediction', 'predictionscore');
    $table->align = array('center', 'left', 'left');

    foreach ($predictions as $prediction) {
        $table->data[] = $prediction;
    }

    return html_writer::table($table);
}