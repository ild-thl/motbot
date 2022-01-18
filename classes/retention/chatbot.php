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

namespace mod_motbot\retention;

defined('MOODLE_INTERNAL') || die();

/**
 * Chatbot that responds to telegram messages.
 *
 * @package   mod_motbot
 * @copyright 2022, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class chatbot {
    private $user;
    private $chatid;
    private $message;
    private $request;
    private $telegram;
    private $is_callback = false;

    public function __construct($message) {
        global $DB;
        $this->message = $message;


        if (array_key_exists('callback_query', $message)) {
            $this->is_callback = true;
            // Require chatid.
            $this->chatid = $message['callback_query']['message']['chat']['id'];
            if (!$this->chatid) throw new \moodle_exception('No chatid received');

            $this->request = strtolower($message['callback_query']['data']);
        } else {
            // Require chatid.
            $this->chatid = $message['message']['from']['id'];
            if (!$this->chatid) throw new \moodle_exception('No chatid received');

            $this->request = strtolower($message['message']['text']);
        }

        // Require and resolve userid from chatid.
        $userid = $DB->get_field('user_preferences', 'userid', array('value' => $this->chatid), IGNORE_MISSING);
        if (!$userid) throw new \moodle_exception('Could\'nt resolve user_id');
        $this->user = $DB->get_record('user', array('id' => $userid), '*', IGNORE_MISSING);

        $this->telegram = new \message_telegram\manager();

        $this->respond();
    }

    private function respond() {
        $keyboard = null;

        if ($this->request == 'advice') {
            $advice_manager = new \mod_motbot\retention\advice_manager($this->user);
            $response = $advice_manager->render_random_advice('telegram');
            if ($response) {
                $response['chat_id'] = $this->chatid;

                // Send message.
                return $this->telegram->send_message($response);
            }
            $response = (new \lang_string('advice:noneavailable', 'mod_motbot', $this->user->firstname))->out($this->user->lang);
        } else if ($this->request == 'status') {
            if (\mod_motbot\manager::is_motbot_enabled($this->user->id)) {
                if (\mod_motbot\manager::is_motbot_happy($this->user->id)) {
                    $response = (new \lang_string('motbot:ishappy', 'mod_motbot', $this->user->firstname))->out($this->user->lang);
                } else {
                    $response = (new \lang_string('motbot:isunhappy', 'mod_motbot', $this->user->firstname))->out($this->user->lang);
                }
                $keyboard = $this->get_keyboard([[$this->get_status_info_button()]]);
            } else {
                $response = (new \lang_string('motbot:disabled', 'mod_motbot', $this->user->firstname))->out($this->user->lang);
                $keyboard = $this->get_keyboard($this->get_activate_button());
            }
        } else {
            $response = (new \lang_string('chatbot:default', 'mod_motbot', $this->user->firstname))->out($this->user->lang);

            $keyboard = $this->get_default_menu();
        }

        \print_r($keyboard);

        // Send message.
        return $this->telegram->send_message($response, null, [
            'chat_id' => $this->chatid,
            'parse_mode' => 'Markdown',
            'reply_markup' => $keyboard
        ]);
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
