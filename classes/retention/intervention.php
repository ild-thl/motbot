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

namespace mod_motbot\retention;

defined('MOODLE_INTERNAL') || die();

class intervention {
    const TARGET_NAME_REGEX = '/.+\\\(.+)/m';
    private $subject = null;
    private $prediction = null;
    private $method = null;
    private $desired_event = null;
    private $status = 'ongoing';

    public function __construct($subject, $prediction) {
        $this->subject = $subject;
        $this->prediction = $prediction;
        $this->method = $prediction;
        $this->desired_event = $prediction;

        $this->schedule();
    }


    private function send_intervention_message() {
        global $DB;

        $model = $DB->get_record('analytics_models', array('id'=> $this->prediction->modelid));
        if(!$model) {
            error_log('Model not found.');
            return;
        }

        preg_match(self::TARGET_NAME_REGEX, $model->target, $matches);
        $target_name = $matches[1];

        if(!$target_name || empty($target_name)) {
            error_log('Target name couldnt be identified.');
            return;
        }

        $message = new \core\message\message();
        $message->component = 'mod_motbot'; // Your plugin's name
        $message->name = 'motbot_intervention'; // Your notification name from message.php
        $message->userfrom = \core_user::get_noreply_user(); // If the message is 'from' a specific user you can set them here
        $message->userto = $this->subject;
        $message->subject = \get_string('message:' . $target_name . '_subject', 'motbot');
        $message->fullmessage = 'message body';
        $message->fullmessageformat = FORMAT_MARKDOWN;
        $message->fullmessagehtml = \get_string('message:' . $target_name . '_fullmessagehtml', 'motbot', $this->subject->firstname);
        $message->smallmessage = 'small message';
        $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message
        $message->contexturl = (new \moodle_url('/course/'))->out(false); // A relevant URL for the notification
        $message->contexturlname = 'Course list'; // Link title explaining where users get to for the contexturl
        // $content = array('*' => array('header' => ' test ', 'footer' => ' test ')); // Extra content for specific processor
        // $message->set_additional_content('email', $content);

        // Actually send the message
        $messageid = message_send($message);
        echo('Message ' . $messageid . ' sent to User ' . $this->subject->id);
    }

    private function schedule() {
        $this->intervene();
    }

    private function intervene() {
        switch($this->method) {
            default:
                $this->send_intervention_message();
        }
    }
}