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

        \core\notification::info('Result: ' . $prediction->result);

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

        $messageid = self::send_message($message);
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
     * @param \core\message\message $message
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

        // Actually send the message.
        try {
            $messageid = message_send($message);

            // Update contexturl with messageid, so user get redirected to the full message, wehen they click on a notification.
            if ($messageid) {
                self::set_message_contexturl($messageid);
                echo ('Message ' . $messageid . ' sent to User ' . $message->userto->id);

                return $messageid;
            }
        } catch (\moodle_exception $e) {
            throw $e;
        }
        return null;
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
        $message->contexturl = (new \moodle_url('/message/output/popup/notifications.php?notificationid=' . $messageid . '&offset=0'))->out(false);
        $message->contexturlname = get_string('motbot:notification', 'motbot');

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

    /**
     * Checks if a Motbot is happy. A Motbot is unhappy, when there are any ongoing interventions.
     *
     * @param int $userid
     * @param int $motbotid
     * @return bool
     */
    public static function is_motbot_happy($userid, $motbotid = null) {
        global $DB;
        if ($motbotid) {
            $motbotcourse = $DB->get_field('motbot', 'course', array('id' => $motbotid), IGNORE_MISSING);
            $coursecontext = \context_course::instance($motbotcourse);
            $motbotmodels = $DB->get_records('motbot_model', array('motbot' => $motbotid), '', 'id, active');
        } else {
            $motbotmodels = $DB->get_records('motbot_model', array(), '', 'id, active');
        }

        foreach ($motbotmodels as $motbotmodel) {
            if (!$motbotmodel->active) {
                continue;
            }
            if ($motbotid) {
                $sql = "SELECT *
                FROM mdl_motbot_intervention
                WHERE recipient = :recipient
                AND model = :model
                AND contextid = :contextid
                ORDER BY timecreated DESC
                LIMIT 1";
                $latestintervention = $DB->get_record_sql($sql, array('contextid' => $coursecontext->id, 'recipient' => $userid, 'model' => $motbotmodel->id), IGNORE_MISSING);
            } else {
                $sql = "SELECT *
                FROM mdl_motbot_intervention
                WHERE recipient = :recipient
                AND model = :model
                ORDER BY timecreated DESC
                LIMIT 1";
                $latestintervention = $DB->get_record_sql($sql, array('recipient' => $userid, 'model' => $motbotmodel->id), IGNORE_MISSING);
            }

            if (!$latestintervention) {
                continue;
            }

            if ($latestintervention->state == \mod_motbot\retention\intervention::INTERVENED ||
                $latestintervention->state == \mod_motbot\retention\intervention::UNSUCCESSFUL ||
                $latestintervention->state == \mod_motbot\retention\intervention::SCHEDULED) {
                return false;
            }
        }
        return true;
    }

    /**
     * Checks if user has enabled MotBot.
     *
     * @param int $userid
     * @param int $motbotid
     * @return bool
     */
    public static function is_motbot_enabled($userid, $motbotid = null) {
        global $DB;

        if ($motbotid == null) {
            return $DB->record_exists('motbot_user', array('user' => $userid, 'authorized' => 1));
        } else {
            return $DB->record_exists('motbot_course_user', array('user' => $userid, 'motbot' => $motbotid, 'authorized' => 1));
        }
    }

    /**
     * Adds a new section to the form. This section allows the use of specific analytics models in a course and to edit the message templates of corresponding interventions.
     *
     * @param \MoodleQuickForm $mform
     * @param \context $context
     * @param object $model
     * @return void
     */
    public static function add_intervention_settings($mform, $context, $model) {
        $targetname = \mod_motbot_get_name_of_target($model->target);

        $mform->addElement('header', $targetname . '_header' . $model->prediction, get_string('mod_form:' . $targetname . '_header', 'motbot') . $model->prediction_description);

        $mform->addElement('hidden', $targetname . '_id' . $model->prediction);
        $mform->setType($targetname . '_id' . $model->prediction, PARAM_RAW);
        $mform->setDefault($targetname . '_id' . $model->prediction, null);

        $mform->addElement('hidden', $targetname . '_motbot' . $model->prediction);
        $mform->setType($targetname . '_motbot' . $model->prediction, PARAM_RAW);
        $mform->setDefault($targetname . '_motbot' . $model->prediction, null);

        $mform->addElement('hidden', $targetname . '_model' . $model->prediction);
        $mform->setType($targetname . '_model' . $model->prediction, PARAM_RAW);

        $mform->addElement('hidden', $targetname . '_target' . $model->prediction);
        $mform->setType($targetname . '_target' . $model->prediction, PARAM_RAW);

        $mform->addElement('hidden', $targetname . '_prediction' . $model->prediction);
        $mform->setType($targetname . '_prediction' . $model->prediction, PARAM_RAW);
        $mform->setDefault($targetname . '_prediction' . $model->prediction, null);

        $mform->addElement('selectyesno', $targetname . '_active' . $model->prediction, get_string('mod_form:active', 'motbot'));
        $mform->addHelpButton($targetname . '_active' . $model->prediction, 'mod_form:active', 'motbot');

        $mform->addElement('selectyesno', $targetname . '_custom' . $model->prediction, get_string('mod_form:custom', 'motbot'));
        $mform->addHelpButton($targetname . '_custom' . $model->prediction, 'mod_form:custom', 'motbot');

        $mform->addElement('text', $targetname . '_subject' . $model->prediction, get_string('mod_form:subject', 'motbot'), array('size' => '64'));
        $mform->setType($targetname . '_subject' . $model->prediction, PARAM_TEXT);
        $mform->addRule($targetname . '_subject' . $model->prediction, get_string('mod_form:too_long', 'motbot', 64), 'maxlength', 64, 'client');
        $mform->disabledIf($targetname . '_subject' . $model->prediction, $targetname . '_custom' . $model->prediction, 'eq', 0);

        $mform->addElement('textarea', $targetname . '_fullmessage' . $model->prediction, get_string('mod_form:fullmessage', 'motbot'), 'wrap="virtual" rows="10" cols="150"');
        $mform->setType($targetname . '_fullmessage' . $model->prediction, PARAM_TEXT);
        $mform->disabledIf($targetname . '_fullmessage' . $model->prediction, $targetname . '_custom' . $model->prediction, 'eq', 0);

        $mform->addElement(
            'editor',
            $targetname . '_fullmessagehtml' . $model->prediction,
            get_string('mod_form:fullmessagehtml', 'motbot'),
            array('rows' => 15), mod_motbot_get_editor_options($context)
        );
        $mform->setType($targetname . '_fullmessagehtml' . $model->prediction, PARAM_RAW);
        $mform->disabledIf($targetname . '_fullmessagehtml' . $model->prediction, $targetname . '_custom' . $model->prediction, 'eq', 0);

        $mform->addElement('hidden', $targetname . '_usermodified' . $model->prediction);
        $mform->setType($targetname . '_usermodified' . $model->prediction, PARAM_ALPHA);

        $mform->addElement('hidden', $targetname . '_timemodified' . $model->prediction);
        $mform->setType($targetname . '_timemodified' . $model->prediction, PARAM_ALPHA);

        $mform->addElement('hidden', $targetname . '_timecreated' . $model->prediction);
        $mform->setType($targetname . '_timecreated' . $model->prediction, PARAM_ALPHA);
    }

    /**
     * Gets all motbot targets.
     *
     * @return array
     */
    public static function get_motbot_targets() {
        global $DB;

        $targets = array();

        // Get all available motbot models.
        $sql = "SELECT target
                FROM mdl_analytics_models
                WHERE target LIKE '%mod_motbot%';";
        if (!$models = $DB->get_records_sql($sql)) {
            return null;
        }

        foreach ($models as $model) {
            $targets[$model->target] = \core_analytics\manager::get_target($model->target);
        }

        return $targets;
    }

    /**
     * Creates default values for valid motbot models or gets prevoius settings, if they exist.
     *
     * @param int $motbotid
     * @return array
     */
    public static function get_motbot_models($motbotid = null) {
        global $DB;

        $modelinfo = array();

        // Get previous model settings for this specific motbot.
        $motbotmodels = $DB->get_records('motbot_model', array('motbot' => $motbotid));

        // Get all available motbot models.
        $sql = "SELECT *
                FROM mdl_analytics_models
                WHERE enabled = 1
                AND target LIKE '%mod_motbot%';";
        $models = $DB->get_records_sql($sql);

        // Create deault values for models, that are valid for this course.
        foreach ($models as $model) {
            if (!$model->target::custom_intervention()) {
                continue;
            }
            // Get variants for possible prediction results.
            $target = \core_analytics\manager::get_target($model->target);

            if ($motbotid) { // If a motbot id is given, only select models that analyse course enrolement specific data.
                if ($target->get_analyser_class() !== '\core\analytics\analyser\student_enrolments') {
                    continue;
                }
            } else { // Else select the non course enrolement specigfic models.
                if ($target->get_analyser_class() === '\core\analytics\analyser\student_enrolments') {
                    continue;
                }
            }
            $classes = $model->target::get_classes();
            $predictions = array();
            $ignored = array();
            if ($target instanceof \core_analytics\local\target\discrete) {
                $ignored = $target->ignored_predicted_classes();

                foreach ($classes as $class) { // Skip classes, for which there won't be any predictions made in the future.
                    if (!in_array($class, $ignored)) {
                        $predictions[] = $class;
                    }
                }
            } else {
                $predictions = $classes;
            }

            foreach ($predictions as $index => $prediction) {
                // Skip models, for which there are already previous records.
                $exists = false;
                foreach ($motbotmodels as $motbotmodel) {
                    if ($model->target == $motbotmodel->target && (count($predictions) <= 1 || $prediction == $motbotmodel->prediction)) {
                        $exists = true;
                        $motbotmodel->prediction_description = count($predictions) > 1 ? '(' . (((int)$index) + 1) . '/' . count($predictions) . ')' : '';
                        $modelinfo[] = $motbotmodel;
                        break;
                    }
                }

                if ($exists) {
                    continue;
                }

                $targetname = \mod_motbot_get_name_of_target($model->target);

                // Set default values.
                $modelinfo[] = (object) [
                    'id' => null,
                    'motbot' => $motbotid,
                    'model' => $model->id,
                    'active' => 1,
                    'custom' => 0,
                    'target' => $model->target,
                    'targetname' => null,
                    'prediction' => count($predictions) > 1 ? $prediction : null,
                    'prediction_description' => count($predictions) > 1 ? ' (' . (((int)$index) + 1) . '/' . count($predictions) . ')' : '',
                    'subject' => get_string('mod_form:' . $targetname . '_subject' . (count($predictions) > 1 ? '_' . $prediction : ''), 'motbot'),
                    'fullmessage' => get_string('mod_form:' . $targetname . '_fullmessage' . (count($predictions) > 1 ? '_' . $prediction : ''), 'motbot'),
                    'fullmessagehtml' => get_string('mod_form:' . $targetname . '_fullmessagehtml' . (count($predictions) > 1 ? '_' . $prediction : ''), 'motbot'),
                    'attachementuri' => null,
                    'usermodified' => null,
                    'timecreated' => null,
                    'timemodified' => null,
                ];
            }
        }

        // Set targetname, and prediction_name property where it isn't already set.
        foreach ($modelinfo as $motbotmodel) {
            if (property_exists($motbotmodel, 'targetname') && $motbotmodel->targetname) {
                continue;
            }

            $targetname = \mod_motbot_get_name_of_target($motbotmodel->target);
            $motbotmodel->targetname = $targetname;
        }

        return $modelinfo;
    }

    /**
     * Creates default values for available motbot advice.
     *
     * @return \mod_motbot\retention\advice[]|\mod_motbot\retention\advice
     */
    public static function get_motbot_advice($id = null) {
        global $DB;

        // Get advice saved in db.
        if ($id) {
            $adviceobjs = $DB->get_records('motbot_advice', array('id' => $id));
        } else {
            $adviceobjs = $DB->get_records('motbot_advice');
        }

        if (!$adviceobjs || empty($adviceobjs)) {
            return array();
        }

        // Discard misconfiguered advice.
        $advicelist = array();
        foreach ($adviceobjs as $obj) {
            $advice = new \mod_motbot\retention\advice($obj);

            if ($advice->is_available()) {
                $advicelist[$obj->id] = $advice;
            }
        }

        if (count($advicelist) === 1) {
            return \reset($advicelist);
        }

        // Sort the advice by the name using the current session language.
        \core_collator::asort_objects_by_method($advicelist, 'get_name');
        return $advicelist;
    }
}
