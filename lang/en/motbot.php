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
 * Strings for activity plugin 'motbot', language 'en'
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['messageprovider:motbot_intervention'] = 'New prediction avaialble';

$string['message:no_recent_accesses_subject'] = 'We miss you!';
$string['message:no_recent_accesses_fullmessagehtml'] = 'Hi {$a}! We haven\'t seen you for a while. There are plenty of interesting topics for you to explore! See you later, your Motbot.';
$string['message:course_dropout_subject'] = 'Dropout warning!';
$string['message:course_dropout_fullmessagehtml'] = 'Hi {$a}! We haven\'t seen you for a while. There are plenty of interesting topics for you to explore! See you later, your Motbot.';

$string['modulename'] = 'Motbot';
$string['modulenameplural'] = 'Motbot';

$string['mod_form:active'] = 'active';
$string['mod_form:motbot_name'] = 'Name of the bot';
$string['mod_form:paused'] = 'paused';
$string['mod_form:usecode'] = 'State';
$string['mod_form:usecode_help'] = 'Defines wether the bot is supposed to be active or paused. When paused the bot will not analyse user activity and intervene when students are in need.';

$string['motbotpaused'] = 'Motbot is paused for this course.';
$string['nomotbotinstance'] = 'No Motbot activity found in course.';

$string['pluginadministration'] = 'Plugin Administration';
$string['pluginname'] = 'Motbot';

$string['target:norecentaccesses'] = 'Students who have not accessed the course recently (Motbot)';
$string['target:norecentaccesses_help'] = 'This target identifies students who have not accessed a course they are enrolled in within the set analysis interval (by default the past month).';
$string['target:norecentaccessesinfo'] = 'The following students have not accessed a course they are enrolled in within the set analysis interval (by default the past month).';
$string['target:coursedropout'] = 'Students at risk of dropping out (Motbot)';
$string['target:coursedropout_help'] = 'This target describes whether the student is considered at risk of dropping out.';

$string['tomanyinstances'] = 'There should only be one Motbot activity in a course.';

$string['user_settings_form:prohibit'] = 'Prohibit';
$string['user_settings_form:authorize'] = 'Authorize';
$string['user_settings_form:authorized'] = 'Enable Motbot';
$string['user_settings_form:authorized_help'] = 'Allow the Motbot to analyze your user activity and intervene if needed.';
