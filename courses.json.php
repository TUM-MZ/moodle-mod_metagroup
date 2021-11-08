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
 * Adds new instance of enrol_metagroup to specified course.
 *
 * @package   enrol_metagroup
 * @copyright 2021 Berengar W. Lehr {@link https://www.uni-jena.de/mmz}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once '../../config.php';
require_once $CFG->libdir . '/datalib.php';

const METAGROUP_FIELD_NAME  = 0;
const METAGROUP_FIELD_ID    = 1;
const METAGROUP_MAX_COURSES = 20;

global $DB;

$searchFieldId = optional_param('field', METAGROUP_FIELD_NAME, PARAM_INT);

if ($searchFieldId === METAGROUP_FIELD_ID) {
    $searchField = 'idnumber';
    $searchValue = required_param('search', PARAM_INT);
} else {
    $searchField = 'fullname';
    $searchValue = optional_param('search', '', PARAM_TEXT);
}

$courses = array_map(
    function ($course) {
        return [
            'id'       => $course->id,
            'idnumber' => $course->idnumber,
            'name'     => $course->fullname,
            'visible'  => $course->visible,
        ];
    },
    $DB->get_records_sql(
        "SELECT id, idnumber, fullname, visible FROM {course} WHERE $searchField LIKE '%${searchValue}%'",
        null,
        0,
        METAGROUP_MAX_COURSES
    )
);

echo json_encode(array_values($courses));
die();
