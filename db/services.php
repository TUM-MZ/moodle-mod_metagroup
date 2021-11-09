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

$functions = [
    'enrol_metagroup_add' => [
        'classname'    => 'metagroup_enrollment',
        'methodname'   => 'add_metagroup',
        'classpath'    => 'enrol/metagroup/classes/external/enrollment.php',
        'description'  => 'Adds meta enrollment: Members of a group inside a course are added as members to another course',
        'type'         => 'write',
        'ajax'         => true,
        'capabilities' => 'moodle/course:enrolconfig enrol/metagroup',
    ],
];
