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
 * Abstract class for advices that need a title and one or more call to actions.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\retention\advice;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/motbot/locallib.php');

/**
 * Abstract class for advices that need a title and one or more call to actions.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class title_and_actionrow extends \mod_motbot\retention\advice\base {
    protected $title = null;
    protected $actions = null;

    /**
     * Generates advices as text.
     *
     * @return void
    */
    public function render() {
        $result = $this->title;
        foreach($this->actions as $action) {
            $result .=  PHP_EOL . '*' . $action['action'] . '*: _' . $action['action_url'] . '_';
        }
        return $result;
    }

    /**
     * Generates advices as html.
     *
     * @return void
    */
    public function render_html() {
        global $OUTPUT;

        $context = [
            "title" => $this->title,
            "actions" => $this->actions
        ];

        return $OUTPUT->render_from_template('mod_motbot/title_and_actionrow', $context);
    }
}