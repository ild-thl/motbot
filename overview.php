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
 * Shows a non course specific motbot overview.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once('lib.php');
require_once(__DIR__ . '/locallib.php');
require_once(__DIR__ . '/user_overview.php');

require_login();
$context = context_system::instance();

if (isguestuser()) {
    redirect($CFG->wwwroot . '/login/');
}

$motbot_user = $DB->get_record('motbot_user', array('user' => $USER->id), '*', IGNORE_MISSING);
$view = new mod_motbot_overview($USER->id);
// If motbot user not yet registered or motbot activity ist deactivated by the user, redirect to settings page.
if (!$motbot_user || !$motbot_user->authorized) {
    redirect($view->settings_url, \get_string('motbot:pleaseactivate', 'motbot'));
}

// Else render view.
$PAGE->set_context($context);
$PAGE->set_url('/mod/motbot/overview.php');
$PAGE->set_pagelayout('admin');
$PAGE->set_title(format_string(get_string('pluginname', 'motbot')));
$PAGE->set_heading(get_string('pluginname', 'motbot'));

echo $OUTPUT->header();

echo $view->render();

echo $OUTPUT->footer();
