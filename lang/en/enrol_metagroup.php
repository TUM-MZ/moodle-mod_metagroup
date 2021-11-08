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
 * Strings for component 'enrol_metagroup', language 'en'.
 *
 * @package   enrol_metagroup
 * @copyright 2010 onwards Petr Skoda {@link http://skodak.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['linkedcourse']             = 'Link course';
$string['metagroup:config']         = 'Configure metagroup enrol instances';
$string['metagroup:selectaslinked'] = 'Select course as metagroup linked';
$string['metagroup:unenrol']        = 'Unenrol suspended users';
$string['nosyncroleids']            = 'Roles that are not synchronised';
$string['nosyncroleids_desc']       = 'By default all course level role assignments are synchronised from parent to child courses. Roles ' .
    'that are selected here will not be included in the synchronisation process. The roles available for synchronisation will be updated ' .
    'in the next cron execution.';
$string['pluginname']               = 'Course metagroup link';
$string['pluginname_desc']          = 'Course metagroup link enrolment plugin synchronises enrolments and roles in two different courses.';
$string['syncall']                  = 'Synchronise all enrolled users';
$string['syncall_desc']             = 'If enabled all enrolled users are synchronised even if they have no role in parent course, if ' .
    'disabled only users that have at least one synchronised role are enrolled in child course.';
$string['handlecsv']                = 'Import from csv';
$string['csvfile']                  = 'CSV file';
$string['import']                   = 'Import';
$string['next']         = 'Next';
$string['csvfileerror'] = 'Invalid csv file';
$string['linkedgroup']  = 'Link group';
$string['remarks']      = 'Remarks';
$string['course']       = 'Course';

$string['form:field_id']   = 'Course ID';
$string['form:field_name'] = 'Course name';
$string['form:searchterm'] = 'Course search';
$string['form:group']      = 'Course group';

$string['select:no_courses'] = 'No course found';
$string['select:no_groups']  = 'No groups found';

$string['format_error']      = 'Format error. 3 items per row';
$string['course_error']      = 'Invalid course';
$string['link_course_error'] = 'Invalid link course';
$string['link_group_error']  = 'Invalid link group';
$string['existing_error']    = 'Enrolment already exists';
$string['eligibile']         = 'Enrolment will be added';
