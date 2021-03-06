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
 * Library of interface functions and constants.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/motbot/locallib.php');

/**
 * Return if the plugin supports $feature.
 *
 * @param string $feature Constant representing the feature.
 * @return true | null True if the feature is supported, null otherwise.
 */
function motbot_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the mod_motbot into the database.
 *
 * Given an object containing all the necessary data, (defined by the form
 * in mod_form.php) this function will create a new instance and return the id
 * number of the instance.
 *
 * @param object $data An object from the form.
 * @param mod_motbot_mod_form $mform The form.
 * @return int The id of the newly inserted record.
 */
function motbot_add_instance($data, $mform = null) {
    global $DB, $USER;

    $data->timecreated = time();

    $id = $DB->insert_record('motbot', $data);

    foreach ($data->motbot_models as $motbotmodel) {
        $motbotmodel->motbot = $id;
        $motbotmodel->timecreated = $data->timecreated;
        $motbotmodel->usermodified = $USER->id;
        $motbotmodel->id = $DB->insert_record('motbot_model', $motbotmodel);

        $targetname = $motbotmodel->targetname;

        if ($mform and !empty($data->{$targetname . '_fullmessagehtml' . $motbotmodel->prediction}['itemid'])) {

            $draftitemid = $data->{$targetname . '_fullmessagehtml' . $motbotmodel->prediction}['itemid'];
            $cmid = $data->coursemodule;
            $context = context_module::instance($cmid);

            $motbotmodel->fullmessagehtml = file_save_draft_area_files($draftitemid, $context->id, 'mod_motbot', 'attachment', 0, mod_motbot_get_editor_options($context), $motbotmodel->fullmessagehtml);
            $DB->update_record('motbot_model', $motbotmodel);
        }
    }

    return $id;
}

/**
 * Updates an instance of the mod_motbot in the database.
 *
 * Given an object containing all the necessary data (defined in mod_form.php),
 * this function will update an existing instance with new data.
 *
 * @param object $data An object from the form in mod_form.php.
 * @param mod_motbot_mod_form $mform The form.
 * @return bool True if successful, false otherwise.
 */
function motbot_update_instance($data, $mform = null) {
    global $DB, $USER;

    $data->timemodified = time();
    $data->id = $data->instance;

    foreach ($data->motbot_models as $motbotmodel) {
        $motbotmodel->timemodified = $data->timemodified;
        $motbotmodel->usermodified = $USER->id;
        if (!isset($motbotmodel->id)) {
            $motbotmodel->motbot = $data->id;
            $motbotmodel->timecreated = $data->timemodified;
            $motbotmodel->id = $DB->insert_record('motbot_model', $motbotmodel);
        } else {
            $DB->update_record('motbot_model', $motbotmodel);
        }
        $targetname = $motbotmodel->targetname;

        $draftitemid = $data->{$targetname . '_fullmessagehtml' . $motbotmodel->prediction}['itemid'];
        $cmid = $data->coursemodule;
        $context = context_module::instance($cmid);
        if ($draftitemid) {
            $motbotmodel->fullmessagehtml = file_save_draft_area_files($draftitemid, $context->id, 'mod_motbot', 'attachment', 0, mod_motbot_get_editor_options($context), $motbotmodel->fullmessagehtml);
            $DB->update_record('motbot_model', $motbotmodel);
        }
    }

    return $DB->update_record('motbot', $data);
}

/**
 * Removes an instance of the mod_motbot from the database.
 *
 * @param int $id Id of the module instance.
 * @return bool True if successful, false on failure.
 */
function motbot_delete_instance($id) {
    global $DB;

    $exists = $DB->get_record('motbot', array('id' => $id));
    if ($exists) {
        $DB->delete_records('motbot', array('id' => $id));
    }

    $records = $DB->get_records('motbot_model', array('motbot' => $id));
    foreach ($records as $record) {
        $DB->delete_records('motbot_model', array('id' => $record->id));
    }

    return true;
}

/**
 * Serves motbot files and messages.
 *
 * @package  mod_motbot
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 * @return bool false if file not found, does not return if found - just send the file
 */
function motbot_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    global $CFG, $DB;

    if ($filearea !== 'attachment') {
        // intro is handled automatically in pluginfile.php
        return false;
    }

    // Leave this line out if you set the itemid to null in make_pluginfile_url (set $itemid to 0 instead).
    $itemid = array_shift($args); // The first item in the $args array.

    // Use the itemid to retrieve any relevant data records and perform any security checks to see if the
    // user really does have access to the file in question.

    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args); // The last item in the $args array.

    if (!$args) {
        $filepath = '/'; // $args is empty => the path is '/'
    } else {
        $filepath = '/' . implode('/', $args) . '/'; // $args contains elements of the filepath
    }

    // Retrieve the file from the Files API.
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'mod_motbot', $filearea, $itemid, $filepath, $filename);
    if (!$file) {
        return false; // The file does not exist.
    }

    // We can now send the file back to the browser - in this case with a cache lifetime of 1 day and no filtering.
    send_stored_file($file, 86400, 0, $forcedownload, $options);
}

/**
 * Sets how the module should be represented in the course overview.
 *
 * @param cm_info $cm Info about course_module.
 * @return void
 */
function motbot_cm_info_view(cm_info $cm) {
    global $USER, $CFG, $DB;

    $modulecontext = context_module::instance($cm->id);

    $courseid = required_param('id', PARAM_INT);

    $motbot = $DB->get_record('motbot', array('id' => $cm->instance), '*', MUST_EXIST);
    $motbotcourseuser = $DB->get_record('motbot_course_user', array('motbot' => $motbot->id, 'user' => $USER->id), '*');

    if (!$motbotcourseuser) {
        $motbotcourseuser = (object) [
            'id' => null,
            'motbot' => $motbot->id,
            'user' => $USER->id,
            'authorized' => 0,
            'allow_teacher_involvement' => 0,
        ];
    }

    // Display a form that lets students enable the motbot, if they haven't already.
    if (!$motbotcourseuser->authorized && !has_capability('mod/motbot:addinstance', $modulecontext)) {
        $content = mod_motbot_view_enable_module_form($motbotcourseuser, $courseid);
        $content = str_replace('class="mform"', 'class="mform float-right"', $content);
        $cm->set_name($motbot->name);
    } else {
        // Show motivational quote of the day.
        $now = new DateTime("now", core_date::get_user_timezone_object());
        $dayofweek = $now->format('N');
        $quoteoftheday = get_string('quote:' . $dayofweek % 6, 'motbot');
        $content = '<div style="font-style: italic; padding-left: 2em">' . $quoteoftheday . '</div>';
    } // If enabled or not a student show a motivational quote instead.

    $cm->set_after_link($content);
}

/**
 * Sets dynamic cacheable parts of the modules representation in the course overview.
 *
 * @param cm_info $cm Info about course_module.
 * @return void
 */
function motbot_cm_info_dynamic(cm_info $cm) {
    global $USER, $DB, $PAGE;

    $modulecontext = context_module::instance($cm->id);

    if (!has_capability('mod/motbot:addinstance', $modulecontext)) {
        // Display a diffrent icon depending on wether the motbot is enabled for the logged in user.
        $active = $DB->get_record('motbot_course_user', array('motbot' => $cm->instance, 'user' => $USER->id, 'authorized' => 1));
        if ($active) {
            $ishappy = \mod_motbot\manager::is_motbot_happy($USER->id, $cm->instance);
            // Load script that asynchronously reevaluates wether the motbot is happy and updates the icon accordingly.
            $PAGE->requires->js_call_amd('mod_motbot/update_motbot_icon', 'init', array($cm->instance, $ishappy));
            if (!$ishappy) {
                $cm->set_icon_url(new \moodle_url('/mod/motbot/pix/icon-unhappy.svg'));
            }
        } else {
            $cm->set_icon_url(new \moodle_url('/mod/motbot/pix/icon-inactive.svg'));
            $cm->set_name('Motbot disabled');
        }
    } else {
        $paused = $DB->get_record('motbot', array('id' => $cm->instance, 'active' => 0));
        if ($paused) {
            $cm->set_icon_url(new \moodle_url('/mod/motbot/pix/icon-inactive.svg'));
            $cm->set_name('Motbot disabled');
        }
    }
}
