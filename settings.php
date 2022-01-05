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
 * Plugin administration pages are defined here.
 *
 * @package     mod_ilddigitalcert
 * @category    admin
 * @copyright   2020 ILD TH Lübeck <dev.ild@th-luebeck.de>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    $modfolder = new admin_category(
        'modmotbot',
        new lang_string(
            'pluginname',
            'mod_motbot'
        ),
        $module->is_enabled() === false
    );
    $ADMIN->add('modsettings', $modfolder);

    $ADMIN->add(
        'modmotbot',
        new admin_externalpage(
            'motbot_edit_motbot',
            get_string('settings:edit_motbot', 'mod_motbot'),
            $CFG->wwwroot . '/mod/motbot/edit_motbot.php'
        )
    );

    $ADMIN->add(
        'modmotbot',
        new admin_externalpage(
            'motbot_edit_models',
            get_string('settings:edit_models', 'mod_motbot'),
            $CFG->wwwroot . '/mod/motbot/edit_models.php'
        )
    );

    $ADMIN->add(
        'modmotbot',
        new admin_externalpage(
            'motbot_advice_settings',
            get_string('settings:edit_advice', 'mod_motbot'),
            $CFG->wwwroot . '/mod/motbot/advice_settings.php'
        )
    );
}
// Prevent Moodle from adding settings block in standard location.
$settings = null;
