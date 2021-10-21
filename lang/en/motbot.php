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

//---- Advice
$string['advice:course_completion'] = 'Advice regarding current course progress';
$string['advice:feedback'] = 'Suggestion to give feedback';
$string['advice:recent_activities'] = 'Recommendation for recently added activities';
$string['advice:recent_forum_activity'] = 'Recommendation for recent forum posts';
$string['advice:recommended_discussion'] = 'Recomemnded discussion';
$string['advice:visit_course'] = 'Advice to visit course';


//---- Bot
$string['bot'] = 'Executes scheduled interventions';

//----  Indicator
$string['indicator:socialpresenceincourseforum'] = 'Any write action in a course forum';
$string['indicator:socialpresenceincourseforum_help'] = 'TODO: Any write action in a course forum help text.';

$string['indicator:socialpresenceincoursechat'] = 'Any write action in a course chat';
$string['indicator:socialpresenceincoursechat_help'] = 'TODO: Any write action in a course chat help text.';

$string['indicator:anywriteincoursefeedbackyet'] = 'Any write action in a course feedback yet';
$string['indicator:anywriteincoursefeedbackyet_help'] = 'TODO: Any write action in a course feedback yet help text.';
//----

//----  Message
$string['messageprovider:motbot_intervention'] = 'New prediction avaialble';
$string['messageprovider:motbot_teacher_intervention'] = 'Motbot teacher intervention';


$string['message:teacher_subject'] = 'Failed intervention: {$a}';
$string['message:teacher_fullmessagehtml'] = '<p>Student {$a->fullname} might need your attention.</p><p>Previous automatic interventions by Motbot were unsuccessful.</p><p>Previous interventions:</p>{$a->interventions}';


$string['message:unhelpfulinterventions_subject'] = 'Unhelpful interventions in: {course}';
$string['message:unhelpfulinterventions_fullmessage'] = 'Hello {firstname} {lastname},
Motbot detected undesirable intervention results.
There are either an uncommon amount of negative interventions or students that received interventions found them unhelpful.
Please check motbot settings and check wether the calculated model is appropriate.
Kind regards Motbot {motbot}';
$string['message:unhelpfulinterventions_fullmessagehtml'] = '<p>Hello {firstname} {lastname},</p><p>
Motbot detected undesirable intervention results.</p><p>
There are either an uncommon amount of negative interventions or students that received interventions found them unhelpful.</p><p>
Please check motbot settings and check wether the calculated model is appropriate.</p><p>
Kind regards Motbot {motbot}</p>';
//----

//----  Module
$string['modulename'] = 'Motbot';
$string['modulenameplural'] = 'Motbot';
//----


//----  Mod Form
$string['mod_form:active'] = 'Active';
$string['mod_form:active_help'] = 'Select wether this model should analyze useractivity in this course and try to send interventions to users.';

$string['mod_form:course_dropout_header'] = 'Course Dropout Settings';
$string['mod_form:course_dropout_subject'] = 'Do you have trouble with {course_shortname}?';
$string['mod_form:course_dropout_fullmessage'] = 'Hi {firstname} {lastname},

it seems like you´re haveing difficulties keeping up. Would you like to ask a teacher for help?

{suggestions}

Kind regards, your {motbot}.';
$string['mod_form:course_dropout_fullmessagehtml'] = '<p>Hi {firstname} {lastname},</p></br><p>it seems like you´re haveing difficulties keeping up.</p><p>Would you like to ask a teacher for help?</p></br><p>Kind regards, your {motbot}.</p>';

$string['mod_form:fullmessage'] = 'Full Message';
$string['mod_form:fullmessageformat'] = 'Full message format';
$string['mod_form:fullmessageformat_help'] = 'Format of the full message';
$string['mod_form:fullmessagehtml'] = 'Full Message HTML';

$string['mod_form:intro'] = '<p>This is a motivational bot. It will analyse user activity and will intervene when it detects users that seem to have difficulties with the course content and motivation.</p>';

$string['mod_form:low_social_presence_fullmessage'] = 'Hi {firstname} {lastname},
it seems like you´re not using the forum a lot.

{suggestions}

Try it out? There might be people that have useful information to share with you.

Kind regards, your {motbot}.';
$string['mod_form:low_social_presence_fullmessagehtml'] = '<p>Hi {firstname} {lastname},</p></br><p>it seems like you´re not using the forum a lot.</p><p>Try it out? There might be people that have useful information to share with you.</p></br><p>Kind regards, your {motbot}.</p>';
$string['mod_form:low_social_presence_header'] = 'Low Social Presence Settings';
$string['mod_form:low_social_presence_subject'] = 'Detected low social activity in {course_shortname}.';

$string['mod_form:motbot_name'] = 'Name of the bot';

$string['mod_form:no_recent_accesses_fullmessage'] = 'Hi {firstname} {lastname},
it seems like you haven´t accessed the course {course_shortname} recently.

{suggestions}

We´d be happy to welcome you back!

Your {motbot}';
$string['mod_form:no_recent_accesses_fullmessagehtml'] = '<p>Hi {firstname} {lastname},</p></br><p>it seems like you haven´t accessed the course <strong>{course_shortname}</strong> recently.</p><p>We´d be happy to welcome you back!</p></br><p>Your {motbot}</p>';
$string['mod_form:no_recent_accesses_header'] = 'No Recent Accesses Settings';
$string['mod_form:no_recent_accesses_subject'] = 'We miss you, {firstname}!';

$string['mod_form:subject'] = 'Subject';

//----

$string['motbotpaused'] = 'Motbot is paused for this course.';
$string['nomotbotinstance'] = 'No Motbot activity found in course.';
$string['motbotmodelinactive'] = 'This model is deactivated or has no motbot message information associated with it in course.';

//----  General Plugin
$string['pluginadministration'] = 'Plugin Administration';
$string['pluginname'] = 'Motbot';
//----


//----  Quotes
$string['quote:0'] = 'Start where you are. Use what you have. Do what you can. - Arthur Ashe';
$string['quote:1'] = 'You will never win if you never begin. - Helen Rowland';
$string['quote:2'] = 'Good, better, best. Never let it rest. \'Til your good is better and your better is best. - St. Jerome';
$string['quote:3'] = 'It always seems impossible until it´s done. - Nelson Mandela';
$string['quote:4'] = 'With the new day comes new  strength and new thoughts. - Eleanor Roosevelt';
$string['quote:5'] = 'Step by step and the thing is done. - Charles Atlas';
//----

//----  Intervention States
$string['state:0'] = 'Scheduled';
$string['state:1'] = 'Intervened';
$string['state:2'] = 'Successful';
$string['state:3'] = 'Unsuccessful';
$string['state:4'] = 'Stored';
//----

//----  Analytic Targetstarget:
$string['target:norecentaccesses'] = 'Students who have not accessed the course recently (Motbot)';
$string['target:norecentaccesses_help'] = 'This target identifies students who have not accessed a course they are enrolled in within the set analysis interval (by default the past month).';
$string['target:norecentaccessesinfo'] = 'The following students have not accessed a course they are enrolled in within the set analysis interval (by default the past month).';
$string['target:no_recent_accesses_short'] = 'No recent accesses';
$string['target:no_recent_accesses_neutral'] = 'Attendance';

$string['target:coursedropout'] = 'Students at risk of dropping out (Motbot)';
$string['target:coursedropout_help'] = 'This target describes whether the student is considered at risk of dropping out.';
$string['target:course_dropout_short'] = 'Possible course dropout';
$string['target:course_dropout_neutral'] = 'Dropout risk';

$string['target:unhelpfulinterventions'] = 'Motbot that sends unhelpful interventions (Motbot)';
$string['target:unhelpfulinterventions_help'] = 'Motbot that sends unhelpful interventions (Motbot)';
$string['target:unhelpfulinterventionsinfo'] = 'Motbot that sends unhelpful interventions (Motbot)';
$string['target:unhelpfulinterventions_short'] = 'Unhelpful interventions';
$string['target:unhelpfulinterventions_neutral'] = 'Interventions';

$string['target:upcomingactivitiesdue'] = 'Upcoming activities due (Motbot)';
$string['target:upcomingactivitiesdue_help'] = 'This target generates reminders for upcoming activities due.';
$string['target:upcomingactivitiesdueinfo'] = 'All upcoming activities due insights are listed here. These students have received these insights directly.';
$string['target:upcoming_activities_due_short'] = 'Upcoming activities due';

$string['target:lowsocialpresence'] = 'Students with low social presence (Motbot)';
$string['target:lowsocialpresence_help'] = 'This target generates reminders for upcoming activities due.';
$string['target:lowsocialpresenceinfo'] = 'All upcoming activities due insights are listed here. These students have received these insights directly.';
$string['target:low_social_presence_short'] = 'Low social presence';
$string['target:low_social_presence_neutral'] = 'Social presence';


$string['targetlabellowsocialpresenceno'] = 'Student has low social presence.';
$string['targetlabellowsocialpresenceyes'] = 'Student has enough social presence.';
//----

$string['tomanyinstances'] = 'There should only be one Motbot activity in a course.';

//----  Course User Settings Form
$string['course_settings_form:advice_settings'] = 'Enabled advice options';
$string['course_settings_form:allow_teacher_involvement'] = 'Allow teacher involvement?';
$string['course_settings_form:allow_teacher_involvement_help'] = 'When "Yes" is checked, teachers will be informed of your situation in case other means of interventions fail.';
$string['course_settings_form:authorized'] = 'Enable Motbot';
$string['course_settings_form:authorized_help'] = 'Allow the Motbot to analyze your user activity and intervene if needed.';
$string['course_settings_form:model_settings'] = 'Enabled models';
$string['course_settings_form:only_weekdays'] = 'Only send interventions on weekdays';
$string['course_settings_form:pref_time'] = 'Prefered time of day for receiveing messages:';
$string['course_settings_form:pref_time_help'] = 'Here you can set a time of day. The MotBot will try to send you messages at this time. If you select auto, the motbot will use a time calculated by your usual active hours.';

//----
