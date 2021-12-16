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
 * Advice definitions for the motbot module
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$advice = [
    [
        'name' => '\mod_motbot\retention\advice\recommended_discussion',
        'targets' => [
            '\mod_motbot\analytics\target\low_social_presence',
        ],
        'enabled' => true,
    ],
    [
        'name' => '\mod_motbot\retention\advice\recent_forum_activity',
        'targets' => [
            '\mod_motbot\analytics\target\low_social_presence',
            '\mod_motbot\analytics\target\recent_cognitive_presence',
        ],
        'enabled' => true,
    ],
    [
        'name' => '\mod_motbot\retention\advice\course_completion',
        'targets' => [
            '\mod_motbot\analytics\target\no_recent_accesses',
        ],
        'enabled' => true,
    ],
    [
        'name' => '\mod_motbot\retention\advice\visit_course',
        'targets' => [
            '\mod_motbot\analytics\target\no_recent_accesses',
        ],
        'enabled' => true,
    ],
    [
        'name' => '\mod_motbot\retention\advice\recent_activities',
        'targets' => [
            '\mod_motbot\analytics\target\no_recent_accesses',
            '\mod_motbot\analytics\target\recent_cognitive_presence',
        ],
        'enabled' => true,
    ],
    [
        'name' => '\mod_motbot\retention\advice\feedback',
        'targets' => [
            '\mod_motbot\analytics\target\low_social_presence',
            '\mod_motbot\analytics\target\no_recent_accesses',
        ],
        'enabled' => true,
    ],
    [
        'name' => '\mod_motbot\retention\advice\last_stop',
        'targets' => [
            '\mod_motbot\analytics\target\no_recent_accesses',
            '\mod_motbot\analytics\target\recent_cognitive_presence',
        ],
        'enabled' => true,
    ],
];
