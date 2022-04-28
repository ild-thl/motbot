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
 * Chatbot that responds to messenger messages.
 *
 * @package   mod_motbot
 * @copyright 2022, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\retention\chatbot;

/**
 * Chatbot that responds to messenger messages.
 *
 * @package   mod_motbot
 * @copyright 2022, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class chatbot {
    protected $user;
    protected $chatid;
    protected $request;
    protected $messenger;

    public function __construct($chatid, $request) {
        global $DB;

        if (!isset($this->messenger)) {
            throw new \moodle_exception('No messenger is set.');
        }

        if (!isset($chatid)) {
            throw new \moodle_exception('No chatid received');
        }
        $this->chatid = $chatid;
        $this->request = strtolower($request);

        // Require and resolve userid from chatid.
        $userid = $DB->get_field('user_preferences', 'userid', array('value' => $this->chatid), MUST_EXIST);
        if (!$userid) {
            throw new \moodle_exception('Could\'nt resolve user_id');
        }
        $this->user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);

        $this->respond();
    }


    /**
     * Sends a message in response to a received request.
     *
     * @return bool True if response was sent sucessfully, otherwise false.
     */
    protected function respond() {
        $message = null;

        switch($this->request) {
            case 'advice':
                $message = $this->get_advice_message();
                break;
            case 'status':
                $message = $this->get_status_message();
                break;
            default:
                $message = $this->get_welcome_message();
                break;
        }

        if ((empty($message))) {
            return false;
        }

        // Send message.
        return $this->messenger->send_message($message, $this->user->id);
    }


    /**
     * Creates a message that contains a simple greeting.
     *
     * @return array Contains all information necessary for sending a message to the set message output.
     */
    abstract protected function get_welcome_message();


    /**
     * Creates a message containing a random motbot advice.
     *
     * @return array Contains all information necessary for sending a message.
     */
    abstract protected function get_advice_message();


    /**
     * Creates a message that informs the moodle user of its motbot status.
     *
     * @return array Contains all information necessary for sending a message.
     */
    abstract protected function get_status_message();
}
