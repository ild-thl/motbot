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

/**
 * Chatbot that responds to telegram messages.
 *
 * @package   mod_motbot
 * @copyright 2022, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class telegrambot extends chatbot {
    private $iscallback = false;

    public function __construct($message) {
        if (array_key_exists('callback_query', $message)) {
            $this->is_callback = true;
            // Require chatid.
            $chatid = $message['callback_query']['message']['chat']['id'];
            $request = $message['callback_query']['data'];
        } else {
            // Require chatid.
            $chatid = $message['message']['from']['id'];
            $request = $message['message']['text'];
        }

        $this->messenger = new \message_telegram\manager();

        parent::__construct($chatid, $request);
    }


    /**
     * Creates a telegram message containing a random motbot advice.
     *
     * @return array Contains all information necessary for sending a telegram message.
     */
    protected function get_welcome_message() {
        $response = (new \lang_string('chatbot:default', 'mod_motbot', $this->user->firstname))->out($this->user->lang);

        $keyboard = $this->get_default_menu();

        return [
            'text' => $response,
            'chat_id' => $this->chatid,
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
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
            $message = $advicemanager->render_random_advice('telegram');

            if (!$message) {
                $message = [
                    'text' => (new \lang_string('advice:noneavailable', 'mod_motbot', $this->user->firstname))->out($this->user->lang),
                    'parse_mode' => 'Markdown'
                ];
            }
            $message['chat_id'] = $this->chatid;
        } catch (\moodle_exception $e) {
            $keyboard = $this->get_keyboard([[$this->get_activate_button()]]);
            $message = [
                'text' => (new \lang_string('advice:noneavailable', 'mod_motbot', $this->user->firstname))
                ->out($this->user->lang) . PHP_EOL .
                (new \lang_string('motbot:disabled', 'mod_motbot', $this->user->firstname))
                    ->out($this->user->lang),
                'parse_mode' => 'Markdown',
                'reply_markup' => $keyboard,
                'chat_id' => $this->chatid,
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
            $keyboard = $this->get_keyboard([[$this->get_status_info_button()]]);
        } else {
            $response = (new \lang_string('motbot:disabled', 'mod_motbot', $this->user->firstname))->out($this->user->lang);
            $keyboard = $this->get_keyboard([[$this->get_activate_button()]]);
        }

        return [
            'text' => $response,
            'chat_id' => $this->chatid,
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ];
    }

    private function get_keyboard(array $buttons) {
        return \json_encode(["inline_keyboard" => $buttons]);
    }

    private function get_activate_button() {
        global $CFG;
        return [
            "text" => (new \lang_string('motbot:activate', 'mod_motbot', null))->out($this->user->lang),
            "url" => $CFG->wwwroot . "/mod/motbot/overview_settings.php"
        ];
    }

    private function get_status_info_button() {
        global $CFG;
        return [
            "text" => (new \lang_string('motbot:moreinfo', 'mod_motbot', null))->out($this->user->lang),
            "url" => $CFG->wwwroot . "/mod/motbot/overview.php"
        ];
    }

    private function get_status_button() {
        return [
            "text" => (new \lang_string('motbot:state', 'mod_motbot', null))->out($this->user->lang),
            "callback_data" => "status"
        ];
    }

    private function get_advice_button() {
        return [
            "text" => (new \lang_string('advice', 'mod_motbot', null))->out($this->user->lang),
            "callback_data" => "advice"
        ];
    }

    private function get_settings_button() {
        global $CFG;
        return [
            "text" => (new \lang_string('settings:edit_motbot', 'mod_motbot', null))->out($this->user->lang),
            "url" => $CFG->wwwroot . "/mod/motbot/overview_settings.php"
        ];
    }

    private function get_default_menu() {
        return $this->get_keyboard([
            [
                $this->get_status_button(),
                $this->get_advice_button()
            ],
            [
                $this->get_settings_button()
            ]
        ]);
    }
}
