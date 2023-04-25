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
 * Post installation hook for removing entry in custom user menu.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Post installation procedure
 *
 * Add a motbot menu item to the custom user menu.
 */
function xmldb_motbot_install() {
    global $CFG;
    $result = true;

    $old_item = "\nmodulenameplural,mod_motbot|/mod/motbot/overview.php|grades";
    $new_item = "\nmodulenameplural,mod_motbot|/mod/motbot/overview.php";
    $menu = $CFG->customusermenuitems;
    // Remove any old motbot menu items, if there are any.
    $menu = str_replace($new_item, "", $menu);
    $menu = str_replace($old_item, "", $menu);

    // Add the motbot menu item.
    $menu = $menu .= $new_item;
    set_config('customusermenuitems', $menu);

    // Load default advice from all components.
    \mod_motbot\retention\advice_manager::load_default_advice();

    return $result;
}
