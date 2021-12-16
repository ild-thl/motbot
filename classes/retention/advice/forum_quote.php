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
 * Abstract class for advices that consist of a title, a quote of a forum post, and one call to action.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\retention\advice;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/motbot/locallib.php');

/**
 * Abstract class for advices that consist of a title, a quote of a forum post, and one call to action.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class forum_quote extends \mod_motbot\retention\advice\base {
    protected $title = null;
    protected $subject = null;
    protected $message = null;
    protected $author = null;
    protected $date = null;
    protected $action_url = null;
    protected $action = null;

    /**
     * Generates advices as text.
     *
     * @return void
    */
    public function render() {
        $message = $this->title . PHP_EOL;
        $message .= PHP_EOL;
        $message .= 'Posted by *' . $this->author . '* on ' . $this->date . ':' . PHP_EOL;
        $message .= PHP_EOL;
        $message .= '*' . $this->subject . '*' . PHP_EOL;
        $message .= '```' . strip_tags($this->message) . '```' . PHP_EOL . PHP_EOL;
        $message .= '*' . $this->action . '*: _' . $this->action_url . '_';
        $message .= PHP_EOL;
        return $message;
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
            "subject" => $this->subject,
            "message" => $this->message,
            "author" => $this->author,
            "date" => $this->date,
            "action_url" => $this->action_url,
            "action" => $this->action,
        ];

        return $OUTPUT->render_from_template('mod_motbot/forum_quote', $context);
    }
}