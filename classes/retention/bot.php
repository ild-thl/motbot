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
 * Manages predictions and creates interventions.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\retention;

defined('MOODLE_INTERNAL') || die();

/**
 * Manages predictions and creates interventions.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class bot extends \core\task\scheduled_task {

    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name() {
        return get_string('bot', 'mod_motbot');
    }

    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;
        $motbot_users = $DB->get_records('motbot_user');
        foreach($motbot_users as $mbuser) {
            $scheduled_interventions = $DB->get_records('motbot_intervention', array('recipient' => $mbuser->user, 'state' => \mod_motbot\retention\intervention::SCHEDULED), '', '*');

            $this->execute_interventions_individually($scheduled_interventions);
        }
    }

    private function execute_interventions_individually($scheduled_interventions) {
        foreach($scheduled_interventions as $i) {
            $intervention = \mod_motbot\retention\intervention::from_db($i);

            if($intervention->get_target()::always_intervene()) {
                $this->intervene($intervention);
            } else {
                $intervention->set_state(\mod_motbot\retention\intervention::STORED);
            }

            if($intervention->get_context()->contextlevel == 50) {
                if($intervention->is_critical()) {
                    $this->inform_teachers($intervention);
                }
            }
        }
    }


    private function execute_interventions_altogether($scheduled_interventions) {
        $message = null;
        foreach($scheduled_interventions as $i) {
            $intervention = \mod_motbot\retention\intervention::from_db($i);

            if($intervention->get_target()::always_intervene()) {
                $m = $intervention->get_intervention_message();
                if($message) {
                    $message->fullmessagehtml .= '</br></br> -------- </br></br>' . $m->fullmessagehtml;

                    $message->fullmessage .= '


 --------


' . $m->fullmessage;

                } else {
                    $message = $m;
                }
            } else {
                $intervention->set_state(\mod_motbot\retention\intervention::STORED);
            }

            if($intervention->get_context()->contextlevel == 50) {
                if($intervention->is_critical()) {
                    $this->inform_teachers($intervention);
                }
            }
        }


        $message->subject = 'You can do better!';

        $messageid = \mod_motbot\manager::send_message($message);
        if($messageid) {
            foreach($scheduled_interventions as $i) {
                $intervention = \mod_motbot\retention\intervention::from_db($i);
                $intervention->set_messageid($messageid);
                $intervention->set_state(\mod_motbot\retention\intervention::INTERVENED);
            }
        }
    }


    private function intervene($intervention) {
        $message = $intervention->get_intervention_message();

        $messageid = \mod_motbot\manager::send_message($message);
        if($messageid) {
            $intervention->set_messageid($messageid);
            $intervention->set_state(\mod_motbot\retention\intervention::INTERVENED);
        }
    }


    private function inform_teachers($intervention) {
        global $DB;

        $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $context = \context_course::instance($intervention->get_context()->instanceid);
        $teachers = get_role_users($role->id, $context);

        if(!$teachers || empty($teachers)) {
            return;
        }


        $intervention->set_teachers_informed(true);
        $sent = false;

        $message = $intervention->get_teacher_message();
        foreach($teachers as $teacher) {
            $userto = $DB->get_record('user', array('id' => $teacher->id), '*');
            if(\mod_motbot\manager::send_message($message, $userto)) {
                $sent = true;
            } else {
                echo('Couldnt send message to ' . $teacher->id);
            }
        }

        if(!$sent) {
            $intervention->set_teachers_informed(false);
        }
    }
}