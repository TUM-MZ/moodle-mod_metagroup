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
 * Adds instance form
 *
 * @package   enrol_metagroup
 * @copyright 2021 Berengar W. Lehr {@link http://uni-jena.de}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once "$CFG->libdir/externallib.php";

/**
 * Interface to handle adding/removing meta enrollement
 */
class metagroup_enrollment extends external_api
{


    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function add_metagroup_parameters() {
        return new external_function_parameters(
            [
                'enrollments' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'from_group' => new external_value(PARAM_INT, 'group id of group with members to add from'),
                            'to_course'  => new external_value(PARAM_INT, 'course id of course to add members to'),
                        ]
                    )
                ),
            ]
        );
    }


    /**
     * Adds mutiple meta enrollments
     *
     * @param  $enrollments List of enrollments as input to add_single_metagroup
     * @return string List of return values of add_single_metagroup
     * @throws Exception As in add_single_metagroup
     */
    public static function add_metagroup($enrollments) {
        $return_values = [];
        foreach ($enrollments as $enrollment) {
            $return_values[] = self::add_single_metagroup(
                $enrollment['from_group'],
                $enrollment['to_course']
            );
        }
        return $return_values;
    }


    /**
     * Adds meta enrollment of group to course
     *
     * @param  $fromGroup int    Group id of group with members to add from
     * @param  $toCourse  int    Course id of course to add members to
     * @return array Echos transmitted given parameters with additional target course id
     * @throws Exception If error occurs
     */
    public static function add_single_metagroup($fromGroup, $toCourse) {
        global $DB;

        $group = $DB->get_record('groups', ['id' => $fromGroup], 'courseid');
        if (!$group) {
            throw new Exception("Can not find group $fromGroup");
        }

        $enrol = enrol_get_plugin('metagroup');
        if (!$enrol->get_newinstance_link($toCourse)) {
            throw new Exception('Can not get new instance link');
        }

        $parameter = [
            'from' => [
                'course' => intval($group->courseid),
                'group'  => $fromGroup,
            ],
            'to'   => $toCourse,
        ];

        $enrol->add_instance(
            get_course($parameter['to']),
            [
                'customint1' => $parameter['from']['course'],
                'customint2' => $parameter['from']['group'],
            ]
        );

        return $parameter;
    }


    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function add_metagroup_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'from' => new external_single_structure(
                        [
                            'course' => new external_value(PARAM_INT, 'ID of course inside whitch group exists'),
                            'group'  => new external_value(PARAM_INT, 'ID of group of members to add'),
                        ]
                    ),
                    'to'   => new external_value(PARAM_INT, 'ID of course members are added to'),
                ]
            )
        );
    }
}
