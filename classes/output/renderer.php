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
 * Renderer.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;


/**
 * Renderer class.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends plugin_renderer_base {

    /**
     * Defer to template.
     *
     * @param \mod_motbot\output\advice_list $advicelist
     * @return string HTML
     */
    protected function render_models_list(\mod_motbot\output\advice_list $advicelist) {
        $data = $advicelist->export_for_template($this);
        return parent::render_from_template('mod_motbot/advice_list', $data);
    }

    /**
     * Renders a table.
     *
     * @param \table_sql $table
     * @return string HTML
     */
    public function render_table(\table_sql $table) {

        ob_start();
        $table->out(10, true);
        $output = ob_get_contents();
        ob_end_clean();

        return $output;
    }

    /**
     * Renders an motbot disabled notification.
     *
     * @return string HTML
     */
    public function render_motbot_disabled() {
        global $FULLME;

        $this->page->set_url($FULLME);
        $this->page->set_title(get_string('pluginname', 'mod_motbot'));
        $this->page->set_heading(get_string('pluginname', 'mod_motbot'));

        $output = $this->output->header();
        $output .= $this->output->notification(
            get_string('motbot:disabled', 'mod_motbot'),
            \core\output\notification::NOTIFY_INFO
        );
        $output .= \html_writer::tag('a', get_string('continue'), [
            'class' => 'btn btn-primary',
            'href' => (new \moodle_url('/'))->out()
        ]);
        $output .= $this->output->footer();

        return $output;
    }
}
