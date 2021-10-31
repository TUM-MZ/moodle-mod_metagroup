<?php

define('AJAX_SCRIPT', true);

require_once '../../config.php';
require_once $CFG->libdir . '/datalib.php';

const METAGROUP_FIELD_NAME  = 0;
const METAGROUP_FIELD_ID    = 1;
const METAGROUP_MAX_COURSES = 20;

$search_field = optional_param('field', METAGROUP_FIELD_NAME, PARAM_INT);

if ($search_field === METAGROUP_FIELD_ID) {
    $search_value = required_param('value', PARAM_INT);
    try {
        $course  = get_course($search_value);
        $courses = [
            [
                'id'        => $course->id,
                'fullname'  => $course->fullname,
                'shortname' => $course->shortname,
                'visible'   => $course->visible,
            ],
        ];
    } catch (dml_missing_record_exception $e) {
        $courses = [];
    }
} else {
    $search_value = optional_param('value', '', PARAM_TEXT);
    $courses      = array_slice(
        array_filter(
            get_courses('all', 'c.sortorder ASC', 'c.id, c.fullname, c.shortname, c.visible'),
            function ($course) use ($search_value) {
                return (strlen($search_value) === 0) or (strpos($course->fullname, $search_value) !== false) or (strpos($course->shortname, $search_value) !== false);
            }
        ),
        0,
        METAGROUP_MAX_COURSES
    );
}

echo json_encode($courses);
die();
