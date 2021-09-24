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
 * Student enrolments analyser.
 *
 * @package   core
 * @copyright 2016 David Monllao {@link http://www.davidmonllao.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_motbot\analytics\analyser;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/enrollib.php');

/**
 * Student enrolments analyser.
 *
 * It does return all student enrolments including the suspended ones.
 *
 * @package   core
 * @copyright 2016 David Monllao {@link http://www.davidmonllao.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class teacher_enrolments extends \core\analytics\analyser\student_enrolments {

    /**
     * Returns the student enrolment course.
     *
     * @param int $sampleid
     * @return \core_analytics\analysable
     */
    public function get_sample_analysable($sampleid) {
        $course = enrol_get_course_by_user_enrolment_id($sampleid);
        return \mod_motbot\analysable\course::instance($course);
    }


    /**
     * All course student enrolments.
     *
     * It does return all student enrolments including the suspended ones.
     *
     * @param \core_analytics\analysable $course
     * @return array
     */
    public function get_all_samples(\core_analytics\analysable $course) {

        $enrolments = enrol_get_course_users($course->get_id());

        // We fetch all enrolments, but we are only interested in students.
        $studentids = $course->get_teachers();

        $samplesdata = array();
        foreach ($enrolments as $userenrolmentid => $user) {

            if (empty($studentids[$user->id])) {
                // Not a student or an analysed one.
                continue;
            }

            $sampleid = $userenrolmentid;
            $samplesdata[$sampleid]['user_enrolments'] = (object)array(
                'id' => $user->ueid,
                'status' => $user->uestatus,
                'enrolid' => $user->ueenrolid,
                'userid' => $user->id,
                'timestart' => $user->uetimestart,
                'timeend' => $user->uetimeend,
                'modifierid' => $user->uemodifierid,
                'timecreated' => $user->uetimecreated,
                'timemodified' => $user->uetimemodified
            );
            unset($user->ueid);
            unset($user->uestatus);
            unset($user->ueenrolid);
            unset($user->uetimestart);
            unset($user->uetimeend);
            unset($user->uemodifierid);
            unset($user->uetimecreated);
            unset($user->uetimemodified);

            // This student has been already analysed. We analyse each student once.
            unset($studentids[$user->id]);

            $samplesdata[$sampleid]['course'] = $course->get_course_data();
            $samplesdata[$sampleid]['context'] = $course->get_context();
            $samplesdata[$sampleid]['user'] = $user;

            // Fill the cache.
            $this->samplecourses[$sampleid] = $course->get_id();
        }

        $enrolids = array_keys($samplesdata);
        return array(array_combine($enrolids, $enrolids), $samplesdata);
    }

}
