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
 * Task that schedules and executes interventions.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\retention;

defined('MOODLE_INTERNAL') || die();

/**
 * Task that schedules and executes interventions.
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
     *
     * @return void
     */
    public function execute() {
        global $DB;
        // Get all scheduled interventions.
        $scheduled_interventions = $DB->get_records('motbot_intervention', array('state' => \mod_motbot\retention\intervention::SCHEDULED), '', '*');

        $now = new \DateTime("now", \core_date::get_user_timezone_object());
        $dayofweek = intval($now->format('N'));
        $hour = intval($now->format('H'));
        echo ('hour: ' . $hour);
        echo ('dayofweek: ' . $dayofweek);

        // Check for each scheduled intervention, if now is the right time for intervention.
        foreach ($scheduled_interventions as $intervention) {
            // Get the preferred time from the intervention recipient.
            $user_pref = $DB->get_record('motbot_user', array('user' => $intervention->recipient), 'pref_time, only_weekdays', IGNORE_MISSING);

            // Check if now time is in a 3 hour period after the set prefered time.
            if (!$user_pref->only_weekdays || ($user_pref->only_weekdays && $dayofweek < 6)) {
                if ($user_pref->pref_time > -1 && $hour >= $user_pref->pref_time && $hour <= ($user_pref->pref_time - 3)) {
                    $this->intervene($intervention);
                } else {
                    echo ('later...');
                }
            } else {
                echo ('only during weekdays, later...');
            }
        }
    }

    /**
     * Send intervention message. If necessary also send message to teacher.
     *
     * @param \mod_motbot\retention\intervention
     * @return void
     */
    private function intervene($scheduled_intervention) {
        $intervention = \mod_motbot\retention\intervention::from_db($scheduled_intervention);

        if ($intervention->get_target()::always_intervene()) {
            $message = $intervention->get_intervention_message();

            $messageid = \mod_motbot\manager::send_message($message);
            if ($messageid) {
                $intervention->set_messageid($messageid);
                $intervention->set_state(\mod_motbot\retention\intervention::INTERVENED);
            }
        } else {
            $intervention->set_state(\mod_motbot\retention\intervention::STORED);
        }

        if ($intervention->get_context()->contextlevel == 50) {
            if ($intervention->is_critical()) {
                $this->inform_teachers($intervention);
            }
        }
    }

    /**
     * Inform teachers that are responsible for the student or the course context,
     * about intervention history of the intervention subject.
     *
     * @param \mod_motbot\retention\intervention
     * @return void
     */
    private function inform_teachers($intervention) {
        global $DB;

        $role = $DB->get_record('role', array('shortname' => 'editingteacher'));
        $context = \context_course::instance($intervention->get_context()->instanceid);
        $teachers = get_role_users($role->id, $context);

        if (!$teachers || empty($teachers)) {
            return;
        }

        $intervention->set_teachers_informed(true);
        $sent = false;

        $message = $intervention->get_teacher_message();
        foreach ($teachers as $teacher) {
            $userto = $DB->get_record('user', array('id' => $teacher->id), '*');
            if (\mod_motbot\manager::send_message($message, $userto)) {
                $sent = true;
            } else {
                echo ('Couldnt send message to ' . $teacher->id);
            }
        }

        if (!$sent) {
            $intervention->set_teachers_informed(false);
        }
    }
}
