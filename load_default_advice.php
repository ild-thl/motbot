<?php
// This file is part of Moodle - https://moodle.org/
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
 * Check and create missing default prediction models and advice.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');

// require_login();

// $returnurl = new \moodle_url('/admin/tool/analytics/index.php');
// $myurl = new \moodle_url('/mod/motbot/load_default_advice.php');

// \tool_analytics\output\helper::set_navbar(get_string('restoredefault', 'tool_analytics'), $myurl);



foreach ($default = \mod_motbot\retention\advice_manager::load_default_advice_for_all_components() as $type => $component) {
    foreach ($component as $componentname => $advicelist) {
        $numcreated = 0;

        foreach ($advicelist as $definition) {
            \mod_motbot\retention\advice_manager::create_advice($definition);
            $numcreated++;
        }

        $message = get_string('load_default_advice', 'motbot', ['count' => $numcreated, 'component' => $componentname]);
        \core\notification::success($message);
    }
}

// print_r($default);


// redirect($returnurl, $message, null, $type);
