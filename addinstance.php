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

require '../../config.php';

require_once $CFG->dirroot . '/enrol/metagroup/addinstance_form.php';
require_once $CFG->dirroot . '/enrol/metagroup/locallib.php';
require_once $CFG->libdir . '/formslib.php';



$course             = get_course(required_param('id', PARAM_INT));
$to_enrol_instances = new moodle_url('/enrol/instances.php', ['id' => $course->id]);
$context            = context_course::instance($course->id, MUST_EXIST);
$PAGE->set_url('/enrol/metagroup/addinstance.php', ['id' => $course->id]);
$PAGE->set_pagelayout('admin');
$PAGE->requires->js_call_amd(
    'enrol_metagroup/metalink',
    'init',
    [
        'lang' => [
            'no_courses' => get_string('select:no_courses', 'enrol_metagroup'),
            'no_groups'  => get_string('select:no_groups', 'enrol_metagroup'),
        ],
    ]
);

navigation_node::override_active_url($to_enrol_instances);

require_login($course);
require_capability('moodle/course:enrolconfig', $context);

$enrol = enrol_get_plugin('metagroup');
if (!$enrol->get_newinstance_link($course->id)) {
    redirect($to_enrol_instances);
}

$mform = new enrol_metagroup_addinstance_form(null, ['id' => $course->id]);

if ($mform->is_cancelled()) {
    redirect($to_enrol_instances);
} else {
    $data = $mform->get_data();
    if ($data && ($data->group > 0)) {
        $eid = $enrol->add_instance(
            $course,
            [
                'customint1' => $data->link,
                'customint2' => $data->group,
            ]
        );
        redirect($to_enrol_instances);
    }
}

echo $OUTPUT->header();
$mform->display();

if (groups_get_course_groupmode($course) == SEPARATEGROUPS &&
    !has_capability('moodle/site:accessallgroups', $context)) {
        echo("Nothing to see");
        var_dump(groups_get_all_groups($course));
        echo("Do you want to split the course?");
    }
}

echo $OUTPUT->footer();
