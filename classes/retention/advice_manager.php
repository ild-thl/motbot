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
 * Manages the generation of advice for an intervention.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\retention;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/motbot/locallib.php');

/**
 * Manages the generation of advice for an intervention.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class advice_manager {

    /**
     * Name of the file where components declare their advice.
     */
    const ADVICE_FILENAME = 'db/advice.php';

    /**
     * @var \core\user Subject of the advice, Moodle user.
     */
    private $user;

    /**
     * @var \core\course The course where the bot intervenes the user.
     */
    private $course;

    /**
     * @var int The target of the analytics model.
     */
    private $target;

    /**
     * @var int User id of the intervention recipient.
     */
    private $advice = null;

    /**
     * Contstructor.
     *
     * @param \core\user $user
     * @param \core\course $course
     * @param \mod_motbot\retention\core_analytics\local\target\base $target
     */
    public function __construct($user, $course, $target) {
        $this->user = $user;
        $this->course = $course;
        $this->target = $target;
    }

    /**
     * Initialize advice, that is available in this context (user, course, target).
     *
     * @param string $class
     * @return array
     */
    private function generate_advice() {
        global $DB;

        $advice = array();
        // Get advice settings.
        $advice_settings = $DB->get_records('motbot_advice', array());

        $sql = 'SELECT cu.disabled_advice
            FROM {motbot_course_user} as cu
            JOIN {motbot} as m
            ON m.id = cu.motbot
            WHERE cu.user = :userid
            AND m.course = :courseid';
        $course_user = $DB->get_record_sql($sql, array('userid' => $this->user->id, 'courseid' => $this->course->id), IGNORE_MISSING);
        $disabled_advice = json_decode($course_user->disabled_advice);

        // Initialize advice, if aplicable for the set target
        foreach ($advice_settings as $setting) {
            if (!$setting->enabled) { // Skip, if advice is disabled.
                continue;
            }

            $targets = json_decode($setting->targets);
            if (!in_array($this->target, $targets)) { // Skip, if target is not part of the defined aplicable targets.
                continue;
            }

            if (!empty($disabled_advice) && in_array($setting->name, $disabled_advice)) {
                continue;
            }

            if ($a = $this->get_advice_if_available($setting->name)) {
                $advice[] = $a;
            }
        }

        return $advice;
    }

    /**
     * Try to initialize a specific advice object.
     *
     * @param string $class
     * @return mod_motbot\retention\advice\base
     */
    public function get_advice_if_available($class) {
        $advice = null;
        try {
            // Init new advice
            $advice = new $class($this->user, $this->course);
        } catch (\moodle_exception $e) {
            print_r($e->getMessage());
        }
        return $advice;
    }

    /**
     * Generates advice as text.
     *
     * @return string
     */
    public function render() {
        $advice_output = '';

        if ($this->advice == null) {
            $this->advice = $this->generate_advice();
        }
        if (empty($this->advice)) {
            return $advice_output;
        }

        foreach ($this->advice as $advice) {
            $advice_output .= PHP_EOL . PHP_EOL . $advice->render();
        }

        return $advice_output;
    }

    /**
     * Generates advice as html.
     *
     * @return string
     */
    public function render_html() {
        global $OUTPUT;
        $advice_output = '';

        if ($this->advice == null) {
            $this->advice = $this->generate_advice();
        }
        if (empty($this->advice)) {
            return $advice_output;
        }
        $advices = array();
        foreach ($this->advice as $advice) {
            $advices[] = $advice->render_html();
        }
        $context = (object) [
            "advices" => $advices,
        ];
        $advice_output = $OUTPUT->render_from_template('mod_motbot/advices', $context);

        return $advice_output;
    }


    /**
     * Define and create DB entrys for advice, so motbot knows wich advice to generate in diffrent situations.
     *
     * @param array $advice
     * @param bool $update
     * @return bool
     */
    static function create_advice($advice, $update = true) {
        global $DB;

        // Json Encode targets array, because DB only accepts strings.
        $advice['targets'] = json_encode($advice['targets']);

        // Look for entry with same name.
        $exists = $DB->get_field('motbot_advice', 'id', array('name' => $advice['name']), IGNORE_MISSING);
        try {
            if (!$exists) { // Else insert new record.
                $id = $DB->insert_record('motbot_advice', $advice);

                if (!$id) {
                    throw new \dml_exception('Could\'nt insert advice into database.');
                }

                return true;
            } else if ($update) { // If another record exists, update it.
                $advice['id'] = $exists;
                $DB->update_record('motbot_advice', $advice);
            }
        } catch (\moodle_exception $e) {
            error_log($e->getMessage());
        }

        return false;
    }


    /**
     * Return the list of advice declared by the given component.
     *
     * @param string $componentname The name of the component to load advice for.
     * @throws \coding_exception Exception thrown in case of invalid syntax.
     * @return array The $advice description array.
     */
    public static function load_default_advice_for_component(string $componentname): array {

        $dir = \core_component::get_component_directory($componentname);

        if (!$dir) {
            // This is either an invalid component, or a core subsystem without its own root directory.
            return [];
        }

        $file = $dir . '/' . self::ADVICE_FILENAME;

        if (!is_readable($file)) {
            return [];
        }

        $advice = null;
        include($file);

        if (!isset($advice) || !is_array($advice) || empty($advice)) {
            return [];
        }

        foreach ($advice as &$ad) {
            if (!isset($ad['enabled'])) {
                $ad['enabled'] = false;
            } else {
                $ad['enabled'] = clean_param($ad['enabled'], PARAM_BOOL);
            }
        }

        static::validate_advice_declaration($advice);

        return $advice;
    }

    /**
     * Return the list of advice declared anywhere in this Moodle installation.
     *
     * Models defined by the core and core subsystems come first, followed by those provided by plugins.
     *
     * @return array indexed by the frankenstyle component
     */
    public static function load_default_advice_for_all_components(): array {

        $result = array();

        foreach (\core_component::get_component_list() as $type => $components) {
            foreach (array_keys($components) as $component) {
                if ($loaded = static::load_default_advice_for_component($component)) {
                    $result[$type][$component] = $loaded;
                }
            }
        }

        return $result;
    }

    public static function load_default_advice($update = false) {
        foreach (\mod_motbot\retention\advice_manager::load_default_advice_for_all_components() as $type => $component) {
            foreach ($component as $componentname => $advicelist) {
                $numcreated = 0;
                $numupdated = 0;

                foreach ($advicelist as $definition) {
                    $created = false;
                    if (!$exists = \mod_motbot\retention\advice::exists($definition['name'])) {
                        if (\mod_motbot\retention\advice::create($definition)) {
                            $numcreated++;
                        }
                    } else if ($update) {
                        $advice = new \mod_motbot\retention\advice($exists);
                        $advice->update($definition['enabled'], $definition['targets']);
                        $numupdated++;
                    }
                }
                if ($numupdated) {
                    $updatedmessage = get_string('advice:updated', 'motbot', ['count' => $numupdated, 'component' => $componentname]);
                    \core\notification::info($updatedmessage);
                }

                $createdmessage = get_string('advice:created', 'motbot', ['count' => $numcreated, 'component' => $componentname]);
                \core\notification::info($createdmessage);
            }
        }
    }

    /**
     * Validate the declaration of advice according to the syntax expected in the component's db folder.
     *
     * The expected structure looks like this:
     *
     *  [
     *      [
     *          'name' => '\fully\qualified\name\of\the\advice\classname',
     *          'targets' => [
     *              '\fully\qualified\name\of\the\first\target',
     *              '\fully\qualified\name\of\the\second\target',
     *          ],
     *          'enabled' => true,
     *      ],
     *  ];
     *
     * @param array $advice_list List of declared advice.
     * @throws \coding_exception Exception thrown in case of invalid syntax.
     */
    public static function validate_advice_declaration(array $advice_list) {

        foreach ($advice_list as $advice) {
            if (!isset($advice['name'])) {
                throw new \coding_exception('Missing advice name declaration');
            }

            if (!static::is_valid($advice['name'], '\mod_motbot\retention\advice\base')) {
                throw new \coding_exception('Invalid advice classname', $advice['name']);
            }

            if (empty($advice['targets']) || !is_array($advice['targets'])) {
                throw new \coding_exception('Missing advice targets declaration');
            }

            foreach ($advice['targets'] as $target) {
                if (!static::is_valid($target, '\core_analytics\local\target\base')) {
                    throw new \coding_exception('Invalid advice target classname', $target);
                }
            }
        }
    }

    /**
     * Returns whether a classname is valid or not.
     *
     * @param string $fullclassname
     * @param string $baseclass
     * @return bool
     */
    public static function is_valid($fullclassname, $baseclass) {
        if (is_subclass_of($fullclassname, $baseclass)) {
            if ((new \ReflectionClass($fullclassname))->isInstantiable()) {
                return true;
            }
        }
        return false;
    }
}
