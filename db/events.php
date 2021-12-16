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
 * Definitions of events that should be observed
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$observers = array(

    array(
        'eventname'   => '\core\event\course_viewed',
        'callback'    => '\mod_motbot\observer::course_viewed',
    ),

    array(
        'eventname'   => '\mod_chat\event\message_sent',
        'callback'    => '\mod_motbot\observer::check_intervention_success',
    ),

    array(
        'eventname'   => '\mod_forum\event\assessable_uploaded',
        'callback'    => '\mod_motbot\observer::discussion_or_post_created',
    ),

    array(
        'eventname'   => '\mod_forum\event\discussion_created',
        'callback'    => '\mod_motbot\observer::discussion_or_post_created',
    ),

    array(
        'eventname'   => '\mod_forum\event\post_created',
        'callback'    => '\mod_motbot\observer::discussion_or_post_created',
    ),

    array(
        'eventname'   => '\core\event\user_loggedin',
        'callback'    => '\mod_motbot\observer::user_loggedin',
    ),

);