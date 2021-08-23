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
        return SELF::POTENTIAL_LEVEL_3;
    }


    /**
     * feedback_viewed_events
     *
     * @return string[]
     */
    public static function view_events() {
        // We could add any forum event, but it will make feedback_post_action slower.
        return array('\mod_chat\event\course_module_viewed');
    }


    /**
     * feedback_viewed_events
     *
     * @return string[]
     */
    public static function post_events() {
        // We could add any forum event, but it will make feedback_post_action slower.
        return array('\mod_chat\event\message_sent');
    }


    protected function any_post_volley() {
        $volleylogs = array();
        // Filter out events that arent declared as post_events.
        foreach($this->useractivity as $id => $log) {
            if($log->crud == 'c' && in_array($log->eventname, self::post_events())) {
                $volleylogs[] = $log;
            }
        }

        if(empty($volleylogs)) {
            return false;
        }

        // Count posts per discussion.
        $userid = $volleylogs[0]->userid;

        $object_count = array();
        $known_discussionids = array();
        $max_count = 0;
        $max_discussion = 0;
        foreach($volleylogs as $log) {
            // Get the id of the discussion this log belongs to.
            if(array_key_exists('discussionid', $log->other)) {
                // Is a reply to a discussion post.
                $discussionid = $log->other['discussionid'];
            } else {
                // Is the dicussion post itself
                $discussionid = $log->objectid;
            }

            // Go to next log, if this log belogs to a post we already counted.
            if(in_array($discussionid, $known_discussionids)) {
                continue;
            }
            $known_discussionids[] = $discussionid;

            // Count posts per discussion.
            if(array_key_exists($log->contextid, $object_count)) {
                $object_count[$log->contextid] = $object_count[$log->contextid] + 1;
            } else {
                $object_count[$log->contextid] = 1;
            }

            // Get current max count.
            if($object_count[$log->contextid] > $max_count) {
                $max_count = $object_count[$log->contextid];
                $max_discussion = $discussionid;
                if($max_count >= 2) {
                    // If the user has contributed more than one post to a single discussion we skip to the next step.
                    break;
                }
            }
        }

        // If there are more than 2 posts from this user in a single discussion,
        //  check if there are more posts from other users.
        if($max_count >= 2 && $this->has_discussion_posts_from_others($max_discussion, $userid)) {
            return true;
        }
        return false;
    }


    private function has_discussion_posts_from_others($discussionid, $userid) {
        global $DB;
        $sql = 'SELECT COUNT(id) as postcount
            FROM mdl_forum_posts
            WHERE discussion = :discussionid
            AND userid != :userid';
        $count = $DB->get_record_sql($sql, array('discussionid' => $discussionid, 'userid' => $userid))->postcount;

        if($count && $count > 0) {
            return true;
        }
        return false;
    }

}
