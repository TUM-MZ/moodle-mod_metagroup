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
 * @package    enrol_metagroup
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once("$CFG->dirroot/enrol/metagroup/addinstance_form.php");
require_once("$CFG->dirroot/enrol/metagroup/locallib.php");
//print_r($_POST);
$id = required_param('id', PARAM_INT); // course id

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);
$PAGE->set_url('/enrol/metagroup/addinstance.php', array('id'=>$course->id));
$PAGE->set_pagelayout('admin');
$PAGE->requires->js_call_amd('enrol_metagroup/metalink', 'initialize');

navigation_node::override_active_url(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));

require_login($course);
require_capability('moodle/course:enrolconfig', $context);

$enrol = enrol_get_plugin('metagroup');
if (!$enrol->get_newinstance_link($course->id)) {
    redirect(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));
}

//new form handling
if($_POST){
    if(isset($_POST["groups"]))
    {
        $linked_course_id = $_POST["link"];
        if ($_POST["groups"] > 0) {
            $linked_group_id = $_POST["groups"];
        } else {
            $linked_group_id = 0;
        }

        if($_POST["submit"] == "Cancel") {
            redirect(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));
        }
        $coursecontext = context_course::instance($linked_course_id);
        if (!$course->visible and !has_capability('moodle/course:viewhiddencourses', $coursecontext)) {
            // can't add a hidden course
            redirect(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));
        } else if (!has_capability('enrol/metagroup:selectaslinked', $coursecontext)) {
            // need rights
            redirect(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));
        }

        else if($_POST["groups"] > 0) {
            $existing = $DB->get_records('enrol', array('enrol'=>'metagroup', 'courseid' => $course-> id, 'customint1'=>$linked_course_id, 'customint2' => $linked_group_id), '', 'customint1, customint2, id');
            if ($course->id == SITEID or $linked_course_id == $course->id or !empty($existing)) {
                // don't add the same group twice
                redirect(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));
            }
            $eid = $enrol->add_instance($course, array('customint1'=>$linked_course_id, 'customint2'=>$linked_group_id));
        }
        else {
            $existing = $DB->get_records('enrol', array('enrol'=>'metagroup', 'courseid' => $course->id, 'customint1'=>$linked_course_id, 'customint2' => null), '', 'customint1, customint2, id');
            if ($course->id == SITEID or $linked_course_id == $course->id or !empty($existing)) {
                // don't add the same course twice
                redirect(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));
            }
            $eid = $enrol->add_instance($course, array('customint1'=>$_POST["link"]));
        }
        enrol_metagroup_sync($course->id);
        redirect(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));
    }


}
/*
$mform = new enrol_metagroup_addinstance_form(NULL, $course);

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));

} else if ($data = $mform->get_data()) {
    print_r($data);
    if ($data->groups > 0)
//        $eid = $enrol->add_instance($course, array('customint1'=>$data->link));
        $eid = $enrol->add_instance($course, array('customint1'=>$data->link, 'customint2'=>$data->groups));
    else
        $eid = $enrol->add_instance($course, array('customint1'=>$data->link));
    //enrol_metagroup_sync($course->id);
    redirect(new moodle_url('/enrol/instances.php', array('id'=>$course->id)));
}
*/
$PAGE->set_heading($course->fullname);
$PAGE->set_title(get_string('pluginname', 'enrol_metagroup'));


//new variables to show the form here manually
$courses = array('' => get_string('choosedots'));
$select = ', ' . context_helper::get_preload_record_columns_sql(' ctx');
$join = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
$sql = "SELECT c.id, c.fullname, c.shortname, c.visible $select FROM {course} c $join ORDER BY c.sortorder ASC";
$rs = $DB->get_recordset_sql($sql, array('contextlevel' => CONTEXT_COURSE));
foreach ($rs as $c) {
    if ($c->id == SITEID or $c->id == $course->id) {
        continue;
    }
    context_helper::preload_from_record($c);
    $coursecontext = context_course::instance($c->id);
    if (!$c->visible and !has_capability('moodle/course:viewhiddencourses', $coursecontext)) {
        continue;
    }
    if (!has_capability('enrol/metagroup:selectaslinked', $coursecontext)) {
        continue;
    }
    $courses[$c->id] = $c->fullname . ' (' . $c->shortname . ')';
}
$rs->close();

echo $OUTPUT->header();
echo '<form id="selectionform" action="addinstance.php" method="post">'."\n";
echo '<div>';
echo '<label for="course_filter">Search</label>';
echo '<input id="course_filter" type="text">';
echo '</div>';
echo '<div>'."\n";
echo '<label for="id_link">'.
    get_string('linkedcourse', 'enrol_metagroup').'</label>';
echo '<select id="id_link" name="link">';
foreach($courses as $key => $value) {
    echo '<option value="'. $key.'" title="'. $value.'">'.$value.'</option>';
}
echo '</select>';
echo '</div>';
echo '<div>';
echo '<label for="id_groups">Group</label>';
echo '<select id="id_groups" name="groups"></select>';
echo '<p><input type="hidden" name="id" value="'.$course->id.'"></p>';
echo '<p><input type="submit" name ="submit" value="Enroll"/>'."\n";
echo '<input type="submit" name ="submit" value="Cancel"/></p>'."\n";
echo '</div>'."\n";
echo '</form>'."\n";
//$mform->display();

echo $OUTPUT->footer();
