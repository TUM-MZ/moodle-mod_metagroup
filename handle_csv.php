<?php
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once($CFG->libdir . '/grouplib.php');
require_once('./csv_form.php');
require_once('./locallib.php');

admin_externalpage_setup('handlecsv');
echo $OUTPUT->header();
$mform = new csv_form();
if ($form_data = $mform->get_data()) {
    global $DB;
    $course_list = array();
    $enrol = enrol_get_plugin('metagroup');
    $import_id = csv_import_reader::get_new_iid('handlecsv');
    $cir = new csv_import_reader($import_id, 'handlecsv');
    $content = $mform->get_file_content('csvfile');     //    echo $content;

    $read_count = $cir->load_csv_content($content, 'UTF-8', 'comma');

//    check empty or invalid csv file
    if ($read_count === false) {
        print_error('csvfileerror', 'enrol_metagroup', $returnurl, $cir->get_error());
    } else if ($read_count == 0) {
        print_error('csvemptyfile', 'error', $returnurl, $cir->get_error());
    }

    $cir->init();
    while ($pieces = $cir->next()) {
        if (count($pieces) != 3){
            print_error('csvfileerror', 'enrol_metagroup', $returnurl, $cir->get_error());
            continue;
        }
        // get course object and parent course object
        $course = get_course_from_shortname(trim($pieces[0]));
        $parent_course = get_course_from_shortname(trim($pieces[1]));

        // if none of the objects are missing
        if($course and $parent_course){
            $parent_course_group = groups_get_group_by_name($parent_course->id, trim($pieces[2]));
            if ($parent_course_group){
                if(!enrolment_exists($course, $parent_course->id, $parent_course_group)){
                    $course_list[] = $course->id;       // add course id in list, for sync call
                    $enrol->add_instance($course, array('customint1'=>$parent_course->id, 'customint2'=>$parent_course_group));
                    echo "Enrolment successful for '".implode($pieces, ','). "' <br>";
                }
                else{
                    echo "Enrolment already exists for '".implode($pieces, ','). "' <br>";
                }
            }
            else{
                echo "Enrolment failed for '". implode($pieces, ',')."'. Invalid group <br>";
            }
        }
        else{
            echo "Enrolment failed for '". implode($pieces, ',')."'. Invalid course <br>";
        }
    }
//    for unique course list, call metagroup sync
    $course_list = array_unique($course_list);
    foreach ($course_list as $cid){
        enrol_metagroup_sync($cid);
    }


    unset($content);
}

$mform->display();
echo $OUTPUT->footer();
die();

