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
 * Motbot Advice list page.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\output;

defined('MOODLE_INTERNAL') || die();

/**
 * Shows mod_motbot advice list.
 *
 * @package   mod_motbot
 * @copyright 2021, Pascal Hürten <pascal.huerten@th-luebeck.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class advice_list implements \renderable, \templatable {
    /**
     * advice
     *
     * @var \mod_motbot\retention\advice[]
     */
    protected $advice = null;

    /**
     * __construct
     *
     * @param \mod_motbot\retention\advice[] $advice
     * @return void
     */
    public function __construct($advice) {
        $this->advice = $advice;
    }

    /**
     * Exports the data.
     *
     * @param \renderer_base $output
     * @return \stdClass
     */
    public function export_for_template(\renderer_base $output) {
        global $PAGE;

        $data = new \stdClass();

        $newadvicemenu = new \action_menu();
        $newadvicemenu->set_menu_trigger(get_string('advice:update', 'mod_motbot'), 'btn btn-default');
        $newadvicemenu->set_alignment(\action_menu::TL, \action_menu::BL);

        $urlparams = ['action' => 'load_new', 'sesskey' => sesskey()];
        $url = new \moodle_url('/mod/motbot/advice_settings.php', $urlparams);
        $newadvicemenu->add(new \action_menu_link(
            $url,
            new \pix_icon('i/import', ''),
            get_string('advice:load_new', 'mod_motbot'),
            false
        ));

        $urlparams = ['action' => 'restore_default', 'sesskey' => sesskey()];
        $url = new \moodle_url('/mod/motbot/advice_settings.php', $urlparams);
        $newadvicemenu->add(new \action_menu_link(
            $url,
            new \pix_icon('i/reload', ''),
            get_string('advice:restore_default', 'mod_motbot'),
            false
        ));

        $data->newadvicemenu = $newadvicemenu->export_for_template($output);

        // $misconfiguredadvice = [];
        $data->advice = array();

        foreach ($this->advice as $advice) {
            $advicedata = $advice->export($output);

            // Check if there is a help icon for the target to show.
            $identifier = $advicedata->advicename->get_identifier();
            $component = $advicedata->advicename->get_component();
            if (get_string_manager()->string_exists($identifier . '_help', $component)) {
                $helpicon = new \help_icon($identifier, $component);
                $advicedata->advicehelp = $helpicon->export_for_template($output);
            } else {
                // We really want to encourage developers to add help to their targets.
                debugging("The advice '{$advicedata->advicename}' should include a '{$identifier}_help' string to
                    describe its purpose.", DEBUG_DEVELOPER);
            }

            // Check if there is a help icon for the targets to show.
            if (!empty($advicedata->targets)) {
                $targets = array();
                foreach ($advicedata->targets as $tar) {
                    // Create the target with the details we want for the context.
                    $target = new \stdClass();
                    $target->name = $tar->out();
                    $identifier = $tar->get_identifier();
                    $component = $tar->get_component();
                    if (get_string_manager()->string_exists($identifier . '_help', $component)) {
                        $helpicon = new \help_icon($identifier, $component);
                        $target->help = $helpicon->export_for_template($output);
                    } else {
                        // We really want to encourage developers to add help to their targets.
                        debugging("The target '{$tar}' should include a '{$identifier}_help' string to
                            describe its purpose.", DEBUG_DEVELOPER);
                    }
                    $targets[] = $target;
                }
                $advicedata->targets = $targets;
            }


            // Actions.
            $actionsmenu = new \action_menu();
            $actionsmenu->set_menu_trigger(get_string('actions'));
            $actionsmenu->set_owner_selector('model-actions-' . $advice->get_id());
            $actionsmenu->set_alignment(\action_menu::TL, \action_menu::BL);

            $urlparams = ['id' => $advice->get_id(), 'sesskey' => sesskey()];

            // Edit model.
            $urlparams['action'] = 'edit';
            $url = new \moodle_url('/mod/motbot/advice_settings.php', $urlparams);
            $icon = new \action_menu_link_secondary($url, new \pix_icon('t/edit', get_string('edit')), get_string('edit'));
            $actionsmenu->add($icon);

            // Enable / disable.
            if ($advice->is_enabled()) {
                $action = 'disable';
                $text = get_string('disable');
                $icontype = 't/block';
            } else {
                $action = 'enable';
                $text = get_string('enable');
                $icontype = 'i/checked';
            }
            $urlparams['action'] = $action;
            $url = new \moodle_url('/mod/motbot/advice_settings.php', $urlparams);
            $icon = new \action_menu_link_secondary($url, new \pix_icon($icontype, $text), $text);
            $actionsmenu->add($icon);


            // Delete model.
            $actionid = 'delete-' . $advice->get_id();
            $PAGE->requires->js_call_amd('tool_analytics/model', 'confirmAction', [$actionid, 'delete']);
            $urlparams['action'] = 'delete';
            $url = new \moodle_url('/mod/motbot/advice_settings.php', $urlparams);
            $icon = new \action_menu_link_secondary(
                $url,
                new \pix_icon(
                    't/delete',
                    get_string('delete', 'tool_analytics')
                ),
                get_string('delete', 'tool_analytics'),
                ['data-action-id' => $actionid]
            );
            $actionsmenu->add($icon);

            $advicedata->actions = $actionsmenu->export_for_template($output);

            $data->advice[] = $advicedata;
        }
        // $data->warnings = [];
        // $data->infos = [];
        // if (!$onlycli) {
        //     $data->warnings[] = (object)array('message' => get_string('bettercli', 'tool_analytics'), 'closebutton' => true);
        // } else {
        //     $url = new \moodle_url(
        //         '/admin/settings.php',
        //         array('section' => 'analyticssettings'),
        //         'id_s_analytics_onlycli'
        //     );

        //     $langstrid = 'clievaluationandpredictionsnoadmin';
        //     if (is_siteadmin()) {
        //         $langstrid = 'clievaluationandpredictions';
        //     }
        //     $data->infos[] = (object)array(
        //         'message' => get_string($langstrid, 'tool_analytics', $url->out()),
        //         'closebutton' => true
        //     );
        // }

        // if ($misconfiguredmodels) {
        //     $warningstr = get_string('invalidtimesplittinginmodels', 'tool_analytics', implode(', ', $misconfiguredmodels));
        //     $data->warnings[] = (object)array('message' => $warningstr, 'closebutton' => true);
        // }
        return $data;
    }
}
