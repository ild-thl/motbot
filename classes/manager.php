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
 * Manages predictions and creates interventions.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot;

defined('MOODLE_INTERNAL') || die();

/**
 * Library of useful helper functions for managing predictions and interventions.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {
    public static function get_prediction_subject($sampleid, $target = null) {
        global $DB;
        if($target == '\mod_motbot\analytics\target\upcoming_activities_due') {
            $sql = "SELECT id as userid
                FROM mdl_user
                WHERE id = :sampleid;";

        } else {
            $sql = "SELECT u.id as userid
                    FROM mdl_user u
                    JOIN mdl_user_enrolments ue ON ue.userid = u.id
                    WHERE ue.id = :sampleid;";
        }

        $result = $DB->get_record_sql($sql, array('sampleid' => $sampleid));

        if(!$result) {
            echo('No user found! ' . $sampleid);
            return null;
        }

        return $result->userid;
    }
}