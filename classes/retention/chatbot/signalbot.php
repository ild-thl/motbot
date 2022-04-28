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
 * Chatbot that responds to telegram messages.
 *
 * @package   mod_motbot
 * @copyright 2022, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\retention\chatbot;

use moodle_url;

/**
 * Chatbot that responds to telegram messages.
 *
 * @package   mod_motbot
 * @copyright 2022, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class signalbot extends chatbot {

    public function __construct($message) {

        $this->messenger = new \message_signal\manager();

        parent::__construct($message['sourceNumber'], $message['dataMessage']['message']);
    }


    /**
     * Creates a telegram message containing a random motbot advice.
     *
     * @return array Contains all information necessary for sending a telegram message.
     */
    protected function get_welcome_message() {
        return [
            'message' => (new \lang_string('chatbot:default', 'mod_motbot', $this->user->firstname))->out($this->user->lang) .
                $this->get_default_menu(),
        ];
    }


    /**
     * Creates a telegram message containing a random motbot advice.
     *
     * @return array Contains all information necessary for sending a telegram message.
     */
    protected function get_advice_message() {
        try {
            $advicemanager = new \mod_motbot\retention\advice_manager($this->user);
            $message = $advicemanager->render_random_advice('signal');

            if (!$message) {
                $message = [
                    'message' => (new \lang_string('advice:noneavailable', 'mod_motbot', $this->user->firstname))
                        ->out($this->user->lang),
                ];
            }
        } catch (\moodle_exception $e) {
            $message = [
                'message' => (new \lang_string('advice:noneavailable', 'mod_motbot', $this->user->firstname))
                ->out($this->user->lang) . PHP_EOL .
                (new \lang_string('motbot:disabled', 'mod_motbot', $this->user->firstname))
                    ->out($this->user->lang),
            ];
        }

        return $message;
    }


    /**
     * Creates a telegram message that informs the moodle user of its' motbot status.
     *
     * @return array Contains all information necessary for sending a telegram message.
     */
    protected function get_status_message() {
        if (\mod_motbot\manager::is_motbot_enabled($this->user->id)) {
            if (\mod_motbot\manager::is_motbot_happy($this->user->id)) {
                $response = (new \lang_string('motbot:ishappy', 'mod_motbot', $this->user->firstname))->out($this->user->lang);
            } else {
                $response = (new \lang_string('motbot:isunhappy', 'mod_motbot', $this->user->firstname))->out($this->user->lang);
            }
            $response .= PHP_EOL . (new \lang_string('motbot:moreinfo', 'mod_motbot', null))->out($this->user->lang) .
                ' ' . new moodle_url("/mod/motbot/overview.php");
        } else {
            $response = (new \lang_string('motbot:disabled', 'mod_motbot', $this->user->firstname))->out($this->user->lang);
            $response .= PHP_EOL . (new \lang_string('motbot:activate', 'mod_motbot', null))->out($this->user->lang) .
                ' ' . new moodle_url("/mod/motbot/overview_settings.php");
        }

        return [
            'message' => $response
        ];
    }

    private function get_default_menu() {
        return PHP_EOL . PHP_EOL . 'Available commands: ' .
        PHP_EOL . (new \lang_string('motbot:state', 'mod_motbot', null))->out($this->user->lang) . ': "status", ' .
        PHP_EOL . (new \lang_string('advice', 'mod_motbot', null))->out($this->user->lang) . ': "advice"' .
        PHP_EOL . PHP_EOL . 'Go to motbot settings: ' . new moodle_url("/mod/motbot/overview_settings.php");
    }
}
