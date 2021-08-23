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
 * Private page module utility functions
 *
 * @package mod_page
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;


function mod_motbot_get_editor_options($context) {
    return array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $context, 'subdirs' => true);
}

function mod_motbot_get_interventions_table($userid, $contextid = null) {
    global $DB;

    if($contextid) {
        $conitionsarray = array('recipient' => $userid, 'contextid' => $contextid);
    } else {
        $conitionsarray = array('recipient' => $userid);
    }

    $interventions = $DB->get_records('motbot_intervention', $conitionsarray , '', 'timecreated, state, teachers_informed, message');
    foreach($interventions as $intervention) {
        $intervention->state = \get_string('state:' . $intervention->state, 'motbot');
        $intervention->teachers_informed = $intervention->teachers_informed ? 'Yes' : 'No';
        $intervention->message =  '<a href="' . (new \moodle_url('/message/output/popup/notifications.php?notificationid=' . $intervention->message))->out(false) . '">View</a>';
        $intervention->timecreated = userdate($intervention->timecreated);
    }

    $table = new html_table();
    $table->attributes['class'] = 'generaltable';

    $table->head  = array('date', 'state', 'teachers_informed', 'message',);
    $table->align = array('left', 'left', 'left', 'left');

    foreach ($interventions as $intervention) {
        $table->data[] = $intervention;
    }

    return html_writer::table($table);
}


function mod_motbot_info_form($motbot_course_user, $courseid, $cm) {
    global $CFG, $DB;

    $toform = (object) [
        'id' => $courseid,
    ];
    //Instantiate form
    $mform = new mod_motbot_cm_info_form();

    //Form processing and displaying is done here
    if ($mform->is_cancelled()) {
        //Handle form cancel operation, if cancel button is present on form
    } else if ($fromform = $mform->get_data()) {
        //In this case you process validated data. $mform->get_data() returns data posted in form.
        $form_data = $mform->get_data();
        $motbot_course_user->authorized = true;

        if(!$motbot_course_user->id) {
            $DB->insert_record('motbot_course_user', $motbot_course_user);
        } else {
            $DB->update_record('motbot_course_user', $motbot_course_user);
        }

        redirect($CFG->wwwroot.'/course/view.php?id=' . $courseid, 'Enabling Motbot', 1);
    } else {
        // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
        // or on the first display of the form.

        //Set default data (if any)
        $mform->set_data($toform);

        //displays the form
        return $mform->render();
    }

}


function mod_motbot_get_name_of_target($target) {
    preg_match('/.+\\\(.+)/m', $target, $matches);
    return $matches[1];
}



function mod_motbot_get_mod_count($name, $course) {
    global $DB;

    $sql = 'SELECT COUNT(cm.id) AS modcount
            FROM mdl_course_modules cm
            JOIN mdl_modules m ON m.id = cm.module
            WHERE cm.course = :course
            AND m.name = :name;';
    return $DB->get_record_sql($sql, array('course' => $course, 'name' => $name))->modcount;
}