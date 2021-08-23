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
 * Upgrades the database tables if not already uptodate.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_motbot_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    // if ($oldversion < 2021081815) {

    //     // Define field usecode to be added to motbot.
    //     $table = new xmldb_table('intervention');
    //     $field1 = new xmldb_field('message', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'state');
    //     $field2 = new xmldb_field('target', XMLDB_TYPE_CHAR, '128', null, null, null, null, 'state');

    //     // Conditionally launch add field usecode.
    //     if (!$dbman->field_exists($table, $field1)) {
    //         $dbman->add_field($table, $field1);
    //     }
    //     if (!$dbman->field_exists($table, $field2)) {
    //         $dbman->add_field($table, $field2);
    //     }

    //     // Motbot savepoint reached.
    //     upgrade_mod_savepoint(true, 2021081815, 'motbot');
    // }

    return true;
}
