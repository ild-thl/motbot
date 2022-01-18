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
 * Intervention.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\retention;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/motbot/locallib.php');

/**
 * Intervention.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class intervention {
    const SCHEDULED = 0;
    const INTERVENED = 1;
    const SUCCESSFUL = 2;
    const UNSUCCESSFUL = 3;
    const STORED = 4;

    /**
     * @var int DB id of this intervention.
     */
    private $id = null;

    /**
     * @var int User id of the intervention recipient.
     */
    private $recipient = null;

    /**
     * @var \core\user Intervention recipient.
     */
    private $recipientuser = null;

    /**
     * @var object Motbot instance.
     */
    private $motbot = null;

    /**
     * @var string Name of the model that produced the prediction this intervention is based on.
     */
    private $model = null;

    /**
     * @var int Id of the context of the prediction this intervention is based on.
     */
    private $contextid = null;

    /**
     * @var \context Context object with id $this->contextid.
     */
    private $context = null;

    /**
     * @var \core\course Course for wich a prediction was made.
     */
    private $course = null;

    /**
     * @var core_analytics\local\target\base Target of the model that produced the prediction this intervention is based on.
     */
    private $target = null;

    /**
     * @var string[] Array of desirable moodle events.
     */
    private $desired_events = null;

    /**
     * @var int State of the intervention.
     */
    private $state = self::SCHEDULED;

    /**
     * @var bool Wether a user allows the involvement their teacher in certain conditions.
     */
    private $teachers_informed = false;

    /**
     * @var int The id of the message that is sent to the user in the course of the intervention.
     */
    private $message = null;

    /**
     * @var bool Wether a user has marked this intervention as helpful or not.
     */
    private $helpful = null;

    /**
     * @var int The id of the user that last modified the DB entry of this intervention.
     */
    private $usermodified = null;

    /**
     * @var int The time a DB entry for this intervention was inserted.
     */
    private $timecreated = null;

    /**
     * @var int The Time, when the DB entry of this intervention was last modified.
     */
    private $timemodified = null;

    /**
     * @var \mod_motbot\retention\advice_manager Manages and generates advices for this intervention.
     */
    public $advice_manager = null;

    /**
     * @var int The scalar prediction result.
     */
    private $prediction = null;


    /**
     * Constructor.
     *
     * @return void
     */
    private function __construct() {
    }

    /**
     * Get the lang string describing the current state of the intervention.
     *
     * @param int $state
     * @return string
     */
    public static function get_state_name($state) {
        return \get_string('state:' . $state, 'motbot');
    }

    /**
     * Caches the user information belonging to the recipient id.
     *
     * @return \core\user
     */
    public function get_recipient() {
        global $DB;
        if (!$this->recipientuser) {
            $this->recipientuser = $DB->get_record('user', array('id' => $this->recipient));
        }
        return $this->recipientuser;
    }


    /**
     * Caches the motbot db info.
     *
     * @return object
     */
    public function get_motbot() {
        global $DB;
        if (!$this->get_context()->contextlevel == 50) {
            return null;
        }
        if (!$this->motbot) {
            $this->motbot = $DB->get_record('motbot', array('course' => $this->get_context()->instanceid));
        }
        return $this->motbot;
    }

    /**
     * Caches the context db info.
     *
     * @return \context
     */
    public function get_context() {
        global $DB;
        if (!$this->context) {
            $this->context = $DB->get_record('context', array('id' => $this->contextid));
        }
        return $this->context;
    }

    /**
     * Caches the course db info.
     *
     * @return \core\course
     */
    public function get_course() {
        global $DB;
        if (!$this->get_context()->contextlevel == 50) {
            return null;
        }
        if (!$this->course) {
            $this->course = $DB->get_record('course', array('id' => $this->get_context()->instanceid));
        }
        return $this->course;
    }

    /**
     * Chaches the analytics target.
     *
     * @return core_analytics\local\target
     */
    public function get_target() {
        return \core_analytics\manager::get_target($this->target);
    }

    /**
     * Chaches the advice_manager.
     *
     * @return core_analytics\local\target
     */
    public function get_advice_manager() {
        if (!$this->advice_manager) {
            $this->advice_manager = new \mod_motbot\retention\advice_manager($this->get_recipient(), $this->get_course(), $this->target);
        }

        return $this->advice_manager;
    }



    /**
     * Factory constructor for initialising an intervention based on prediction data.
     *
     * @param object $prediction
     * @return mod_motbot\retention\intervention
     */
    public static function from_prediction($prediction) {
        global $DB;

        $intervention = new self();
        $intervention->prediction = $prediction->result;
        $intervention->contextid = $prediction->samplecontext->id;
        if ($motbot = $intervention->get_motbot()) {
            $motbot_model = $DB->get_record('motbot_model', array('motbot' => $motbot->id, 'model' => $prediction->modelid), 'id, target', IGNORE_MISSING);
            $intervention->model = $motbot_model->id;
            $intervention->target = $motbot_model->target;
        } else {
            $analytics_model = $DB->get_record('analytics_models', array('id' => $prediction->modelid), 'id, target', IGNORE_MISSING);
            if ($analytics_model) {
                $intervention->target = $analytics_model->target;
            }
        }

        // set prediction to null, if there is only one prediction outcome to be expected.
        $target = $intervention->get_target();
        if ($target instanceof \core_analytics\local\target\discrete) {
            $classes = $target::get_classes();
            $ignored = $target->ignored_predicted_classes();
            if (count($classes) - \count($ignored) <= 1) {
                $intervention->prediction = null;
            }
        }

        // Get recipient id.
        $recipientid = \mod_motbot\manager::get_prediction_subject($prediction->sampleid, $intervention->target);
        if (!$recipientid) {
            error_log('no subject');
            return;
        }
        $intervention->recipient = $recipientid;

        $intervention->desired_events = $intervention->get_desired_events();

        // Create DB entry.
        $intervention->id = $DB->insert_record('motbot_intervention', $intervention->get_db_data());
        if (!$intervention->id) {
            error_log('Intervention couldnt be inserted into DB');
            return;
        }

        return $intervention;
    }

    /**
     * Factory constructor. Initializes an intervention based on a DB record.
     *
     * @param object $record
     * @return \mod_motbot\retention\intervention
     */
    public static function from_db($record) {
        $intervention = new self();

        $intervention->id = $record->id;
        $intervention->recipient = $record->recipient;
        $intervention->model = $record->model;
        $intervention->contextid = $record->contextid;
        $intervention->desired_events = $record->desired_events;
        $intervention->target = $record->target;
        $intervention->prediction = $record->prediction;
        $intervention->state = $record->state;
        $intervention->teachers_informed = $record->teachers_informed;
        $intervention->message = $record->message;
        $intervention->usermodified = $record->usermodified;
        $intervention->timecreated = $record->timecreated;
        $intervention->timemodified = $record->timemodified;

        return $intervention;
    }

    /**
     * Checks if an intervention is critical. An intervention is critical,
     * if there are previous unsuccessful interventions.
     *
     * @return bool
     */
    public function is_critical() {
        global $DB;

        $critical = false;

        // If the result of the targets is_critical method is false,
        // don't check previous interventions - return false.
        if (!$this->target::is_critical()) {
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
        foreach ($prevint_records as $prevint_record) {
            if ($prevint_record->id == $this->id) {
                continue;
            }
            if ($prevint_record->state != \mod_motbot\retention\intervention::SUCCESSFUL) {
                self::from_db($prevint_record)->set_state(\mod_motbot\retention\intervention::UNSUCCESSFUL);
                $critical = true;
            }
        }

        // Check wether user has allowed teacher involvement
        $sql = "SELECT *
                FROM mdl_motbot_course_user mu, mdl_motbot m
                WHERE m.course = :course
                AND m.id = mu.motbot
                AND mu.allow_teacher_involvement = 1
                AND mu.user = :user;";
        $allowed = $DB->get_record_sql($sql, array('course' => $this->get_context()->instanceid, 'user' => $this->recipient));
        echo ("_Teacher inv allowed_");
        if (!$critical || !$allowed) {
            return false;
        }
        return true;
    }


    /**
     * Generates an object fit for inserting or updating th db based on current intervention state.
     *
     * @return object
     */
    private function get_db_data() {
        global $USER;

        if (!$this->timecreated) {
            $this->timecreated = time();
        }

        return (object) [
            'id' => $this->id,
            'recipient' => $this->recipient,
            'contextid' => $this->contextid,
            'model' => $this->model,
            'desired_events' => $this->desired_events,
            'target' => $this->target,
            'prediction' => $this->prediction,
            'state' => $this->state,
            'teachers_informed' => $this->teachers_informed,
            'message' => $this->message,
            'helpful' => $this->helpful,
            'usermodified' => $USER->id,
            'timecreated' => $this->timecreated,
            'timemodified' => time(),
        ];
    }

    /**
     * Generates a json string based on an array of deired event, defined in the target.
     *
     * @return string
     */
    private function get_desired_events() {
        return json_encode($this->target::get_desired_events());
    }


    /**
     * Generates a message meant for the recipients teacher.
     *
     * @return object
     */
    public function get_teacher_message() {
        global $DB;
        $target_name = \mod_motbot_get_name_of_target($this->target);

        if (!$target_name || empty($target_name)) {
            error_log('Target name couldnt be identified.');
            return null;
        }

        $recipient = $this->get_recipient();

        $message = new \core\message\message();
        $message->component = 'mod_motbot'; // Your plugin's name.
        $message->name = 'motbot_teacher_intervention'; // Your notification name from message.php.
        $message->userfrom = \core_user::get_noreply_user(); // If the message is 'from' a specific user you can set them here.
        $message->userto = null;
        $message->subject = new \lang_string('message:teacher_subject', 'motbot', $recipient->firstname . ' ' . $recipient->lastname, $recipient->lang);
        $message->fullmessageformat = FORMAT_HTML;
        $message->fullmessagehtml = new \lang_string('message:teacher_fullmessagehtml', 'motbot', (object)['fullname' => $recipient->firstname . ' ' . $recipient->lastname, 'interventions' => mod_motbot_get_interventions_table($this->recipient, $this->contextid)], $recipient->lang);
        $message->fullmessage = strip_tags($message->fullmessagehtml);
        $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message.
        $message->contexturl = (new \moodle_url('/course/view.php?id=' . $this->get_context()->instanceid))->out(false); // A relevant URL for the notification.
        $message->contexturlname = new \lang_string('motbot:gotocourse', 'motbot', null, $recipient->lang); // Link title explaining where users get to for the contexturl.

        return $message;
    }

    /**
     * Generates the intervention message containing individually generated suggestions.
     *
     * @return object
     */
    public function get_intervention_message() {
        global $DB, $OUTPUT, $CFG;
        $target_name = \mod_motbot_get_name_of_target($this->target);

        if (!$target_name || empty($target_name)) {
            error_log('Target name couldnt be identified.');
            return null;
        }

        $recipient = $this->get_recipient();


        // Retrieve message template.
        if ($this->get_context()->contextlevel == 50) {
            $sql = "SELECT m.subject, m.fullmessage, m.fullmessagehtml, m.prediction, m.custom
                    FROM mdl_motbot_model m
                    JOIN mdl_motbot motbot ON m.motbot = motbot.id
                    WHERE motbot.course = :course
                    AND m.target = :target
                    AND m.prediction = :prediction";
            $motbot_model = $DB->get_record_sql($sql, array('course' => $this->get_context()->instanceid, 'target' => $this->target, 'prediction' => $this->prediction));
        } else {
            $motbot_model = $DB->get_record(
                'motbot_model',
                array('motbot' => null, 'target' => $this->target, 'prediction' => $this->prediction),
                'subject, fullmessage, fullmessagehtml, prediction, custom',
                IGNORE_MISSING
            );
        }

        $message = new \core\message\message();
        $message->component = 'mod_motbot'; // Your plugin's name.
        $message->name = 'motbot_intervention'; // Your notification name from message.php.
        $message->userfrom = \core_user::get_noreply_user(); // If the message is 'from' a specific user you can set them here.
        $message->userto = $recipient;
        $message->fullmessageformat = FORMAT_MARKDOWN;

        if ($motbot_model) {
            if ($motbot_model->custom) {
                $message->subject = $motbot_model->subject;
                $body = $motbot_model->fullmessagehtml;
                $message->fullmessage = $motbot_model->fullmessage;
            } else {
                $message->subject = new \lang_string('mod_form:' . $target_name . '_subject_' . $motbot_model->prediction, 'motbot', null, $recipient->lang);
                $body = new \lang_string('mod_form:' . $target_name . '_fullmessagehtml_' . $motbot_model->prediction, 'motbot', null, $recipient->lang);
                $message->fullmessage = new \lang_string('mod_form:' . $target_name . '_fullmessage_' . $motbot_model->prediction, 'motbot', null, $recipient->lang);
            }
        } else {
            $message->subject = new \lang_string('mod_form:' . $target_name . '_subject', 'motbot', null, $recipient->lang);
            $body = new \lang_string('mod_form:' . $target_name . '_fullmessagehtml', 'motbot', null, $recipient->lang);
            $message->fullmessage = new \lang_string('mod_form:' . $target_name . '_fullmessage', 'motbot', null, $recipient->lang);
        }
        $message->subject = \mod_motbot\manager::replace_intervention_placeholders($message->subject, $this);
        $message->fullmessage = \mod_motbot\manager::replace_intervention_placeholders($message->fullmessage, $this);

        // Get module context.
        if ($this->get_context()->contextlevel == 50) {
            $sql = 'SELECT cm.id
                FROM {course_modules} AS cm
                JOIN {modules} AS m
                ON m.id = cm.module
                WHERE m.name = :module_name
                AND cm.course = :course
                AND cm.instance = :motbot';
            $params = array("course" => $this->get_course()->id, "motbot" => $this->get_motbot()->id, "module_name" => 'motbot');
            if (!$course_module = $DB->get_record_sql($sql, $params, IGNORE_MISSING)) {
                throw new \dml_exception("Couldn't get motbot instanceid.");
            }
            $module_context = \context_module::instance($course_module->id);

            // This step is need so that pictures and other attachements can be displayed correctly.
            $body = file_rewrite_pluginfile_urls($body, 'pluginfile.php', $module_context->id, 'mod_motbot', 'attachment', 0);
        }
        // Insert placeholder info.
        $body = \mod_motbot\manager::replace_intervention_placeholders($body, $this);

        $helpfulurl = $CFG->wwwroot . '/mod/motbot/intervention_helpful.php?id=' . $this->id . '&helpful=';

        // Get advice/suggestions as html.
        $html_advice = $this->get_advice_manager()->render_html();
        // Contextinfo to be displayed in the intervention mustache template.
        $contextinfo = [
            'usefulbuttons' => ['usefulurl' => $helpfulurl . '1', 'notusefulurl' => $helpfulurl . '0'],
            'advice' => $html_advice,
            'body' => $body
        ];
        $message->fullmessagehtml = $OUTPUT->render_from_template('mod_motbot/intervention_message', $contextinfo);


        $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message

        $message->contexturl = (new \moodle_url('/course/view.php?id=' . $this->get_context()->instanceid))->out(false); // A relevant URL for the notification
        $message->contexturlname = new \lang_string('motbot:gotocourse', 'motbot', null, $recipient->lang);

        return $message;
    }

    /**
     * Update intervention DB record based on current intervention objects state.
     *
     * @return bool
     */
    private function update_record() {
        global $DB;

        if (!$DB->update_record('motbot_intervention', $this->get_db_data())) {
            error_log('Couldnt update intervention.');
            return false;
        }

        return true;
    }

    /**
     * Set the state property and updates DB.
     *
     * @return void
     */
    public function set_state($state) {
        $this->state = $state;
        $this->update_record();
    }

    /**
     * Set the message property and updates DB.
     *
     * @return void
     */
    public function set_messageid($messageid) {
        $this->message = $messageid;
        $this->update_record();
    }

    /**
     * Set the teachers_informed property and updates DB.
     *
     * @return void
     */
    public function set_teachers_informed($teachers_informed) {
        $this->teachers_informed = $teachers_informed;
        $this->update_record();
    }
}
