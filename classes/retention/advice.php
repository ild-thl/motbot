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
 * Advice representation.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\retention;

defined('MOODLE_INTERNAL') || die();

/**
 * Advice representation.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class advice {
    /**
     * @var \stdClass
     */
    protected $advice = null;


    /**
     * @var string[]
     */
    protected $targets = null;

    /**
     * Unique Model id created from site info and last model modification.
     *
     * @var string
     */
    protected $uniqueid = null;



    /**
     * Constructor.
     *
     * @param int|\stdClass $advice
     * @return void
     */
    public function __construct($advice) {
        global $DB;

        if (is_scalar($advice)) {
            $advice = $DB->get_record('motbot_advice', array('id' => $advice), '*', MUST_EXIST);
            if (!$advice) {
                throw new \moodle_exception('errorunexistingmodel', 'analytics', '', $advice);
            }
        }
        $this->advice = $advice;
    }

    /**
     * Quick safety check to discard advice which required components are not available anymore.
     *
     * @return bool
     */
    public function is_available() {
        $classname = $this->advice->name;
        if (!class_exists($classname)) {
            return false;
        }

        return true;
    }

    /**
     * Returns the model id.
     *
     * @return int
     */
    public function get_id() {
        return $this->advice->id;
    }

    /**
     * Returns a plain \stdClass with the advice data.
     *
     * @return \stdClass
     */
    public function get_advice_obj() {
        return $this->advice;
    }

    /**
     * Returns the advice targets.
     *
     * @return \core_analytics\local\target\base[]
     */
    public function get_targets() {
        if ($this->targets !== null) {
            return $this->targets;
        }

        $target_names = json_decode($this->advice->targets);

        if (!is_array($target_names)) {
            throw new \coding_exception('Advice ' . $this->advice->id . ' targets can not be read');
        }

        $this->targets = array();
        foreach ($target_names as $target_name) {
            $instance = \core_analytics\manager::get_target($target_name);
            if ($instance) {
                $this->targets[$target_name] = $instance;
            } else {
                debugging('Can\'t load ' . $target_name . ' target', DEBUG_DEVELOPER);
            }
        }

        return $this->targets;
    }

    /**
     * Returns the list of targets that could potentially be linked to the advice.
     *
     * It includes the targets that are already linked to the advice.
     *
     * @return \core_analytics\local\target\base[]
     */
    public function get_potential_targets() {

        $targets = \mod_motbot\manager::get_motbot_targets();

        return $targets;
    }

    /**
     * Creates a new model. Enables it if $timesplittingid is specified.
     *
     * @param object $definition
     * @return \mod_motbot\retention\advice
     */
    public static function create($definition) {
        global $USER, $DB;

        $now = time();

        $adviceobj = (object) [
            'name' => $definition['name'],
            'targets' => \json_encode($definition['targets']),
            'enabled' => $definition['enabled'],
            'version' => $now,
            'timecreated' => $now,
            'timemodified' => $now,
            'usermodified' => $USER->id,
        ];

        $id = $DB->insert_record('motbot_advice', $adviceobj);

        // Get db defaults.
        $adviceobj = $DB->get_record('motbot_advice', array('id' => $id), '*', MUST_EXIST);

        $advice = new static($adviceobj);

        return $advice;
    }

    /**
     * Does this advice exist?
     *
     * @param string $advice_name
     * @return bool
     */
    public static function exists($advice_name) {
        global $DB;

        return $DB->get_field('motbot_advice', 'id', array('name' => $advice_name), IGNORE_MISSING);
    }

    /**
     * Updates the model.
     *
     * @param int|bool $enabled
     * @param \core_analytics\local\target\base[]|false $targets False to respect current indicators
     * @return void
     */
    public function update($enabled, $targets = false) {
        global $USER, $DB;

        $now = time();

        if ($targets !== false) {
            $target_names = $this->get_valid_target_classes($targets);
            $targetsstr = json_encode($target_names);
        } else {
            // Respect current value.
            $targetsstr = $this->advice->targets;
        }

        if ($this->advice->targets !== $targetsstr) {
            // It needs to be reset as the version changes.
            $this->uniqueid = null;
            $this->targets = null;

            // We update the version of the model so different time splittings are not mixed up.
            $this->advice->version = $now;
        }

        $this->advice->enabled = intval($enabled);
        $this->advice->targets = $targetsstr;
        $this->advice->timemodified = $now;
        $this->advice->usermodified = $USER->id;

        $DB->update_record('motbot_advice', $this->advice);
    }

    /**
     * Removes the model.
     *
     * @return void
     */
    public function delete() {
        global $DB;

        $DB->delete_records('motbot_advice', array('id' => $this->advice->id));
    }

    /**
     * Enable the advice.
     *
     * @return void
     */
    public function enable() {
        global $DB, $USER;

        $now = time();

        // It needs to be reset as the version changes.
        $this->uniqueid = null;
        $this->advice->version = $now;
        $this->advice->enabled = 1;
        $this->advice->timemodified = $now;
        $this->advice->usermodified = $USER->id;

        $DB->update_record('motbot_advice', $this->advice);
    }

    /**
     * Is this advice enabled?
     *
     * @return bool
     */
    public function is_enabled() {
        return (bool)$this->advice->enabled;
    }

    /**
     * Returns a unique id for this advice.
     *
     * This id should be unique for this site.
     *
     * @return string
     */
    public function get_unique_id() {
        global $CFG;

        if (!is_null($this->uniqueid)) {
            return $this->uniqueid;
        }

        // Generate a unique id for this sitea and this advice, considering the last time
        // that the advice targets were updated.
        $ids = array($CFG->wwwroot, $CFG->prefix, $this->advice->id, $this->advice->version);
        $this->uniqueid = sha1(implode('$$', $ids));

        return $this->uniqueid;
    }

    /**
     * Exports the model data for displaying it in a template.
     *
     * @param \renderer_base $output The renderer to use for exporting
     * @return \stdClass
     */
    public function export(\renderer_base $output) {

        \core_analytics\manager::check_can_manage_models();

        $data = clone $this->advice;

        $data->advicename = $this->get_name();
        $data->adviceclass = $this->advice->name;


        $data->targets = array();
        foreach ($this->get_targets() as $target) {
            $data->targets[] = $target->get_name();
        }

        $data->targetsnum = count($data->targets);

        return $data;
    }

    /**
     * Utility method to return target class names from a list of target objects
     *
     * @param \core_analytics\local\target\base[]|string[] $targets
     * @return string[]
     */
    private static function get_valid_target_classes($tagets) {

        // What we want to check and store are the indicator classes not the keys.
        $targetclasses = array();
        foreach ($tagets as $target) {
            if (!\is_object($target)) {
                $target = \core_analytics\manager::get_target($target);
            }
            if (!\core_analytics\manager::is_valid($target, '\core_analytics\local\target\base')) {
                if (!is_object($target) && !is_scalar($target)) {
                    $target = strval($target);
                } else if (is_object($target)) {
                    $target = '\\' . get_class($target);
                }
                throw new \moodle_exception('errorinvalidtarget', 'analytics', '', $target);
            }
            $targetclasses[] = $target->get_id();
        }

        return $targetclasses;
    }

    /**
     * Returns the name of the advice.
     *
     * @return lang_string
     */
    public function get_name() {
        return $this->advice->name::get_name();
    }

    /**
     * Returns the classname of the advice.
     *
     * @return string
     */
    public function get_class() {
        return $this->advice->name;
    }

    /**
     * Returns an inplace editable element with the model's name.
     *
     * @return \core\output\inplace_editable
     */
    public function inplace_editable_name() {

        $displayname = format_string($this->get_name());

        return new \core\output\inplace_editable(
            'mod_motbot',
            'advicename',
            $this->advice->id,
            has_capability('moodle/analytics:managemodels', \context_system::instance()),
            $displayname,
            $this->advice->name
        );
    }
}
