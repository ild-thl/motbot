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
 * Moodle page that displays a form to the logged in admin,
 * that enables him/her to set options about how the motbot modules works.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once('lib.php');
require_once(__DIR__ . '/course_settings_form.php');
require_once(__DIR__ . '/delete_intervention_data_form.php');
require_once($CFG->dirroot . '/mod/motbot/locallib.php');
require_once($CFG->libdir . '/adminlib.php');

$context = context_system::instance();

// Only looged in user should be able to view this page.
require_login();

// Only users with certain admin privileges should be able to access this site.
if (!has_capability('moodle/site:config', $context)) {
    redirect($CFG->wwwroot);
}

$PAGE->set_context($context);
$PAGE->set_url('/mod/motbot/edit_motbot.php');
$PAGE->set_title(get_string('settings:edit_motbot', 'motbot'));
$PAGE->set_heading(get_string('settings:edit_motbot', 'motbot'));
// Inform moodle which menu entry currently is active!
admin_externalpage_setup('motbot_edit_motbot');

// Instantiate forms.
// $mform = new mod_motbot_course_settings_form();

// Form processing and displaying is done here.
// if ($mform->is_cancelled()) {
//     // Handle form cancel operation.


// } else if ($fromform = $mform->get_data()) {
//     // Process validated data. $mform->get_data() returns data posted in form.

// } else {
// this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
// or on the first display of the form.

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('settings:edit_motbot', 'motbot'));

// Set default data (if any).
// $mform->set_data($toform);
// Displays the form.
// $mform->display();

echo $OUTPUT->footer();
// }
