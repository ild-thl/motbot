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
 * that enables him/her to manage, edit and update motbot advice options.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$id = optional_param('id', null, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHANUMEXT);

require_login();

$advice = \mod_motbot\manager::get_motbot_advice($id);

$returnurl = new \moodle_url('/mod/motbot/advice_settings.php');
$params = array('id' => $id, 'action' => $action);
$url = new \moodle_url('/mod/motbot/advice_settings.php', $params);

$title = '';
switch ($action) {
    case 'edit':
        $title = get_string('advice:edit', 'mod_motbot', $advice->get_name());
        break;
    case 'enable':
        $title = get_string('enable');
        break;
    case 'disable':
        $title = get_string('disable');
        break;
    case 'delete':
        $title = get_string('delete');
        break;
    case 'restore_default':
        $title = get_string('advice:restore_default', 'mod_motbot');
        break;
    case 'load_new':
        $title = get_string('advice:load_new', 'mod_motbot');
        break;
}

admin_externalpage_setup('motbot_advice_settings', '', null, '', array('pagelayout' => 'report'));

if ($title) {

    // admin_externalpage_setup('motbot_edit_advice', '', null, '', array('pagelayout' => 'report'));
    $PAGE->set_url($url);

    $PAGE->navbar->add($title);

    $PAGE->set_title($title);
    // $PAGE->set_heading($title);
}

switch ($action) {
    case 'enable':
        confirm_sesskey();

        $advice->enable();
        redirect($returnurl);
        break;

    case 'disable':
        confirm_sesskey();

        $advice->update(0, false);
        redirect($returnurl);
        break;

    case 'delete':
        confirm_sesskey();

        $advice->delete();
        redirect($returnurl);
        break;

    case 'restore_default':
        confirm_sesskey();

        \mod_motbot\retention\advice_manager::load_default_advice(true);
        redirect($returnurl);
        break;

    case 'load_new':
        confirm_sesskey();

        \mod_motbot\retention\advice_manager::load_default_advice();
        redirect($returnurl);
        break;

    case 'edit':
        $customdata = array(
            'id' => $advice->get_id(),
            'advice' => $advice->get_name(),
            'adviceclass' => $advice->get_class(),
            'enabled' => $advice->is_enabled(),
            'targets' => $advice->get_targets(),
            'available_targets' => $advice->get_potential_targets(),
        );
        $mform = new \mod_motbot\output\form\edit_advice(null, $customdata);

        if ($mform->is_cancelled()) {
            redirect($returnurl);
        } else if ($data = $mform->get_data()) {
            // Update advice.
            $advice->update($data->enabled, $data->targets);

            redirect($returnurl);
        } else {
            echo $OUTPUT->header();

            $mform->set_data($customdata);
            $mform->display();
        }

        break;

    default:
        echo $OUTPUT->header();

        $templatable = new \mod_motbot\output\advice_list($advice);
        echo $PAGE->get_renderer('mod_motbot')->render($templatable);
}

echo $OUTPUT->footer();
