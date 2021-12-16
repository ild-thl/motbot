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
 * MotBot module utility functions
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/mod/motbot/enable_module_form.php');

/**
 * Returns default options for an html_editor.
 *
 * @param context $context
 * @return array
 */
function mod_motbot_get_editor_options($context) {
    return array('maxfiles' => EDITOR_UNLIMITED_FILES, 'noclean' => true, 'context' => $context, 'subdirs' => true);
}

/**
 * Returns a table of a users intervention data as a html string.
 *
 * @param int $userid
 * @param int $contextid If set, the table includes only interventions from this context.
 * @param bool $include_messages If true, also include links to the corresponding notifications.
 * @return string HTML table, showing intervention data.
 */
function mod_motbot_get_interventions_table($userid, $contextid = null, $include_messages = false) {
    global $DB;

    // Only get interventions of a speicif context, if $contextid is set.
    $condition = 'WHERE i.recipient = :userid';
    $params_array = array('userid' => $userid);
    if($contextid) {
        $condition .= 'AND i.contextid = :contextid';
        $params_array = array_merge($params_array, array('contextid' => $contextid));
    }

    // Select columns. Include message column if $include_messages is set.
    $select = 'i.id, i.timecreated, i.state, i.teachers_informed, i.target';
    if ($include_messages) {
        $select .= ', i.message';
    }

    $sql = "SELECT $select FROM mdl_motbot_intervention i
        $condition
        ORDER BY timecreated DESC;";

    // Get interventions.
    $interventions = $DB->get_records_sql($sql, $params_array, IGNORE_MISSING);

    // Format intervention data.

    $content = array();
    if(!empty($interventions)) {
        foreach($interventions as $intervention) {
            $row = array();
            $targetname = mod_motbot_get_name_of_target($intervention->target);
            $row[] = \get_string('target:' . $targetname . '_short', 'motbot');
            $row[] =  userdate($intervention->timecreated);
            $row[] =  \get_string('state:' . $intervention->state, 'motbot');
            $row[] =  $intervention->teachers_informed ? 'Yes' : 'No';
            if ($include_messages) {
                $row[] =   '<a href="' . (new \moodle_url('/message/output/popup/notifications.php?notificationid=' . $intervention->message))->out(false) . '">View</a>';
            }
            $content[] = $row;
        }
    }
    // Create Table.
    $table = new html_table();
    $table->attributes['class'] = 'generaltable';

    $head = array('Reason', 'Date', 'State', 'Were teachers informed');
    $align = array('left ', 'left', 'left', 'left', 'left');
    if ($include_messages) {
        $head[] = 'message';
        $align[] = 'left';
    }
    $table->head  = $head;
    $table->align = $align;

    // Fill table.
    foreach ($content as $row) {
        $table->data[] = $row;
    }

    return html_writer::table($table);
}


/**
 * Returns a html form, that allows a user to enable a motbot in a specific course.
 *
 * @param object $motbot_course_user
 * @param int $courseid
 * @return string HTML form.
 */
function mod_motbot_view_enable_module_form($motbot_course_user, $courseid) {
    global $CFG, $DB;

    $toform = (object) [
        'id' => $courseid,
    ];
    // Instantiate form.
    $mform = new mod_motbot_enable_module_form();

    // Form processing and displaying is done here.
    if ($mform->is_cancelled()) {
        // Handle form cancel operation, if cancel button is present on form.
    } else if ($fromform = $mform->get_data()) {
        // In this case you process validated data. $mform->get_data() returns data posted in form.
        $form_data = $mform->get_data();
        $motbot_course_user->authorized = true;

        if(!$motbot_course_user->id) {
            $DB->insert_record('motbot_course_user', $motbot_course_user);
        } else {
            $DB->update_record('motbot_course_user', $motbot_course_user);
        }

        redirect($CFG->wwwroot.'/course/view.php?id=' . $courseid, 'Enabling Motbot', 1);
    } else {
        // This branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
        // or on the first display of the form.

        // Set default data (if any).
        $mform->set_data($toform);

        // Displays the form.
        return $mform->render();
    }

}

/**
 * Returns only the name part of a analytics target.
 *
 * @param string $target
 * @return string
 */
function mod_motbot_get_name_of_target($target) {
    preg_match('/.+\\\(.+)/m', $target, $matches);
    return $matches[1];
}

/**
 * Returns the amount of a specific type of activity in a course.
 *
 * @param string $name Name of the activity type.
 * @param int $course
 * @return int
 */
function mod_motbot_get_mod_count($name, $course) {
    global $DB;

    $sql = 'SELECT COUNT(cm.id) AS modcount
            FROM mdl_course_modules cm
            JOIN mdl_modules m ON m.id = cm.module
            WHERE cm.course = :course
            AND m.name = :name;';
    return $DB->get_record_sql($sql, array('course' => $course, 'name' => $name))->modcount;
}

/**
 * Returns the amount of feedbacks a user has given in a course.
 *
 * @param int $userid
 * @param int $courseid
 * @return int
 */
function mod_motbot_has_completed_feedback($userid, $courseid) {
    global $DB;

    $sql = 'SELECT COUNT(fc.id) as count
        FROM mdl_feedback_completed fc
        JOIN mdl_feedback f ON f.id = fc.feedback
        WHERE f.course = :courseid
        AND fc.userid = :userid;';

    return $DB->get_record_sql($sql, array('courseid' => $courseid, 'userid' => $userid))->count;
}

/**
 * Calculates the time period during which a specific user is active the most.
 *
 * @param int $user
 * @return int Time period during which the user is active the most.
 */
function motbot_calc_user_active_period($user) {
    if (!$logstore = core_analytics\manager::get_analytics_logstore()) {
        throw new coding_exception('No available log stores');
    }

    $select = "userid = :userid AND timecreated <= :endtime";
    $params = array('userid' => $user, 'endtime' => time());
    $events = $logstore->get_events_select($select, $params, null, null, null);

    $hours = 24;
    $interval = $hours/8;
    $steps = array();
    for($i = $interval; $i <= $hours; $i+=$interval) {
        $steps[$i] = 0;
    }

    foreach($events as $event) {
        $time = new DateTime("now", core_date::get_user_timezone_object());
        $time->setTimestamp($event->timecreated);
        $hour = $time->format('H');

        foreach($steps as $step => $count) {
            if($hour < $step) {
                $steps[$step] = ++$count;
                break;
            }
        }
    }

    $active_period = 0;
    $highest_count = 0;

    foreach($steps as $step => $count) {
        if($count > $highest_count) {
            $highest_count = $count;
            $active_period = $step;
        }
    }

    return $active_period - $interval;
}