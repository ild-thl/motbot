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
 * that enables him/her to manage, edit and update motbot models that aren't course specific.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once('lib.php');
require_once($CFG->dirroot . '/mod/motbot/locallib.php');
require_once($CFG->dirroot . '/mod/motbot/edit_models_form.php');
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
admin_externalpage_setup('motbot_edit_models');

// Instantiate forms.
$mform = new mod_motbot_edit_models_form();

$models = $DB->get_records('motbot_model', array('motbot' => null));


$toform = new \StdClass();
$motbot_models = \mod_motbot\manager::get_motbot_models();

foreach ($motbot_models as $motbot_model) {
    $target_name = $motbot_model->targetname;
    $draftitemid = file_get_submitted_draft_itemid($target_name . '_fullmessagehtml' . $motbot_model->prediction);

    $toform->{$target_name . '_id' . $motbot_model->prediction} = $motbot_model->id;
    $toform->{$target_name . '_motbot' . $motbot_model->prediction} = $motbot_model->motbot;
    $toform->{$target_name . '_model' . $motbot_model->prediction} = $motbot_model->model;
    $toform->{$target_name . '_active' . $motbot_model->prediction} = $motbot_model->active;
    $toform->{$target_name . '_custom' . $motbot_model->prediction} = $motbot_model->custom;
    $toform->{$target_name . '_target' . $motbot_model->prediction} = $motbot_model->target;
    $toform->{$target_name . '_prediction' . $motbot_model->prediction} = $motbot_model->prediction;
    $toform->{$target_name . '_subject' . $motbot_model->prediction} = $motbot_model->subject;
    $toform->{$target_name . '_fullmessage' . $motbot_model->prediction} = $motbot_model->fullmessage;
    $toform->{$target_name . '_fullmessagehtml' . $motbot_model->prediction}['text']   = file_prepare_draft_area(
        $draftitemid,
        $context->id,
        'mod_motbot',
        'attachment',
        0,
        mod_motbot_get_editor_options($context),
        $motbot_model->fullmessagehtml
    );
    $toform->{$target_name . '_fullmessagehtml' . $motbot_model->prediction}['itemid'] = $draftitemid;
    $toform->{$target_name . '_usermodified' . $motbot_model->prediction} = $motbot_model->usermodified;
    $toform->{$target_name . '_timemodified' . $motbot_model->prediction} = $motbot_model->timemodified;
    $toform->{$target_name . '_timecreated' . $motbot_model->prediction} = $motbot_model->timecreated;
}

// Form processing and displaying is done here.
if ($mform->is_cancelled()) {
    // Handle form cancel operation.
    redirect($CFG->wwwroot . '/admin/category.php?category=modmotbotfolder');
} else if ($fromform = $mform->get_data()) {
    // Process validated data. $mform->get_data() returns data posted in form.
    global $DB, $USER;

    $timemodified = time();

    foreach ($fromform->motbot_models as $motbot_model) {
        $motbot_model->timemodified = $timemodified;
        $motbot_model->usermodified = $USER->id;

        // Get fullmessagehtml from draft files.
        $target_name = $motbot_model->targetname;
        $draftitemid = $motbot_model->itemid;
        $context = context_system::instance();
        if ($draftitemid) {
            $motbot_model->fullmessagehtml = file_save_draft_area_files($draftitemid, $context->id, 'mod_motbot', 'attachment', 0, mod_motbot_get_editor_options($context), $motbot_model->fullmessagehtml);
        }

        if (!isset($motbot_model->id) || empty($motbot_model->id)) { // Insert new record.
            $motbot_model->timecreated = $motbot_model->timemodified;
            $motbot_model->id = $DB->insert_record('motbot_model', $motbot_model);
        } else { // Update existing record.
            $DB->update_record('motbot_model', $motbot_model);
        }
    }

    redirect($CFG->wwwroot . '/admin/category.php?category=modmotbotfolder');
} else {
    // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
    // or on the first display of the form.

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('settings:edit_motbot', 'motbot'));

    // Set default data (if any).
    $mform->set_data($toform);
    // Displays the form.
    $mform->display();

    echo $OUTPUT->footer();
}
