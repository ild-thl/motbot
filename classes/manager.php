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
 * Library of useful helper functions for managing predictions and interventions.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot;

defined('MOODLE_INTERNAL') || die();

/**
 * Library of useful helper functions for managing predictions and interventions.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {
    public static function log_prediction($modelid, $sampleid, $rangeindex, \context $samplecontext, $scalar_prediction, $predictionscore) {

        $prediction = (object) [
            'modelid' => $modelid,
            'samplecontext' => $samplecontext,
            'sampleid' => $sampleid,
            'rangeindex' => $rangeindex,
            'prediction' => $scalar_prediction,
            'predictionscore' => $predictionscore
        ];

        $intervention = \mod_motbot\retention\intervention::from_prediction($prediction);

        self::intervene($intervention);
    }


    private static function intervene($intervention) {

        $intervention = \mod_motbot\retention\intervention::from_db($intervention);

        if($intervention->get_target()::always_intervene()) {
            $message = $intervention->get_intervention_message();

            $messageid = \mod_motbot\manager::send_message($message);
            if($messageid) {
                $intervention->set_messageid($messageid);
                $intervention->set_state(\mod_motbot\retention\intervention::INTERVENED);
            }
        }
    }

    public static function get_prediction_subject($sampleid, $target = null) {
        global $DB;
        if($target == '\mod_motbot\analytics\target\upcoming_activities_due') {
            $sql = "SELECT id as userid
                FROM mdl_user
                WHERE id = :sampleid;";

        } else {
            $sql = "SELECT u.id as userid
                    FROM mdl_user u
                    JOIN mdl_user_enrolments ue ON ue.userid = u.id
                    WHERE ue.id = :sampleid;";
        }

        $result = $DB->get_record_sql($sql, array('sampleid' => $sampleid));

        if(!$result) {
            echo('No user found! ' . $sampleid);
            return null;
        }

        return $result->userid;
    }


    public static function send_message($message, $userto = null) {
        if(!$message) {
            return null;
        }

        if(!$message->userto) {
            $message->userto = $userto;
        }


        // Actually send the message
        try{
            $messageid = message_send($message);
        } catch (\moodle_exception $e) {
            print_r($e->getMessage());
        }
        if($messageid) {
            self::set_message_contexturl($messageid);
            echo('Message ' . $messageid . ' sent to User ' . $message->userto->id);
        }

        return $messageid;
    }


    public static function set_message_contexturl($messageid) {
        global $DB;

        if(!$message = $DB->get_record('notifications', array('id' => $messageid), '*', IGNORE_MISSING)) {
            return;
        }

        $message->contexturl = (new \moodle_url('/message/output/popup/notifications.php?notificationid=' . $messageid . '&offset=0'))->out(false); // A relevant URL for the notification
        $message->contexturlname = 'Notification';

        $DB->update_record('notifications', $message);
    }




    public static function replace_intervention_placeholders($subject, $intervention) {
        $recipient = $intervention->get_recipient();
        $motbot = $intervention->get_motbot();
        $course = $intervention->get_course();
        $courseurl = (new \moodle_url('/course/view.php?id=' . $intervention->get_context()->instanceid))->out(false);

        $result = $subject;
        $result = str_replace('{firstname}', $recipient->firstname, $result);
        $result = str_replace('{lastname}', $recipient->lastname, $result);
        $result = str_replace('{motbot}', $motbot->name, $result);
        $result = str_replace('{course_shortname}', $course->shortname, $result);
        $result = str_replace('{course_fullname}', $course->fullname, $result);
        $result = str_replace('{course_url}', $courseurl, $result);
        if(strpos($result, '{suggestions}') !== false) {
            $result = str_replace('{suggestions}', $intervention->advice_manager->render(), $result);
        }
        return $result;
    }
}