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
 * Interaction.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\retention\advice;

defined('MOODLE_INTERNAL') || die();

abstract class base {
    protected $user = null;
    protected $course = null;

    /**
    * Returns a lang_string object representing the name for the indicator or target.
    *
    * Used as column identificator.
    *
    * If there is a corresponding '_help' string this will be shown as well.
    *
    * @return \lang_string
    */
    public static abstract function get_name() : \lang_string;

    /**
     * Generates advices as text.
     *
     * @return void
    */
    public abstract function render();

    /**
     * Generates advices as html.
     *
     * @return void
    */
    public abstract function render_html();

    /**
     * Constructor.

     * @param \core\user $user
     * @param \core\course $course
     * @return void
    */
    public abstract function __construct($user, $course);
}