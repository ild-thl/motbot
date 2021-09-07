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
require_once(__DIR__. '/course_settings_form.php');
require_once($CFG->dirroot.'/mod/motbot/locallib.php');

$id = required_param('id', PARAM_INT);
list ($course, $cm) = get_course_and_cm_from_cmid($id, 'motbot');
$moduleinstance = $DB->get_record('motbot', array('id'=> $cm->instance), '*', MUST_EXIST);

require_login();
$modulecontext = context_module::instance($cm->id);
$coursecontext = context_course::instance($course->id);

$motbot_user = $DB->get_record('motbot_user', array('user' => $USER->id), '*');
$motbot_course_user = $DB->get_record('motbot_course_user', array('motbot' => $moduleinstance->id, 'user' => $USER->id), '*');

$settings_url = $CFG->wwwroot.'/mod/motbot/course_settings.php?id=' . $id;
if(!$motbot_course_user || !$motbot_course_user->authorized) {
    redirect($settings_url, 'Please activate your Motbot');
}

$messages = $DB->get_records('motbot_message', array('motbot' => $moduleinstance->id), '', 'target, active');

$models = array();

foreach($messages as $message) {
    $models[] = mod_motbot_view_get_model_data($message, $coursecontext->id, $USER->id);
}

usort($models, "mod_motbot_sort_models_by_enable");
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


if(has_capability('mod/motbot:addinstance', $coursecontext)) {
    $url = $CFG->wwwroot.'/course/modedit.php?update=' . $id;
    redirect($url);
    die;
}


$contextinfo = [
    'settings_url' => $settings_url,
    'models' => $models,
    'interventions_table' => mod_motbot_get_interventions_table($USER->id, $coursecontext->id, true),
];

echo $OUTPUT->header();
// echo $OUTPUT->heading(get_string('pluginname', 'motbot'));

echo $OUTPUT->render_from_template('mod_motbot/view', $contextinfo);

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

function mod_motbot_sort_models_by_enable($a, $b) {
    if($a["enabled"] == $b["enabled"]) return 0;
    return (!$b["enabled"] && $b["enabled"]) ? -1 : 1;
}

function mod_motbot_view_get_model_data($message, $contextid, $userid) {
    global $DB;

    $target_name = mod_motbot_get_name_of_target($message->target);
    $model = [
        "name" => \get_string('target:' . $target_name . '_short', 'motbot'),
        "enabled" => $message->active,
        "hasdata" => false,
        "state" => '',
        "date" => null,
        "image" => 'disabled_motbot',
        "intervention_url" => null,
    ];

    if(!$message->active) {
        return $model;
    }

    $sql = "SELECT *
        FROM mdl_motbot_intervention
        WHERE contextid = :contextid
        AND recipient = :recipient
        AND target = :target
        ORDER BY timecreated DESC
        LIMIT 1";
    $latest_intervention = $DB->get_record_sql($sql, array('contextid' => $contextid, 'recipient' => $userid, 'target' => $message->target), IGNORE_MISSING);

    if(!$latest_intervention) {
        $model["image"] = 'happy_motbot';
        return $model;
    }

    $model["state"] = \get_string('state:' . $latest_intervention->state, 'motbot');
    $model["hasdata"] = true;
    $model["date"] = userdate($latest_intervention->timemodified);
    if ($latest_intervention->state == \mod_motbot\retention\intervention::INTERVENED || $latest_intervention->state == \mod_motbot\retention\intervention::UNSUCCESSFUL) {
        $model["intervention_url"] = (new \moodle_url('/message/output/popup/notifications.php?notificationid=' . $latest_intervention->message))->out(false);
        $model["image"] = 'unhappy_motbot';
    } else {
        $model["image"] = 'happy_motbot';
    }
    return $model;
}