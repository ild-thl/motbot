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
    private $desired_events = null;
    private $state = self::SCHEDULED;
    private $teachers_informed = false;
    private $message = null;
    private $helpful = null;
    private $usermodified = null;
    private $timecreated = null;
    private $timemodified = null;
    public $advice_manager = null;


    private function __construct() {

    }

    public static function get_state_name($state) {
        return \get_string('state:' . $state, 'motbot');
    }

    public function get_recipient() {
        global $DB;
        if(!$this->recipientuser) {
            $this->recipientuser = $DB->get_record('user', array('id' => $this->recipient));
        }
        return $this->recipientuser;
    }


    public function get_motbot() {
        global $DB;
        if(!$this->motbot && $this->get_context()->contextlevel == 50) {
            $this->motbot = $DB->get_record('motbot', array('course' => $this->get_context()->instanceid));
        }
        return $this->motbot;
    }

    public function get_context() {
        global $DB;
        if(!$this->context) {
            $this->context = $DB->get_record('context', array('id' => $this->contextid));
        }
        return $this->context;
    }

    public function get_course() {
        global $DB;
        if(!$this->course && $this->get_context()->contextlevel == 50) {
            $this->course = $DB->get_record('course', array('id' => $this->get_context()->instanceid));
        }
        return $this->course;
    }


    public function get_target() {
        return \core_analytics\manager::get_target($this->target);
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

    public function is_critical() {
        global $DB;

        $critical = false;

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
            if($prevint_record->id == $this->id) {
                continue;
            }
            if($prevint_record->state != \mod_motbot\retention\intervention::SUCCESSFUL) {
                $prevint = self::from_db($prevint_record)->set_state(self::UNSUCCESSFUL);
                $critical = true;
            }
        }

        $sql = "SELECT *
                FROM mdl_motbot_course_user mu, mdl_motbot m
                WHERE m.course = :course
                AND m.id = mu.motbot
                AND mu.allow_teacher_involvement = 1
                AND mu.user = :user;";
        $allowed = $DB->get_record_sql($sql, array('course' => $this->get_context()->instanceid, 'user' => $this->recipient));
        echo("_Teacher inv allowed_");
        if(!$critical || !$allowed) {
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
        $intervention->advice_manager = new \mod_motbot\retention\advice_manager($intervention->get_recipient(), $intervention->get_course(), $intervention->target);

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
            'helpful' => $this->helpful,
            'usermodified' => $USER->id,
            'timecreated' => $this->timecreated,
            'timemodified' => time(),
        ];
    }

    private function get_desired_events() {
        return json_encode($this->target::get_desired_events());
    }


    public function get_teacher_message() {
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


    public function get_intervention_message() {
        global $DB, $OUTPUT, $CFG;
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
        $message->subject = \mod_motbot\manager::replace_intervention_placeholders($message->subject, $this);
        $message->fullmessageformat = $db_m ? $db_m->fullmessageformat : FORMAT_MARKDOWN;

        $context = $this->get_context();
        if(!$motbot_sql = $DB->get_record('course_modules', array("course" => $this->get_course()->id, "instance" => $this->get_motbot()->id), "id", IGNORE_MISSING)) {
            throw new \dml_exception("Couldn't get motbot instanceid.");
        }
        $module_context = \context_module::instance($motbot_sql->id);
        $body = $db_m ? $db_m->fullmessagehtml : \get_string('mod_form:' . $target_name . '_fullmessagehtml', 'motbot');
        $body = file_rewrite_pluginfile_urls($body, 'pluginfile.php', $module_context->id, 'mod_motbot', 'attachment', 0);
        $body = \mod_motbot\manager::replace_intervention_placeholders($body, $this);
        $body = str_replace("<p><br></p>", "<br>", $body);

        $helpfulurl = $CFG->wwwroot.'/mod/motbot/intervention_helpful.php?id=' . $this->id . '&helpful=';

        $html_advice = $this->advice_manager->render_html();
        $contextinfo = [
            'usefulbuttons' => ['usefulurl' => $helpfulurl . '1', 'notusefulurl' => $helpfulurl . '0'],
            'advice' => $html_advice,
            'body' => $body
        ];
        $message->fullmessagehtml = $OUTPUT->render_from_template('mod_motbot/intervention_message', $contextinfo);

        $message->fullmessage = $db_m ? \mod_motbot\manager::replace_intervention_placeholders($db_m->fullmessage, $this) : 'message body';


        $message->smallmessage = $db_m ? $db_m->smallmessage : 'small message';
        $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message


        $message->contexturl = (new \moodle_url('/course/view.php?id=' . $this->get_context()->instanceid))->out(false); // A relevant URL for the notification
        $message->contexturlname = 'To Course';
        // Link title explaining where users get to for the contexturl
        // $content = array('*' => array('header' => ' test ', 'footer' => ' test ')); // Extra content for specific processor
        // $message->set_additional_content('email', $content);

        return $message;
    }


    private function update_record() {
        global $DB;

        if(!$DB->update_record('motbot_intervention', $this->get_db_data())) {
            error_log('Couldnt update intervention.');
            return false;
        }

        return true;
    }

    public function set_state($state) {
        $this->state = $state;
        $this->update_record();
    }

    public function set_messageid($messageid) {
        $this->message = $messageid;
        $this->update_record();
    }

    public function set_teachers_informed($teachers_informed) {
        $this->teachers_informed = $teachers_informed;
        $this->update_record();
    }
}