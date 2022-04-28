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
 * Show either a motbot overview for users that have activated the motbot
 * or redirect to a settingspage for users or redirect to a view for teachers.
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
list($course, $cm) = get_course_and_cm_from_cmid($id, 'motbot');
$moduleinstance = $DB->get_record('motbot', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);
$modulecontext = context_module::instance($cm->id);
$coursecontext = context_course::instance($course->id);


if (has_capability('mod/motbot:addinstance', $coursecontext)) {
    // If teacher or admin, redirect.
    $view = new mod_motbot_teacher_view($id, $moduleinstance->id, $coursecontext->id, $USER->id);
    if (!$DB->get_record('motbot', array('id' => $moduleinstance->id), 'active')->active) {
        redirect($view->settings_url, get_string('motbot:pleaseactivate', 'motbot'));
    }
} else {
    // Else init student view.
    $motbotuser = $DB->get_record('motbot_user', array('user' => $USER->id), '*');
    $motbotcourseuser = $DB->get_record('motbot_course_user', array('motbot' => $moduleinstance->id, 'user' => $USER->id), '*');

    $view = new mod_motbot_user_view($id, $moduleinstance->id, $coursecontext->id, $USER->id);
    if (!$motbotcourseuser || !$motbotcourseuser->authorized) {
        // If motbot inactive redirect to motbot settings.
        redirect($view->settings_url, get_string('motbot:pleaseactivate', 'motbot'));
    }
}


$PAGE->set_url('/mod/motbot/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($moduleinstance->name));
$PAGE->set_heading(format_string($course->fullname));


echo $OUTPUT->header();

echo $view->render();

echo $OUTPUT->footer();
