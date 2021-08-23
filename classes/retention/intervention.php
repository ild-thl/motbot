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
 * Interaction.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\retention;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/motbot/locallib.php');

class intervention {
    const SCHEDULED = 0;
    const INTERVENED = 1;
    const SUCCESSFUL = 2;
    const UNSUCCESSFUL = 3;
    const STORED = 4;

    private $id = null;
    private $recipient = null;
    private $recipientuser = null;
    private $motbot = null;
    private $contextid = null;
    private $context = null;
    private $course = null;
    private $target = null;
    private $target_target = null;
    private $desired_events = null;
    private $state = self::SCHEDULED;
    private $teachers_informed = false;
    private $message = null;
    private $usermodified = null;
    private $timecreated = null;
    private $timemodified = null;


    private function __construct() {

    }

    public static function get_state_name($state) {
        return \get_string('state:' . $state, 'motbot');
    }

    private function get_recipient() {
        global $DB;
        if(!$this->recipientuser) {
            $this->recipientuser = $DB->get_record('user', array('id' => $this->recipient));
        }
        return $this->recipientuser;
    }


    private function get_motbot() {
        global $DB;
        if(!$this->motbot && $this->get_context()->contextlevel == 50) {
            $this->motbot = $DB->get_record('motbot', array('course' => $this->get_context()->instanceid));
        }
        return $this->motbot;
    }

    private function get_context() {
        global $DB;
        if(!$this->context) {
            $this->context = $DB->get_record('context', array('id' => $this->contextid));
        }
        return $this->context;
    }

    private function get_course() {
        global $DB;
        if(!$this->course && $this->get_context()->contextlevel == 50) {
            $this->course = $DB->get_record('course', array('id' => $this->get_context()->instanceid));
        }
        return $this->course;
    }


    private function get_target() {
        global $DB;
        if(!$this->target_target) {
            $this->target_target = \core_analytics\manager::get_target($this->target);
        }
        return $this->target_target;
    }


    public static function from_prediction($prediction) {
        global $DB;

        $intervention = new self();

        // Get target of ananlytics model.
        $model = $DB->get_record('analytics_models', array('id'=> $prediction->modelid), 'target');
        if(!$model) {
            error_log('Model not found.');
            return;
        }
        $intervention->target = $model->target;

        // Get recipient id.
        $recipientid = \mod_motbot\manager::get_prediction_subject($prediction->sampleid, $intervention->target);
        if(!$recipientid) {
            error_log('no subject');
            return;
        }
        $intervention->recipient = $recipientid;

        $intervention->contextid = $prediction->samplecontext->id;
        $intervention->desired_events = $intervention->get_desired_events();

        // Create DB entry.
        $intervention->id = $DB->insert_record('motbot_intervention', $intervention->get_db_data());
        if(!$intervention->id) {
            error_log('Intervention couldnt be inserted into DB');
            return;
        }

        return $intervention;
    }

    private function is_critical() {
        global $DB;

        if(!$this->target::is_critical()) {
            return false;
        }

        // Check for previous interventions that werent succesful and mark them as such
        $prevints_conditions = array(
            'recipient' => $this->recipient,
            'contextid' => $this->contextid,
            'desired_events' => $this->desired_events,
            'state' => \mod_motbot\retention\intervention::INTERVENED,
        );
        $prevint_records = $DB->get_records('motbot_intervention', $prevints_conditions, 'id');
        foreach($prevint_records as $prevint_record) {
            $prevint = self::from_db($prevint_record)->set_state(self::UNSUCCESSFUL);
        }

        $sql = "SELECT *
                FROM mdl_motbot_course_user mu, mdl_motbot m
                WHERE m.course = :course
                AND m.id = mu.motbot
                AND mu.allow_teacher_involvement = 1
                AND mu.user = :user;";
        $allowed = $DB->get_record_sql($sql, array('course' => $this->get_context()->instanceid, 'user' => $this->recipient));

        if(empty($prevint_records) || !$allowed) {
            return false;
        }
        return true;
    }

    public static function from_db($record) {
        $intervention = new self();

        $intervention->id = $record->id;
        $intervention->recipient = $record->recipient;
        $intervention->contextid = $record->contextid;
        $intervention->desired_events = $record->desired_events;
        $intervention->target = $record->target;
        $intervention->state = $record->state;
        $intervention->teachers_informed = $record->teachers_informed;
        $intervention->message = $record->message;
        $intervention->usermodified = $record->usermodified;
        $intervention->timecreated = $record->timecreated;
        $intervention->timemodified = $record->timemodified;

        return $intervention;
    }

    private function get_db_data() {
        global $USER;

        if(!$this->timecreated) {
            $this->timecreated = time();
        }

        return (object) [
            'id' => $this->id,
            'recipient' => $this->recipient,
            'contextid' => $this->contextid,
            'desired_events' => $this->desired_events,
            'target' => $this->target,
            'state' => $this->state,
            'teachers_informed' => $this->teachers_informed,
            'message' => $this->message,
            'usermodified' => $USER->id,
            'timecreated' => $this->timecreated,
            'timemodified' => time(),
        ];
    }

    private function get_desired_events() {
        return $this->target::get_desired_events();
    }


    private function send_message($message, $userto = null) {
        if(!$message) {
            return;
        }

        if(!$message->userto) {
            $message->userto = $userto;
        }


        // Actually send the message
        $this->message = message_send($message);
        if(!$this->message) {
            return false;
        }

        $this->update_record();
        echo('Message ' . $this->message . ' sent to User ' . $message->userto->id);

        return true;
    }


    private function create_teacher_message() {
        global $DB;
        $target_name = \mod_motbot_get_name_of_target($this->target);

        if(!$target_name || empty($target_name)) {
            error_log('Target name couldnt be identified.');
            return null;
        }

        $recipient = $this->get_recipient();

        $message = new \core\message\message();
        $message->component = 'mod_motbot'; // Your plugin's name
        $message->name = 'motbot_teacher_intervention'; // Your notification name from message.php
        $message->userfrom = \core_user::get_noreply_user(); // If the message is 'from' a specific user you can set them here
        $message->userto = null;
        $message->subject = \get_string('message:teacher_subject', 'motbot', $recipient->firstname . ' ' . $recipient->lastname);
        $message->fullmessage = 'message body';
        $message->fullmessageformat = FORMAT_HTML;
        $message->fullmessagehtml = \get_string('message:teacher_fullmessagehtml', 'motbot', (object)['fullname' => $recipient->firstname . ' ' . $recipient->lastname, 'interventions' => mod_motbot_get_interventions_table($this->recipient, $this->contextid)]);
        $message->smallmessage = 'small message';
        $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message
        $message->contexturl = (new \moodle_url('/course/view.php?id=' . $this->get_context()->instanceid))->out(false); // A relevant URL for the notification
        $message->contexturlname = 'To Course'; // Link title explaining where users get to for the contexturl

        return $message;
    }


    private function create_intervention_message() {
        global $DB;
        $target_name = \mod_motbot_get_name_of_target($this->target);

        if(!$target_name || empty($target_name)) {
            error_log('Target name couldnt be identified.');
            return null;
        }

        $recipient = $this->get_recipient();
        $sql = "SELECT m.subject, m.fullmessage, m.fullmessageformat, m.fullmessagehtml, m.smallmessage, m.attachementuri
                FROM mdl_motbot_message m
                JOIN mdl_motbot motbot ON m.motbot = motbot.id
                WHERE motbot.course = :course AND m.target = :target;";
        $db_m = $DB->get_record_sql($sql, array('course' => $this->get_context()->instanceid, 'target' => $this->target));

        if(!$db_m) {
            echo('no message template found in db');
        }

        $message = new \core\message\message();
        $message->component = 'mod_motbot'; // Your plugin's name
        $message->name = 'motbot_intervention'; // Your notification name from message.php
        $message->userfrom = \core_user::get_noreply_user(); // If the message is 'from' a specific user you can set them here
        $message->userto = $recipient;
        $message->subject = $db_m ? $db_m->subject : \get_string('mod_form:' . $target_name . '_subject', 'motbot');
        $message->subject = $this->replace_placeholders($message->subject);

        $message->fullmessage = $db_m ? $db_m->fullmessage : 'message body';
        $message->fullmessageformat = $db_m ? $db_m->fullmessageformat : FORMAT_MARKDOWN;

        $context = $this->get_context();
        $message->fullmessagehtml = $db_m ? $db_m->fullmessagehtml : \get_string('mod_form:' . $target_name . '_fullmessagehtml', 'motbot');
        $message->fullmessagehtml = file_rewrite_pluginfile_urls($message->fullmessagehtml, 'pluginfile.php', $context->id, 'mod_motbot', 'attachment', 0);
        $message->fullmessagehtml = $this->replace_placeholders($message->fullmessagehtml);

        $message->smallmessage = $db_m ? $db_m->smallmessage : 'small message';
        $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message


        $message->contexturl = (new \moodle_url('/course/view.php?id=' . $this->get_context()->instanceid))->out(false); // A relevant URL for the notification
        $message->contexturlname = 'To Course'; // Link title explaining where users get to for the contexturl
        // $content = array('*' => array('header' => ' test ', 'footer' => ' test ')); // Extra content for specific processor
        // $message->set_additional_content('email', $content);

        return $message;
    }

    private function replace_placeholders($subject) {
        $recipient = $this->get_recipient();
        $motbot = $this->get_motbot();
        $course = $this->get_course();

        $result = $subject;
        $result = str_replace('{firstname}', $recipient->firstname, $result);
        $result = str_replace('{lastname}', $recipient->lastname, $result);
        $result = str_replace('{motbot}', $motbot->name, $result);
        $result = str_replace('{course_shortname}', $course->shortname, $result);
        $result = str_replace('{course_fullname}', $course->fullname, $result);

        return $result;
    }

    private function update_record() {
        global $DB;

        if(!$DB->update_record('motbot_intervention', $this->get_db_data())) {
            error_log('Couldnt update intervention.');
            return false;
        }

        return true;
    }

    public function execute() {
        // TODO: Schedule..

        if($this->target::always_intervene()) {
            $this->intervene();
        } else {
            $this->set_state(self::STORED);
        }


        if($this->get_context()->contextlevel == 50) {
            if($this->is_critical()) {
                $this->inform_teachers();
            }
        }
    }

    public function inform_teachers() {
        global $DB;

        $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $context = \context_course::instance($this->get_context()->instanceid);
        $teachers = get_role_users($role->id, $context);

        if(!$teachers || empty($teachers)) {
            return;
        }


        $this->teachers_informed = true;
        $this->update_record();
        $sent = false;

        $message = $this->create_teacher_message();
        foreach($teachers as $teacher) {
            $userto = $DB->get_record('user', array('id' => $teacher->id), '*');
            if($this->send_message($message, $userto)) {
                $sent = true;
            } else {
                echo('Couldnt send message to ' . $teacher->id);
            }
        }
        if(!$sent) {
            $this->teachers_informed = true;
        }

        $this->update_record();
    }

    private function intervene() {
        switch($this->target) {
            default:
                $message = $this->create_intervention_message();
        }

        if($this->send_message($message)) {
            $this->set_state(self::INTERVENED);
        }
    }

    public function set_state($state) {
        $this->state = $state;
        $this->update_record();
    }
}