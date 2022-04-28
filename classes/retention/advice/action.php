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
 * Abstract class for advices that need a title and exactly one call to action.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\retention\advice;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/motbot/locallib.php');

/**
 * Abstract class for advices that need a title and exactly one call to action.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class action extends \mod_motbot\retention\advice\base {
    protected $title = null;
    protected $action_url = null;
    protected $action = null;

    /**
     * Generates advices as text.
     *
     * @return string
     */
    public function render() {
        return ($this->title ? $this->title . " " : "") . '[' . $this->action . '](' . $this->action_url . ')';
    }

    /**
     * Generates advices as html.
     *
     * @return string
     */
    public function render_html() {
        global $OUTPUT;

        $context = [
            "title" => $this->title,
            "action_url" => $this->action_url,
            "action" => $this->action,
        ];

        return $OUTPUT->render_from_template('mod_motbot/action', $context);
    }

    /**
     * Generates telegram message object.
     *
     * @return array
     */
    public function render_telegram() {
        $keyboard = \json_encode([
            "inline_keyboard" => [
                [
                    [
                        "text" => $this->action,
                        "url" => $this->action_url
                    ]
                ]
            ]
        ]);

        return [
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard,
            'text' => $this->title
        ];
    }
}
