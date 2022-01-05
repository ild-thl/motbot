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

/*
 * @package    mod_motbot
 * @copyright  2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// import {exception as displayException} from 'core/notification';
// import Templates from 'core/templates';

/**
 * Get MotBot state and update MotBot icon if necessary.
 *
 * @param {num} motbotid
 * @param {num} contextid
 * @param {bool} wasHappy
 */
export const init = async (motbotid, contextid, wasHappy) => {
  let state = await getState(motbotid, contextid);

  setIcon(state, wasHappy);
};

/**
 * Get the current MotBot State.
 *
 * @param {num} motbotid
 * @param {num} contextid
 *
 * @returns {Promise<bool>}
 */
async function getState(motbotid, contextid) {
  let response = await fetch(
    '/mod/motbot/get_motbot_state.php?motbotid=' +
      motbotid +
      '&contextid=' +
      contextid
  );
  return await response.json();
}

/**
 * Get the current MotBot State.
 * @param {bool} state
 * @param {bool} wasHappy
 */
function setIcon(state, wasHappy) {
  let icon = getIcon();
  const happyIcon = '/mod/motbot/pix/icon.svg';
  const unhappyIcon = '/mod/motbot/pix/icon-unhappy.svg';

  if (wasHappy != state) {
    if (state) {
      icon.src = happyIcon;
    } else {
      icon.src = unhappyIcon;
    }
  }
}

/**
 * Get the currently displayed MotBot icon.
 * @returns {Element}
 */
function getIcon() {
  return document.querySelector('.motbot .activityinstance img');
}
