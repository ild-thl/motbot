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

// ---- Advice
$string['advice'] = 'Advice';
$string['advice:plural'] = 'Advice';
$string['advice:course_completion'] = 'Current course progress';
$string['advice:course_completion_help'] = 'Current course progress';
$string['advice:feedback'] = 'Feedback request';
$string['advice:feedback_help'] = 'Feedback request';
$string['advice:recent_activities'] = 'Recently added activities';
$string['advice:recent_activities_help'] = 'Recently added activities';
$string['advice:recent_forum_activity'] = 'Recent forum posts';
$string['advice:recent_forum_activity_help'] = 'Recent forum posts';
$string['advice:recommended_discussion'] = 'Recomemnded discussion';
$string['advice:recommended_discussion_help'] = 'Recomemnded discussion';
$string['advice:last_stop'] = 'Latest accessed activity';
$string['advice:last_stop_help'] = 'Latest accessed activity';
$string['advice:visit_course'] = 'Forward to course';
$string['advice:visit_course_help'] = 'Forward to course';
$string['advice:averageprogress'] = '⌀ progress';
$string['advice:yourprogress'] = 'Your progress';
$string['advice:postedby'] = 'Posted by {$a->author} on {$a->date}';
$string['advice:coursecompletion_title'] = '📈 Your recent progress in the course {$a}:';
$string['advice:completion_title'] = '📈 Your recent progress:';
$string['advice:coursecompletion_desc_bad'] = 'Your progress is only {$a}% behind average. You can easily catch up!';
$string['advice:coursecompletion_desc_worst'] = 'You ara quite a bit behind. But nothing is lost. Try catching up. Please don\'t hesitate to ask your fellow students or teachers for help!';
$string['advice:coursecompletion_desc_good'] = 'Your progress is looking fine. But there is no time to rest. A regular interaction with the course content is only recommended!';
$string['advice:coursecompletion_desc_best'] = 'You are far ahead! But don\'t rest on your laurels!';
$string['advice:feedback_title'] = '🙏 Please consider giving some feedback as well, so we can support you better!';
$string['advice:laststop_title'] = 'Continue where you last stopped:';
$string['advice:laststop_title_newchallenge'] = 'Continue with a new challenge:';
$string['advice:recentactivities_title'] = '🔥 These new or updated activities could be interesting for you:';
$string['advice:recentactivities_action'] = '{$a->activityname} added {$a->date}';
$string['advice:recentforumactivity_title'] = '🔥 These new forum discussions could be interesting for you:';
$string['advice:recentforumactivity_action'] = 'The discussion {$a->subject} was posted on the {$a->date}';
$string['advice:recommendeddiscussion_title'] = '🚑 Nobody replied to this students post yet. Maybe you could try to add something to the discussion? &#128657;';
$string['advice:recommendeddiscussion_action'] = 'Reply now';
$string['advice:visitcourse_title'] = 'Visit the course!';
$string['advice:update'] = 'Update advice';
$string['advice:load_new'] = 'Load new advice';
$string['advice:restore_default'] = 'Restore default';
$string['advice:edit'] = 'Edit "{$a}" advice';
$string['advice:name'] = 'Advice name ';
$string['advice:enabled'] = 'Enabled';
$string['advice:targets'] = 'Analytics Targets';
$string['advice:targets_help'] = 'Analytics Targets';
$string['advice:targetsnum'] = 'Amount of targets: {$a}';
$string['advice:created'] = '{$a->count} new advice defintion(s) loaded for component \'{$a->component}\'';
$string['advice:updated'] = '{$a->count} advice defintion(s) updated for component \'{$a->component}\'';
$string['advice:noneavailable'] = 'No advice available.';
// ----

// ---- Chatbot
$string['chatbot'] = 'Chatbot';
$string['chatbot:default'] = 'Hi {$a},
how can I help you today?';
// ----

// ----  Course User Settings Form
$string['course_settings_form:advice_settings'] = 'Enabled advice options';
$string['course_settings_form:allow_teacher_involvement'] = 'Allow teacher involvement?';
$string['course_settings_form:allow_teacher_involvement_help'] = 'When "Yes" is checked, teachers will be informed of your situation in case other means of interventions fail.';
$string['course_settings_form:authorized'] = 'Enable MotBot';
$string['course_settings_form:authorized_help'] = 'Allow the MotBot to analyze your user activity and intervene if needed.';
$string['course_settings_form:model_settings'] = 'Enabled models';
$string['course_settings_form:only_weekdays'] = 'Only send interventions on weekdays';
$string['course_settings_form:pref_time'] = 'Prefered time of day for receiveing messages:';
$string['course_settings_form:pref_time_help'] = 'Here you can set a time of day. The MotBot will try to send you messages at this time. If you select auto, the motbot will use a time calculated by your usual active hours.';
// ----

// ----  Indicator
$string['indicator:anyaccess'] = 'Any recent access';
$string['indicator:anyaccess_help'] = 'TODO: Any recent access help text.';

$string['indicator:anycompletions'] = 'Recent Completions';
$string['indicator:anycompletions_help'] = 'TODO: Any recent access help text.';

$string['indicator:anywriteaction'] = 'Any recent write action';
$string['indicator:anywriteaction_help'] = 'TODO: Any recent write action help text.';

$string['indicator:anywriteincoursefeedbackyet'] = 'Any write action in a course feedback yet';
$string['indicator:anywriteincoursefeedbackyet_help'] = 'TODO: Any write action in a course feedback yet help text.';

$string['indicator:socialpresenceincoursechat'] = 'Any write action in a course chat';
$string['indicator:socialpresenceincoursechat_help'] = 'TODO: Any write action in a course chat help text.';

$string['indicator:socialpresenceincourseforum'] = 'Any write action in a course forum';
$string['indicator:socialpresenceincourseforum_help'] = 'TODO: Any write action in a course forum help text.';
// ----

// ----  Message
$string['messageprovider:motbot_intervention'] = 'New prediction avaialble';
$string['messageprovider:motbot_teacher_intervention'] = 'MotBot teacher intervention';


$string['message:teacher_subject'] = 'Failed intervention: {$a}';
$string['message:teacher_fullmessagehtml'] = '<p>Student {$a->fullname} might need your attention.</p><p>Previous automatic interventions by MotBot were unsuccessful.</p><p>Previous interventions:</p>{$a->interventions}';


$string['message:unhelpfulinterventions_subject'] = 'Unhelpful interventions in: {course}';
$string['message:unhelpfulinterventions_fullmessage'] = 'Hello {firstname} {lastname},
MotBot detected undesirable intervention results.
There are either an uncommon amount of negative interventions or students that received interventions found them unhelpful.
Please check motbot settings and check wether the calculated model is appropriate.
Kind regards MotBot {motbot}';
$string['message:unhelpfulinterventions_fullmessagehtml'] = '<p>Hello {firstname} {lastname},</p><p>
MotBot detected undesirable intervention results.</p><p>
There are either an uncommon amount of negative interventions or students that received interventions found them unhelpful.</p><p>
Please check motbot settings and check wether the calculated model is appropriate.</p><p>
Kind regards MotBot {motbot}</p>';
// ----

// ----  Module
$string['modulename'] = 'MotBot';
$string['modulenameplural'] = 'MotBot';
// ----


// ----  Mod Form
$string['mod_form:active'] = 'Active';
$string['mod_form:active_help'] = 'Select wether this model should analyze useractivity in this course and try to send interventions to users.';

$string['mod_form:custom'] = 'Custom Message';
$string['mod_form:custom_help'] = 'When this option is enabled a custom message will be sent to students identified by this model. You can edit the custom message using the following input fields.';

$string['mod_form:course_dropout_header'] = 'Course Dropout Settings';
$string['mod_form:course_dropout_subject'] = 'Do you have trouble with {course_shortname}?';
$string['mod_form:course_dropout_fullmessage'] = 'Hi {firstname} {lastname},

it seems like you´re haveing difficulties keeping up. Would you like to ask a teacher for help?

{suggestions}

Kind regards, your {motbot}.';
$string['mod_form:course_dropout_fullmessagehtml'] = '<p>Hi {firstname} {lastname},</p></br><p>it seems like you´re haveing difficulties keeping up.</p><p>Would you like to ask a teacher for help?</p></br><p>Kind regards, your {motbot}.</p>';

$string['mod_form:fullmessage'] = 'Full Message';
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

$string['mod_form:recent_cognitive_presence_header'] = 'Recent cognitive presence Settings';
$string['mod_form:recent_cognitive_presence_subject'] = 'You hav\'nt been around lately!';
$string['mod_form:recent_cognitive_presence_subject_0'] = 'You hav\'nt been around lately!';
$string['mod_form:recent_cognitive_presence_fullmessage_0'] = 'Hi {firstname} {lastname},
it seems like you haven\'t been around lately.

{suggestions}

Enjoy learning!

Kind regards, your {motbot}.';
$string['mod_form:recent_cognitive_presence_fullmessage'] = 'Hi {firstname} {lastname},
it seems like you haven\'t been around lately.

{suggestions}

Enjoy learning!

Kind regards, your {motbot}.';

$string['mod_form:recent_cognitive_presence_fullmessagehtml'] = '<p>Hi {firstname} {lastname},</p></br><p>it seems like you haven´t been around lately.</p><p>We´d be happy to welcome you back!</p></br><p>Your {motbot}</p>';
$string['mod_form:recent_cognitive_presence_fullmessagehtml_0'] = '<p>Hi {firstname} {lastname},</p></br><p>it seems like you haven´t been around lately.</p><p>We´d be happy to welcome you back!</p></br><p>Your {motbot}</p>';

$string['mod_form:recent_cognitive_presence_subject_1'] = 'You hav\'nt been very active lately!';
$string['mod_form:recent_cognitive_presence_fullmessage_1'] = 'Hi {firstname} {lastname},
it seems like you haven\'t been very active lately.
Please let us know if you have any difficulties or problems with the learning content.

{suggestions}

Kind regards, your {motbot}.';
$string['mod_form:recent_cognitive_presence_fullmessagehtml_1'] = '<p>Hi {firstname} {lastname},</p></br><p>it seems like you haven´t been very active lately.</p><p>Please let us know if you have any difficulties or pronlems with the learning content.</p></br><p>Your {motbot}</p>';

$string['mod_form:recent_cognitive_presence_subject_2'] = 'You have been active lately!';
$string['mod_form:recent_cognitive_presence_fullmessage_2'] = 'Hi {firstname} {lastname},
it\'s good to have you. Thank you for participating regularly in the learning activities!

Kind regards, your {motbot}.';
$string['mod_form:recent_cognitive_presence_fullmessagehtml_2'] = '<p>Hi {firstname} {lastname},</p></br><p>it\'s good to have you. Thank you for participating regularly in the learning activities!</p></br><p>Your {motbot}</p>';

$string['mod_form:recent_cognitive_presence_subject_3'] = 'Congrats on you latest achievements!';
$string['mod_form:recent_cognitive_presence_fullmessage_3'] = 'Hi {firstname} {lastname},
I am very proud on your latest acomplishements. Keep on doing great work!

Kind regards, your {motbot}.';
$string['mod_form:recent_cognitive_presence_fullmessagehtml_3'] = '<p>Hi {firstname} {lastname},</p></br><p>I am very proud on your latest acomplishements. Keep on doing great work!</p></br><p>Your {motbot}</p>';


$string['mod_form:too_long'] = 'This form field can only hold {$a} characters. Please check your input.';
$string['mod_form:subject'] = 'Subject';
// ----

// ---- MotBot General
$string['motbot:modelinactive'] = 'This model is deactivated or has no motbot message information associated with it in course.';
$string['motbot:noinstance'] = 'No MotBot activity found in course.';
$string['motbot:paused'] = 'MotBot is paused for this course.';
$string['motbot:pleaseactivate'] = 'Please activate the MotBot first.';
$string['motbot:thanksforfeedback'] = '<div class="row"><img src="http://localhost/theme/image.php/boost/motbot/1639056026/icon" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true">&nbsp;&nbsp;<p>Thank you for your feedback!</p></div>';
$string['motbot:overview_header'] = 'MotBot overview';
$string['motbot:helpful'] = 'Helpful';
$string['motbot:unhelpful'] = 'Unhelpful';
$string['motbot:total'] = 'Total';
$string['motbot:lastinterventionon'] = 'Last intervention on';
$string['motbot:lastupdateon'] = 'Last update on';
$string['motbot:nointerventionyet'] = 'No intervention yet';
$string['motbot:viewintervention'] = 'View intervention';
$string['motbot:allgood'] = 'All good';
$string['motbot:interventions'] = 'Interventions';
$string['motbot:notification'] = 'Notification';
$string['motbot:gotocourse'] = 'To course';
$string['motbot:goto'] = 'Go to {$a}';
$string['motbot:reason'] = 'Reason for intervention';
$string['motbot:date'] = 'Date';
$string['motbot:state'] = 'State';
$string['motbot:wereteachersinformed'] = 'Were teachers informed';
$string['motbot:message'] = 'Message';
$string['motbot:enablingmotbot'] = 'Enabling Motbot';
$string['motbot:disabled'] = 'The MotBot is currently deactivated.';
$string['motbot:ishappy'] = 'The MotBot is happy.';
$string['motbot:isunhappy'] = 'The MotBot could be happier.';
$string['motbot:moreinfo'] = 'More info';
$string['motbot:activate'] = 'Activate MotBot';
$string['motbot:updated'] = 'Updated';
// ----

// ----  Plugin General
$string['pluginadministration'] = 'Plugin Administration';
$string['pluginname'] = 'MotBot';
// ----


// ----  Quotes
$string['quote:0'] = 'Start where you are. Use what you have. Do what you can. - Arthur Ashe';
$string['quote:1'] = 'You will never win if you never begin. - Helen Rowland';
$string['quote:2'] = 'Good, better, best. Never let it rest. \'Til your good is better and your better is best. - St. Jerome';
$string['quote:3'] = 'It always seems impossible until it´s done. - Nelson Mandela';
$string['quote:4'] = 'With the new day comes new  strength and new thoughts. - Eleanor Roosevelt';
$string['quote:5'] = 'Step by step and the thing is done. - Charles Atlas';
// ----

// ---- Event reactions
$string['reaction:coreeventcourse_viewed'] = '<div class="row"><img src="http://localhost/theme/image.php/boost/motbot/1639056026/icon" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true">&nbsp;&nbsp;<p>It\'s good to have you back!</p>&nbsp;{$a}</div>';
$string['reaction:coreeventuser_loggedin'] = '<div class="row"><img src="http://localhost/theme/image.php/boost/motbot/1639056026/icon" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true">&nbsp;&nbsp;<p>It\'s good to have you back!</p>&nbsp;{$a}</div>';
$string['reaction:mod_forumeventdiscussion_created'] = '<div class="row"><img src="http://localhost/theme/image.php/boost/motbot/1639056026/icon" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true">&nbsp;&nbsp;<p>Thank you for participating in the forums!</p></div>';
$string['reaction:mod_forumeventpost_created'] = '<div class="row"><img src="http://localhost/theme/image.php/boost/motbot/1639056026/icon" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true">&nbsp;&nbsp;<p>Thank you for participating in the forums!</p></div>';
$string['reaction:mod_forumeventassessable_uploaded'] = '<div class="row"><img src="http://localhost/theme/image.php/boost/motbot/1639056026/icon" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true">&nbsp;&nbsp;<p>Thank you for participating in the forums!</p></div>';
$string['reaction:mod_chateventmessage_sent'] = '<div class="row"><img src="http://localhost/theme/image.php/boost/motbot/1639056026/icon" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true">&nbsp;&nbsp;<p>Thank you for participating in the chat!</p></div>';
// ----

// ----  State of Intervention
$string['state:0'] = 'Scheduled';
$string['state:1'] = 'Intervened';
$string['state:2'] = 'Successful';
$string['state:3'] = 'Unsuccessful';
$string['state:4'] = 'Stored';
// ----

// ----  Settings
$string['settings:advanced_options'] = 'Advanced options';
$string['settings:course_settings_header'] = '{$a->pluginname} settings for {$a->coursename}';
$string['settings:deleteinterventiondata'] = 'Delete intervention data';
$string['settings:edit_motbot'] = 'MotBot Settings';
$string['settings:edit_models'] = 'MotBot Analytics Model Settings';
$string['settings:edit_advice'] = 'MotBot Advice Settings';
// ----

// ----  Analytic Targets
$string['target:recentcognitivepresence'] = 'Students recent cognitive presence (MotBot)';
$string['target:recentcognitivepresence_help'] = 'This target describes how much the student has interacted with moodle content recently.';
$string['target:recent_cognitive_presence_short'] = 'Recent cognitive presence';
$string['target:recent_cognitive_presence_neutral'] = 'Recent cognitive presence';

$string['target:coursedropout'] = 'Students at risk of dropping out (MotBot)';
$string['target:coursedropout_help'] = 'This target describes wether the student is considered at risk of dropping out.';
$string['target:course_dropout_short'] = 'Possible course dropout';
$string['target:course_dropout_neutral'] = 'Dropout risk';

$string['target:lowsocialpresence'] = 'Students with low social presence (MotBot)';
$string['target:lowsocialpresence_help'] = 'This target generates reminders for upcoming activities due.';
$string['target:low_social_presence_short'] = 'Low social presence';
$string['target:low_social_presence_neutral'] = 'Social presence';

$string['target:norecentaccesses'] = 'Students who have not accessed the course recently (MotBot)';
$string['target:norecentaccesses_help'] = 'This target identifies students who have not accessed a course they are enrolled in within the set analysis interval (by default the past month).';
$string['target:no_recent_accesses_short'] = 'No recent accesses';
$string['target:no_recent_accesses_neutral'] = 'Attendance';

$string['target:unhelpfulinterventions'] = 'MotBot that sends unhelpful interventions (MotBot)';
$string['target:unhelpfulinterventions_help'] = 'MotBot that sends unhelpful interventions (MotBot)';
$string['target:unhelpfulinterventions_short'] = 'Unhelpful interventions';
$string['target:unhelpfulinterventions_neutral'] = 'Interventions';

$string['target:upcomingactivitiesdue'] = 'Upcoming activities due (MotBot)';
$string['target:upcomingactivitiesdue_help'] = 'This target generates reminders for upcoming activities due.';
$string['target:upcomingactivitiesdueinfo'] = 'All upcoming activities due insights are listed here. These students have received these insights directly.';
$string['target:upcoming_activities_due_short'] = 'Upcoming activities due';


$string['targetlabellowsocialpresenceno'] = 'Student has low social presence.';
$string['targetlabellowsocialpresenceyes'] = 'Student has enough social presence.';
// ----

// ---- Taskbot
$string['taskbot'] = 'Executes scheduled interventions';
// ----

$string['tomanyinstances'] = 'There should only be one MotBot activity in a course.';

$string['userdisabledmotbot'] = 'User disabled motbot activity.';
