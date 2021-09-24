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
 * Display information about all the mod_motbot modules in the requested course.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');

$id = required_param('id', PARAM_INT);           // Course ID
$helpful = required_param('helpful', PARAM_INT);           // Course ID

$intervention = $DB->get_record('motbot_intervention', array('id' => $id));
if($intervention) {
    $intervention->helpful = $helpful;
    $DB->update_record('motbot_intervention', $intervention);
}
$intervention = $DB->get_record('motbot_intervention', array('id' => $id));

$url = $CFG->wwwroot . '/message/output/popup/notifications.php';
redirect($url, 'Thank you for your feedback!', 1);