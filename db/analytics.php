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
 * Capability definitions for the motbot module
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$models = [
    [
        'target' => '\mod_motbot\analytics\target\course_dropout',
        'indicators' => [
            '\core\analytics\indicator\any_access_after_end',
            '\core\analytics\indicator\any_access_before_start',
            '\core\analytics\indicator\any_write_action_in_course',
            '\core\analytics\indicator\read_actions',
            '\core_course\analytics\indicator\completion_enabled',
            '\core_course\analytics\indicator\potential_cognitive_depth',
            '\core_course\analytics\indicator\potential_social_breadth',
            '\mod_assign\analytics\indicator\cognitive_depth',
            '\mod_assign\analytics\indicator\social_breadth',
            '\mod_book\analytics\indicator\cognitive_depth',
            '\mod_book\analytics\indicator\social_breadth',
            '\mod_chat\analytics\indicator\cognitive_depth',
            '\mod_chat\analytics\indicator\social_breadth',
            '\mod_choice\analytics\indicator\cognitive_depth',
            '\mod_choice\analytics\indicator\social_breadth',
            '\mod_data\analytics\indicator\cognitive_depth',
            '\mod_data\analytics\indicator\social_breadth',
            '\mod_feedback\analytics\indicator\cognitive_depth',
            '\mod_feedback\analytics\indicator\social_breadth',
            '\mod_folder\analytics\indicator\cognitive_depth',
            '\mod_folder\analytics\indicator\social_breadth',
            '\mod_forum\analytics\indicator\cognitive_depth',
            '\mod_forum\analytics\indicator\social_breadth',
            '\mod_glossary\analytics\indicator\cognitive_depth',
            '\mod_glossary\analytics\indicator\social_breadth',
            '\mod_imscp\analytics\indicator\cognitive_depth',
            '\mod_imscp\analytics\indicator\social_breadth',
            '\mod_label\analytics\indicator\cognitive_depth',
            '\mod_label\analytics\indicator\social_breadth',
            '\mod_lesson\analytics\indicator\cognitive_depth',
            '\mod_lesson\analytics\indicator\social_breadth',
            '\mod_lti\analytics\indicator\cognitive_depth',
            '\mod_lti\analytics\indicator\social_breadth',
            '\mod_page\analytics\indicator\cognitive_depth',
            '\mod_page\analytics\indicator\social_breadth',
            '\mod_quiz\analytics\indicator\cognitive_depth',
            '\mod_quiz\analytics\indicator\social_breadth',
            '\mod_resource\analytics\indicator\cognitive_depth',
            '\mod_resource\analytics\indicator\social_breadth',
            '\mod_scorm\analytics\indicator\cognitive_depth',
            '\mod_scorm\analytics\indicator\social_breadth',
            '\mod_survey\analytics\indicator\cognitive_depth',
            '\mod_survey\analytics\indicator\social_breadth',
            '\mod_url\analytics\indicator\cognitive_depth',
            '\mod_url\analytics\indicator\social_breadth',
            '\mod_wiki\analytics\indicator\cognitive_depth',
            '\mod_wiki\analytics\indicator\social_breadth',
            '\mod_workshop\analytics\indicator\cognitive_depth',
            '\mod_workshop\analytics\indicator\social_breadth',
        ],
    ],
    // [
    //     'target' => '\mod_motbot\analytics\target\upcoming_activities_due',
    //     'indicators' => [
    //         '\core_course\analytics\indicator\activities_due',
    //     ],
    //     'timesplitting' => '\core\analytics\time_splitting\upcoming_week',
    //     'enabled' => true,
    // ],
    [
        'target' => '\mod_motbot\analytics\target\no_recent_accesses',
        'indicators' => [
            '\core\analytics\indicator\any_course_access',
        ],
        'timesplitting' => '\core\analytics\time_splitting\past_week',
        'enabled' => true,
    ],
    [
        'target' => '\mod_motbot\analytics\target\low_social_presence',
        'indicators' => [
            'mod_motbot\analytics\indicator\social_presence_in_course_forum',
            'mod_motbot\analytics\indicator\social_presence_in_course_chat',
            'mod_motbot\analytics\indicator\any_write_action_in_course_feedback_yet',
        ],
        'timesplitting' => '\core\analytics\time_splitting\past_week',
        'enabled' => true,
    ],
];

$result = [];

foreach ($models as $model) {
    if (!\core_analytics\model::exists(\core_analytics\manager::get_target($model['target']))) {
        $result[] = \core_analytics\manager::create_declared_model($model);
    }
}
