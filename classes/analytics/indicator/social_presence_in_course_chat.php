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
 * Write actions in a course forum indicator.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\analytics\indicator;

defined('MOODLE_INTERNAL') || die();

/**
 * Write actions in a course indicator.
 *
 * @package   core
 * @copyright 2016 David Monllao {@link http://www.davidmonllao.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class social_presence_in_course_chat extends \mod_motbot\analytics\indicator\social_presence_in_course_activity {


    /**
     * Returns the name.
     *
     * If there is a corresponding '_help' string this will be shown as well.
     *
     * @return \lang_string
     */
    public static function get_name() : \lang_string {
        return new \lang_string('indicator:socialpresenceincoursechat', 'motbot');
    }


    /**
     * Returns the name of the activity.
     *
     * @return string
     */
    public function get_activity_name() {
        return 'chat';
    }


    /**
     * Returns the potential level of social breadth.
     *
     * @return int
     */
    public function get_potential_level() {
        return SELF::POTENTIAL_LEVEL_4;
    }


    /**
     * feedback_viewed_events
     *
     * @return string[]
     */
    protected function view_events() {
        // We could add any forum event, but it will make feedback_post_action slower.
        return array('\mod_chat\event\course_module_viewed');
    }


    /**
     * feedback_viewed_events
     *
     * @return string[]
     */
    protected function post_events() {
        // We could add any forum event, but it will make feedback_post_action slower.
        return array('\mod_chat\event\message_sent');
    }


    protected function any_post_volley() {
        $volleylogs = array();
        // Filter out events that arent declared as post_events.
        foreach($this->useractivity as $id => $log) {
            if($log->crud == 'c' && in_array($log->eventname, $this->post_events())) {
                $volleylogs[] = $log;
            }
        }

        if(empty($volleylogs) || count($volleylogs) < 3) {
            return false;
        }

        // Count posts per discussion.
        $userid = $volleylogs[0]->userid;
        $instanceid = $volleylogs[0]->contextinstanceid;

        $count = 0;
        // Max 15 Minuten zwischen zusammengehörenden Nachrichten.
        $max_interval = 900;

        $firstlog = null;
        $lastlog = null;

        $volleys = array();

        for($i = 1; $i < count($volleylogs); $i++) {
            $log = $volleylogs[$i];
            $prev = $volleylogs[$i-1];

            if($log->timecreated - $prev->timecreated <= $max_interval) {
                if($count == 0) {
                    $firstlog = $prev;
                }
                $count++;
                $lastlog = $log;

                if($firstlog) {
                    $volleys[$firstlog->objectid] = (object)[
                        "count" => $count,
                        "start" => $firstlog->timecreated,
                        "end" => $lastlog->timecreated,
                    ];
                }
            } else {
                // Reset.
                $count = 0;
                $firstlog = null;
                $lastlog = null;
            }
        }

        if(empty($volleys)) {
            return false;
        }

        foreach($volleys as $volley) {
            if($volley->count >=2 && $this->any_messages_inbetween($instanceid, $userid, $volley->start , $volley->end, $max_interval)) {
                return true;
            }
        }

        return false;
    }


    private function any_messages_inbetween($instanceid, $userid, $starttime, $endtime, $margin) {
        global $DB;
        $sql = 'SELECT COUNT(m.id) as count
            FROM mdl_chat_messages m
            JOIN mdl_course_modules cm ON cm.instance = m.chatid
            WHERE cm.id = :instanceid
            AND m.userid != :userid
            AND m.message NOT LIKE "beep %"
            AND m.message != "exit"
            AND m.message != "enter"
            AND timestamp >= :starttime
            AND timestamp <= :endtime;';
        $count = $DB->get_record_sql($sql, array('instanceid' => $instanceid, 'userid' => $userid, 'starttime' => $starttime - $margin, 'endtime' => $endtime + $margin))->count;

        if($count && $count > 0) {
            return true;
        }
        return false;
    }

}
