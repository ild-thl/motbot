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
 * Telegram connection handler.
 *
 * @package message_telegram
 * @author  Pascal Hürten
 * @copyright  2022 onwards Pascal Hürten (pascal.huerten@gmail.de)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');

// Get post data.
$json = file_get_contents('php://input');
if (!$json) die('no data received');

// Decode Json data.
$request = json_decode($json, true);
if ($request === false) die('Couldn\'t decode json.');

if (class_exists('\mod_motbot\retention\chatbot')) {
    new \mod_motbot\retention\chatbot($request);
    die('Ok');
}

// $response = print_r($update);
$response = 'Hi *' . $request->firstname . ' ' . $request->lastname . '*.\n Sorry, the chatbot is currently out of order.';

$telegrammanager = new message_telegram\manager();
$telegrammanager->send_message($response, null, ['chat_id' => $chatid]);
die('\mod_motbot\retention\chatbot class not found');
