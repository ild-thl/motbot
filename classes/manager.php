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

    /**
     * Creates interventions with prediction data.
     *
     * @param int $modelid
     * @param int $sampleid
     * @param int $rangeindex
     * @param \context $samplecontext
     * @param float|int $scalar_prediction
     * @param float $predictionscore
     * @return void
     */
    public static function log_prediction($modelid, $sampleid, $rangeindex, \context $samplecontext, $result, $score) {

        if ($score < 0.7) {
            // Ignore prediction results that are not very accurate.
            \core\notification::info("Ignored prediction result due to lack of accuracy: score = " . $score);
            return;
        }

        $prediction = (object) [
            'modelid' => $modelid,
            'samplecontext' => $samplecontext,
            'sampleid' => $sampleid,
            'rangeindex' => $rangeindex,
            'result' => $result,
            'score' => $score
        ];

        $intervention = \mod_motbot\retention\intervention::from_prediction($prediction);

        self::intervene($intervention);
    }

    /**
     * Only call this method for development purposes to skip the scheduling.
     * Generates and sends a message to the intervention recipient.
     *
     * @param \mod_motbot\retention\intervention $intervention
     * @return void
     */
    private static function intervene($intervention) {

        // $intervention = \mod_motbot\retention\intervention::from_db($intervention);

        $message = $intervention->get_intervention_message();

        $messageid = \mod_motbot\manager::send_message($message);
        if ($messageid) {
            $intervention->set_messageid($messageid);
            $intervention->set_state(\mod_motbot\retention\intervention::INTERVENED);
        }
    }


    /**
     * Gets the id of the user, about which a prediction has made a stament.
     *
     * @param int $sampleid Samplid of a prediction.
     * @param string $target Prediction target.
     * @return int User id - Prediction subject.
     */
    public static function get_prediction_subject($sampleid, $target = null) {
        global $DB;

        if ($target == '\mod_motbot\analytics\target\upcoming_activities_due' || $target == '\mod_motbot\analytics\target\recent_cognitive_presence') {
            // In this case the sampleid is equal to the userid.
            return $sampleid;
        }

        $sql = "SELECT u.id as userid
            FROM mdl_user u
            JOIN mdl_user_enrolments ue ON ue.userid = u.id
            WHERE ue.id = :sampleid;";
        $result = $DB->get_record_sql($sql, array('sampleid' => $sampleid));

        if (!$result) {
            echo ('No user found! ' . $sampleid);
            return null;
        }

        return $result->userid;
    }

    /**
     * Sends a message to a user.
     *
     * @param object $message
     * @param \core\user $userto
     * @return int|null Id of sent message, null the message couldn't be sent.
     */
    public static function send_message($message, $userto = null) {
        if (!$message) {
            return null;
        }

        // Set recipient, if not already set.
        if (!$message->userto) {
            $message->userto = $userto;
        }


        // Actually send the message
        try {
            $messageid = message_send($message);
        } catch (\moodle_exception $e) {
            print_r($e->getMessage());
        }

        // Update contexturl with messageid, so user get redirected to the full message, wehen they click on a notification.
        if ($messageid) {
            self::set_message_contexturl($messageid);
            echo ('Message ' . $messageid . ' sent to User ' . $message->userto->id);
        }

        return $messageid;
    }

    /**
     * Update the context_url of a message, with a url to the message itself.
     *
     * @param int $messageid
     * @return void
     */
    public static function set_message_contexturl($messageid) {
        global $DB;

        if (!$message = $DB->get_record('notifications', array('id' => $messageid), '*', IGNORE_MISSING)) {
            return;
        }

        // URl leading to the message itself.
        $message->contexturl = (new \moodle_url('/message/output/popup/notifications.php?notificationid=' . $messageid . '&offset=0'))->out(false); // A relevant URL for the notification
        $message->contexturlname = 'Notification';

        $DB->update_record('notifications', $message);
    }

    /**
     * Replace placeholders in intervention messages with the requested content.
     *
     * @param string $text
     * @param \mod_motbot\retention\intervention $intervention
     * @return string
     */
    public static function replace_intervention_placeholders($text, $intervention) {
        $result = $text;

        if ($recipient = $intervention->get_recipient()) {
            $result = str_replace('{firstname}', $recipient->firstname, $result);
            $result = str_replace('{lastname}', $recipient->lastname, $result);
        }

        $context = $intervention->get_context();
        if ($context->contextlevel == 50) {
            // If intervention in a course level context.
            if ($motbot = $intervention->get_motbot()) {
                $result = str_replace('{motbot}', $motbot->name, $result);
            }

            if ($course = $intervention->get_course()) {
                $result = str_replace('{course_shortname}', $course->shortname, $result);
                $result = str_replace('{course_fullname}', $course->fullname, $result);

                $courseurl = (new \moodle_url('/course/view.php?id=' . $context->instanceid))->out(false);
                $result = str_replace('{course_url}', $courseurl, $result);
            }
        } else {
            $result = str_replace('{motbot}', 'MotBot', $result);
        }

        if (strpos($result, '{suggestions}') !== false) {
            $result = str_replace('{suggestions}', $intervention->get_advice_manager()->render(), $result);
        }
        return $result;
    }
}
