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
 * Strings for activity plugin 'motbot', language 'de'
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// ---- Advice
$string['advice'] = 'Empfehlung';
$string['advice:plural'] = 'Empfehlungen';
$string['advice:course_completion'] = 'Aktueller Kursfortschritt';
$string['advice:course_completion_help'] = 'Aktueller Kursfortschritt';
$string['advice:feedback'] = 'Aufforderung Feedback zu geben';
$string['advice:feedback_help'] = 'Aufforderung Feedback zu geben';
$string['advice:recent_activities'] = 'Kürzlich hinzugefügte Akrivitäten';
$string['advice:recent_activities_help'] = 'Kürzlich hinzugefügte Akrivitäten';
$string['advice:recent_forum_activity'] = 'Kürzlich hinzugefügte Forum Posts.';
$string['advice:recent_forum_activity_help'] = 'Kürzlich hinzugefügte Forum Posts.';
$string['advice:recommended_discussion'] = 'Empfohlene Diskussion';
$string['advice:recommended_discussion_help'] = 'Empfohlene Diskussion';
$string['advice:visit_course'] = 'Link zum Kurs';
$string['advice:visit_course_help'] = 'Link zum Kurs';
$string['advice:last_stop'] = 'Zuletzt bearbeitete Aktivität';
$string['advice:last_stop_help'] = 'Zuletzt bearbeitete Aktivität';
$string['advice:averageprogress'] = '⌀ Fortschritt';
$string['advice:yourprogress'] = 'Dein Fortschritt';
$string['advice:postedby'] = 'Veröffentlicht von {$a->author} am {$a->date}';
$string['advice:coursecompletion_title'] = '📈 Dein aktueller Fortschritt im Kurs {$a}:';
$string['advice:completion_title'] = '📈 Dein aktueller Fortschritt:';
$string['advice:coursecompletion_desc_bad'] = 'Dein Fortschritt ist nur {$a}% hinter dem Durchschnitt. Du solltest keine Probleme haben aufzuholen.';
$string['advice:coursecompletion_desc_worst'] = 'Du bist ganz schön hinterher, aber du hast noch Zeit aufzuholen. Bitte zögere nicht deine Mitstudierenden oder das Lehrpersonal um Hilfe zu fragen!';
$string['advice:coursecompletion_desc_good'] = 'Dein Forstschritt ist gut. Dennoch empfehlen wir, sich regelmäßig mit den Kursinhalten zu beschäftigen.';
$string['advice:coursecompletion_desc_best'] = 'Du bist weit voraus! Weiter so!';
$string['advice:feedback_title'] = '🙏 Bitte gib uns feedback, sodass wir dich noch besser unterstützen können!';
$string['advice:laststop_title'] = 'Mach weiter, wo du zuletzt aufgehört hast:';
$string['advice:laststop_title_newchallenge'] = 'Beginne eine neue Herausforderung:';
$string['advice:recentactivities_title'] = '🔥 Diese neu hinzugefügten oder aktualisierten Aktivitäten könnten dich interessieren:';
$string['advice:recentactivities_action'] = '{$a->activityname} hinzugefügt am {$a->date}';
$string['advice:recentforumactivity_title'] = '🔥 Diese neuen Forendiskussionen könnten dich interessieren:';
$string['advice:recentforumactivity_action'] = 'Die Diskussion {$a->subject} wurde am {$a->date} veröffentlicht';
$string['advice:recommendeddiscussion_title'] = '🚑 Es hat bisher noch niemand auf diesen Post geantwortet. Vielleicht kannst du etwas zu dieser Diskussion beitragen?';
$string['advice:recommendeddiscussion_action'] = 'Antworte jetzt';
$string['advice:visitcourse_title'] = 'Besuch den Kurs!';
$string['advice:update'] = 'Empfehlungen aktualisieren';
$string['advice:load_new'] = 'Neue Empfehlungen laden';
$string['advice:restore_default'] = 'Standard wiederherstellen';
$string['advice:edit'] = 'Empfehlung "{$a}" bearbeiten';
$string['advice:name'] = 'Name der Empfehlung';
$string['advice:enabled'] = 'Aktiviert';
$string['advice:targets'] = 'Analytik-Ziele';
$string['advice:targets_help'] = 'Analytik-Ziele';
$string['advice:targetsnum'] = 'Anzahl der Ziele: {$a}';
$string['advice:created'] = '{$a->count} neue Empfehlungsdefinition(en) wurde(n) für Komponente \'{$a->component}\' geladen';
$string['advice:updated'] = '{$a->count} Empfehlungsdefinition(en) wurde(n) für Komponente \'{$a->component}\' aktualisiert';
$string['advice:noneavailable'] = 'Keine Empfehlungen verfügbar.';
// ----

// ---- Chatbot
$string['chatbot'] = 'Chatbot';
$string['chatbot:default'] = 'Hi {$a},
wie kann ich dir heute helfen?';
// ----

// ----  Course User Settings Form
$string['course_settings_form:advice_settings'] = 'Aktivierte Empfehlungen';
$string['course_settings_form:allow_teacher_involvement'] = 'Erlaube die Involvierung von Lehrenden?';
$string['course_settings_form:allow_teacher_involvement_help'] = 'Wenn sie die Erlaubnis geben, werden Lehrende über ihre Situation informiert, sollten andere Wege der Intervention durch den Bot scheitern.';
$string['course_settings_form:authorized'] = 'Erlaube MotBot';
$string['course_settings_form:authorized_help'] = 'Erlaube dem MotBot Nutzeraktivität zu analysieren und gegebenenfalls zu intervenieren.';
$string['course_settings_form:model_settings'] = 'Erlaubte Modelle';
$string['course_settings_form:only_weekdays'] = 'Sende Interventionen nur an Wochentagen';
$string['course_settings_form:pref_time'] = 'Präferierte Tageszeit, um Interventionen zu empfangen:';
$string['course_settings_form:pref_time_help'] = 'Wähle eine Tageszeit. Der MotBot wird versuchen dir Interventionen zu dieser Zeit zuzustellen. Wenn Sie "auto" wählen, wird der MotBot anhand ihrer vergangenen Aktivitäten eine Tageszeit berechnen, zu der Sie üblicherweise auf Moodle aktiv sind.';
// ----

// ----  Indicator
$string['indicator:anyaccess'] = 'Kürzliche Zugriffe';
$string['indicator:anyaccess_help'] = 'TODO: Any recent access help text.';

$string['indicator:anycompletions'] = 'Kürzliche Abschlüsse';
$string['indicator:anycompletions_help'] = 'TODO: Any recent access help text.';

$string['indicator:anywriteaction'] = 'Kürzliche Schreib-Aktionen';
$string['indicator:anywriteaction_help'] = 'TODO: Any recent write action help text.';

$string['indicator:anywriteincoursefeedbackyet'] = 'Schreib-Aktionen in einem Feedback-Modul';
$string['indicator:anywriteincoursefeedbackyet_help'] = 'TODO: Any write action in a course feedback yet help text.';

$string['indicator:socialpresenceincoursechat'] = 'Schreib-Aktionen in einer Chat-Aktivität';
$string['indicator:socialpresenceincoursechat_help'] = 'TODO: Any write action in a course chat help text.';

$string['indicator:socialpresenceincourseforum'] = 'Schreib-Aktionen in einer Forum-Aktivität';
$string['indicator:socialpresenceincourseforum_help'] = 'TODO: Any write action in a course forum help text.';
// ----


// ----  Message
$string['messageprovider:motbot_intervention'] = 'Neue Vorhersagen verfügbar';
$string['messageprovider:motbot_teacher_intervention'] = 'MotBot Lehrpersonal-Intervention';


$string['message:teacher_subject'] = 'Fehlgeschlagene Intervention: {$a}';
$string['message:teacher_fullmessagehtml'] = '<p>Der oder die Student:in {$a->fullname} könnte ihre Aufmerksamkeit benötigen.</p><p>Vorherige Interventionen blieben unerfolgreich.</p><p>Vorherige Interventionen:</p>{$a->interventions}';


$string['message:unhelpfulinterventions_subject'] = 'Nicht hilfreiche Interventionen in: {course}';
$string['message:unhelpfulinterventions_fullmessage'] = 'Hallo {firstname} {lastname},
der MotBot hat unerwünschte Interventionsergebnisse entdeckt.
Es gibt entweder eine ungewöhnlich hohe Anzahl an negativen Interventionen oder Studierende haben die erhaltenen Interventionen für nicht hilfreich empfunden.
Bitte prüfen Sie die aktuellen Eintellungen und ob die aktiviereten analytischen Modelle angemessen sind.
Ihr {motbot}';
$string['message:unhelpfulinterventions_fullmessagehtml'] = '<p>Hallo {firstname} {lastname},</p><p>
der MotBot hat unerwünschte Interventionsergebnisse entdeckt.</p><p>
Es gibt entweder eine ungewöhnlich hohe Anzahl an negativen Interventionen oder Studierende haben die erhaltenen Interventionen für nicht hilfreich empfunden.</p><p>
Bitte prüfen Sie die aktuellen Eintellungen und ob die aktiviereten analytischen Modelle angemessen sind.</p><p>
Ihr {motbot}</p>';
// ----

// ----  Module
$string['modulename'] = 'MotBot';
$string['modulenameplural'] = 'MotBot';
// ----


// ----  Mod Form
$string['mod_form:active'] = 'Aktiv';
$string['mod_form:active_help'] = 'Wählen Sie, ob der MotBot in diesem Kurs Nutzeraktivitäten analysieren und wenn nötig Interventionen an Lehrende verschicken soll.';

$string['mod_form:custom'] = 'Benutzerdefineirte Nachricht';
$string['mod_form:custom_help'] = 'Wenn diese Option aktiviert ist, wird den Studierenden eine benutzerdefinierte Nachricht gesendet. Sie können diese Nachricht in den folgenden Feldern bearbeiten.';

$string['mod_form:course_dropout_header'] = 'Kurs Abbruch Einstellungen';
$string['mod_form:course_dropout_subject'] = 'Hast du Schwierigkeiten mit {course_shortname}?';
$string['mod_form:course_dropout_fullmessage'] = 'Hallo {firstname} {lastname},

es scheint als hättest du Schwierigkeiten mit den Kursinhalten. Würdest du gerne Kontakt zu einem Lehrenden aufnehmen?

{suggestions}

Freundliche Grüße, dein {motbot}.';
$string['mod_form:course_dropout_fullmessagehtml'] = '<p>Hallo {firstname} {lastname},</p></br><p>es scheint als hättest du Schwierigkeiten mit den Kursinhalten.</p>
<p> Würdest du gerne Kontakt zu einem Lehrenden aufnehmen?</p></br>
<p>Freundliche Grüße, dein {motbot}.</p>';

$string['mod_form:fullmessage'] = 'Nachricht';
$string['mod_form:fullmessagehtml'] = 'Nachricht im HTML Format';

$string['mod_form:intro'] = '<p>Dies ist ein Motivations-Bot. Der Bot analysiert Nutzeraktivitäten und interveniert Lehrende die möglicherweise Probleme mit den Kursinhalten oder Motivation haben.</p>';

$string['mod_form:low_social_presence_fullmessage'] = 'Hi {firstname} {lastname},
es scheint als würdest du dich im Kurs nicht viel sozial betätigen.

{suggestions}

Ich hoffe, dass diese Empfehlungen ermutigen konnten, dich mit deinen Kursmitgliedern zu vernetzen.

Liebe Grüße, dein {motbot}.';
$string['mod_form:low_social_presence_fullmessagehtml'] = '<p>Hi {firstname} {lastname},</p></br><p>es scheint als würdest du dich im Kurs nicht viel sozial betätigen.</p>
<p>Liebe Grüße, dein {motbot}.</p>';
$string['mod_form:low_social_presence_header'] = 'Geringe soziale Präsenz Einstellungen';
$string['mod_form:low_social_presence_subject'] = 'Geringe soziale Aktivitäte in {course_shortname} entdeckt.';

$string['mod_form:motbot_name'] = 'Name des MotBots';

$string['mod_form:no_recent_accesses_fullmessage'] = 'Hi {firstname} {lastname},
es scheint als hättest du den Kurs {course_shortname} kürzlich nicht besucht.

{suggestions}

Bis bald, dein {motbot}';
$string['mod_form:no_recent_accesses_fullmessagehtml'] = '<p>Hi {firstname} {lastname},</p></br><p>es scheint als hättest du den Kurs <strong>{course_shortname}</strong> kürzlich nicht besucht.</p>
</br><p>Bis bald, dein {motbot}</p>';
$string['mod_form:no_recent_accesses_header'] = 'Keine kürzlichen Zugriffe Einstellungen';
$string['mod_form:no_recent_accesses_subject'] = 'Besuche den Kurs {course_shortname}!';


$string['mod_form:recent_cognitive_presence_header'] = 'Recent cognitive presence Settings';

$string['mod_form:recent_cognitive_presence_subject_0'] = 'Wo warst du solang?';
$string['mod_form:recent_cognitive_presence_subject'] = 'Wo warst du solang?';
$string['mod_form:recent_cognitive_presence_fullmessage_0'] = 'Hi {firstname} {lastname},
es scheint als hättest du dich schon lange nicht mehr eingeloggt.

{suggestions}

Viele Grüße, dein {motbot}.';

$string['mod_form:recent_cognitive_presence_fullmessage'] = 'Hi {firstname} {lastname},
es scheint als hättest du dich schon lange nicht mehr eingeloggt.

{suggestions}

Viele Grüße, dein {motbot}.';
$string['mod_form:recent_cognitive_presence_fullmessagehtml'] = '<p>Hi {firstname} {lastname},</p></br><p>es scheint als hättest du dich schon lange nicht mehr eingeloggt.</p></br><p>Viele Grüße, dein {motbot}</p>';
$string['mod_form:recent_cognitive_presence_fullmessagehtml_0'] = '<p>Hi {firstname} {lastname},</p></br><p>es scheint als hättest du dich schon lange nicht mehr eingeloggt.</p></br><p>Viele Grüße, dein {motbot}</p>';


$string['mod_form:recent_cognitive_presence_subject_1'] = 'Du warst in letzter Zeit nicht sehr aktiv.';
$string['mod_form:recent_cognitive_presence_fullmessage_1'] = 'Hi {firstname} {lastname},
es scheint als wärst du in letzter Zeit nicht sehr aktiv gewesen.
Bitte lass uns wissen solltest du Probleme oder Schwierigkeiten mit den Lerninhalten haben.

{suggestions}

Viele Grüße, dein {motbot}.';
$string['mod_form:recent_cognitive_presence_fullmessagehtml_1'] = '<p>Hi {firstname} {lastname},</p></br><p>es scheint als wärst du in letzter Zeit nicht sehr aktiv gewesen.</p><p>Bitte lass uns wissen solltest du Probleme oder Schwierigkeiten mit den Lerninhalten haben.</p></br><p>Your {motbot}</p>';

$string['mod_form:recent_cognitive_presence_subject_2'] = 'Du warst zuletzt aktiv!';
$string['mod_form:recent_cognitive_presence_fullmessage_2'] = 'Hi {firstname} {lastname},
wir freuen uns, dass du die Plattform nutzt. Danke für deine regelmäßige Teilnahme!

Viele Grüße, dein {motbot}.';
$string['mod_form:recent_cognitive_presence_fullmessagehtml_2'] = '<p>Hi {firstname} {lastname},</p></br><p>it\'s good to have you. Thank you for participating regularly in the learning activities!</p></br><p>Your {motbot}</p>';

$string['mod_form:recent_cognitive_presence_subject_3'] = 'Glückwunsch zu deinen kürzlichen Erfolgen!';
$string['mod_form:recent_cognitive_presence_fullmessage_3'] = 'Hi {firstname} {lastname},
Ich bin sehr stolz auf deine vor kurzem erreichenten Erungenschaften. Mach weiter so!

Viele Grüße, dein {motbot}.';
$string['mod_form:recent_cognitive_presence_fullmessagehtml_3'] = '<p>Hi {firstname} {lastname},</p></br><p>Ich bin sehr stolz auf deine vor kurzem erreichenten Erungenschaften.</p><p>Mach weiter so!</p></br><p>Your {motbot}</p>';


$string['mod_form:too_long'] = 'Dieses Formularfeld kann nur {$a} Zeichen enthalten. Bitte überprüfen Sie Ihre Eingabe.';
$string['mod_form:subject'] = 'Betreff';
// ----

// ---- MotBot General
$string['motbot:paused'] = 'Der MotBot ist für disen Kurs pausiert.';
$string['motbot:modelinactive'] = 'Dieses Model ist in den MotBot-Einstellungen des Kurses deaktiviert oder falsch konfiguriert.';
$string['motbot:noinstance'] = 'Keine MotBot Aktivität in diesem Kurs.';
$string['motbot:pleaseactivate'] = 'Bitte aktivieren Sie den MotBot zuerst.';
$string['motbot:thanksforfeedback'] = '<div class="row"><img src="http://localhost/theme/image.php/boost/motbot/1639056026/icon" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true">&nbsp;&nbsp;<p>Danke für dein Feedback!</p></div>';
$string['motbot:overview_header'] = 'MotBot Übersicht';
$string['motbot:helpful'] = 'Hilfreich';
$string['motbot:unhelpful'] = 'Nicht hilfreich';
$string['motbot:total'] = 'Gesamt';
$string['motbot:lastinterventionon'] = 'Letzte Intervention am';
$string['motbot:lastupdateon'] = 'Letzte Aktualisierung am';
$string['motbot:nointerventionyet'] = 'Noch keine Intervention';
$string['motbot:viewintervention'] = 'Intervention anzeigen';
$string['motbot:allgood'] = 'Alles gut';
$string['motbot:interventions'] = 'Interventionen';
$string['motbot:notification'] = 'Benachrichtigung';
$string['motbot:gotocourse'] = 'Zum Kurs';
$string['motbot:goto'] = 'Gehe zu {$a}';
$string['motbot:reason'] = 'Grund der Intervention';
$string['motbot:date'] = 'Datum';
$string['motbot:state'] = 'Status';
$string['motbot:wereteachersinformed'] = 'Wurde das Lehrpersonal informiert';
$string['motbot:message'] = 'Nachricht';
$string['motbot:enablingmotbot'] = 'Motbot wird aktiviert';
$string['motbot:disabled'] = 'Der MotBot ist zurzeit deaktiviert.';
$string['motbot:ishappy'] = 'Der MotBot ist zufrieden.';
$string['motbot:isunhappy'] = 'Der MotBot könnte zufriedener sein.';
$string['motbot:moreinfo'] = 'Mehr Infos';
$string['motbot:activate'] = 'MotBot aktivieren';
$string['motbot:updated'] = 'Aktualisiert';
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
$string['reaction:coreeventcourse_viewed'] = '<div class="row"><img src="http://localhost/theme/image.php/boost/motbot/1639056026/icon" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true">&nbsp;&nbsp;<p>Schön, dass du wieder da bist!</p>&nbsp;{$a}</div>';
$string['reaction:coreeventuser_loggedin'] = '<div class="row"><img src="http://localhost/theme/image.php/boost/motbot/1639056026/icon" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true">&nbsp;&nbsp;<p>Schön, dass du wieder da bist!</p>&nbsp;{$a}</div>';
$string['reaction:mod_forumeventdiscussion_created'] = '<div class="row"><img src="http://localhost/theme/image.php/boost/motbot/1639056026/icon" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true">&nbsp;&nbsp;<p>Vielen Dank für deinen Beitrag im Forum!</p></div>';
$string['reaction:mod_forumeventpost_created'] = '<div class="row"><img src="http://localhost/theme/image.php/boost/motbot/1639056026/icon" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true">&nbsp;&nbsp;<p>Vielen Dank für deinen Beitrag im Forum!</p></div>';
$string['reaction:mod_forumeventassessable_uploaded'] = '<div class="row"><img src="http://localhost/theme/image.php/boost/motbot/1639056026/icon" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true">&nbsp;&nbsp;<p>Vielen Dank für deinen Beitrag im Forum!</p></div>';
$string['reaction:mod_chateventmessage_sent'] = '<div class="row"><img src="http://localhost/theme/image.php/boost/motbot/1639056026/icon" class="iconlarge activityicon" alt="" role="presentation" aria-hidden="true">&nbsp;&nbsp;<p>Vielen Dank für deine Teilnahme im Chat!</p></div>';
// ----

// ----  State of Intervention
$string['state:0'] = 'Geplant';
$string['state:1'] = 'Interveniert';
$string['state:2'] = 'Erfolgreich';
$string['state:3'] = 'Unerfolgreich';
$string['state:4'] = 'Eingelagert';
// ----

// ----  Settings
$string['settings:advanced_options'] = 'Erweiterte Einstellungen';
$string['settings:course_settings_header'] = '{$a->pluginname} Einstellungen für {$a->coursename}';
$string['settings:deleteinterventiondata'] = 'Lösche Interventions-Daten';
$string['settings:edit_motbot'] = 'MotBot Einstellungen';
$string['settings:edit_models'] = 'MotBot Analytik-Modell Einstellungen';
$string['settings:edit_advice'] = 'MotBot Empfehlungs Einstellungen';
// ----

// ----  Analytic Targets
$string['target:recentcognitivepresence'] = 'Kürzliche kognitive Präsenz der Lernenden (MotBot)';
$string['target:recentcognitivepresence_help'] = 'Dieses Ziel berechnet wie ausgiebig ein Nutzer oder eine Nutzerin sich mit den Lerninhalten auseinander gesetzt hat.';
$string['target:recent_cognitive_presence_short'] = 'Kürzliche kognitive Präsenz';
$string['target:recent_cognitive_presence_neutral'] = 'Kürzliche kognitive Präsenz';

$string['target:coursedropout'] = 'Bevorstehender Kursabbruch (MotBot)';
$string['target:coursedropout_help'] = 'Dieses Ziel berechnet, ob ein Nutzer oder eine Nutzerin Gefahr laufen einen Kurs abzubrechen.';
$string['target:course_dropout_short'] = 'Voraussichtlicher Kursabbruch';
$string['target:course_dropout_neutral'] = 'Risiko eines Kursabbruchs';

$string['target:lowsocialpresence'] = 'Lernende mit geringer sozialer Präsenz (MotBot)';
$string['target:lowsocialpresence_help'] = 'Dieses Ziel berrechnet wie ausgiebig ein Nutzer oder eine Nutzerin sich sozial in einem Kurs betätigt haben.';
$string['target:low_social_presence_short'] = 'Geringe soziale Präsenz';
$string['target:low_social_presence_neutral'] = 'Soziale Präsenz';

$string['target:norecentaccesses'] = 'Lernende die einen Kurs kürzlich nicht besucht haben (MotBot)';
$string['target:norecentaccesses_help'] = 'Dieses Ziel identifiziert Studierende, die einen Kurs, in dem sie eingeschrieben sind, nicht in einem festgelegten Analyse Interval (standardmäßig im vergangenen Monat) besucht haben.';
$string['target:no_recent_accesses_short'] = 'Keine kürzlichen Zugriffe';
$string['target:no_recent_accesses_neutral'] = 'Anwesenheit';

$string['target:upcomingactivitiesdue'] = 'Bevorstehende fällige Aktivitäten (MotBot)';
$string['target:upcomingactivitiesdue_help'] = 'Dieses Ziel generiert Erinnerungen für bevorstehende fällige Aktivitäten.';
$string['target:upcoming_activities_due_short'] = 'Bevorstehende fällige Aktivitäten';


$string['targetlabellowsocialpresenceno'] = 'Lernende:r mit geringer sozialer Präsenz';
$string['targetlabellowsocialpresenceyes'] = 'Lernende:r mit ausreichend sozialer Präsenz.';
// ----

// ---- Taskbot
$string['taskbot'] = 'Ausführen von geplanten Interventionen';
// ----

$string['tomanyinstances'] = 'Es sollte nur eine MotBot Aktivität pro Kurs geben.';

$string['userdisabledmotbot'] = 'Nutzer:in hat MotBot Aktivität deaktiviert.';
