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
 * Manages predictions and creates interventions.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\retention;

defined('MOODLE_INTERNAL') || die();

/**
 * Manages predictions and creates interventions.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class bot {

    public static function log_prediction($modelid, $sampleid, $rangeindex, \context $samplecontext, $scalar_prediction, $predictionscore) {


        $prediction = (object) [
            'modelid' => $modelid,
            'contextid' => $samplecontext->id,
            'sampleid' => $sampleid,
            'rangeindex' => $rangeindex,
            'prediction' => $scalar_prediction,
            'predictionscore' => $predictionscore
        ];

        $intervention = \mod_motbot\retention\intervention::from_prediction($prediction);

        $intervention->schedule();

        return;
    }
}