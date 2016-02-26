<?php
require_once('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once($CFG->libdir . '/grouplib.php');
require_once('./csv_form.php');
require_once('./locallib.php');

admin_externalpage_setup('handlecsv');
echo $OUTPUT->header();

$from_cform = optional_param('cform', '', PARAM_INT);
$ddd= optional_param('ddd', '', PARAM_RAW);
$mform = new csv_form();

$returnurl = new moodle_url('/enrol/metagroup/handle_csv.php');

$enrol = enrol_get_plugin('metagroup');
$course_list = array();

if (empty($from_cform)) {
    $cform_data = array();

    if($form_data = $mform->get_data()) {    # form 2
        $import_id = csv_import_reader::get_new_iid('handlecsv');
        $cir = new csv_import_reader($import_id, 'handlecsv');
        $content = $mform->get_file_content('csvfile');

        $read_count = $cir->load_csv_content($content, 'UTF-8', 'comma');

//    check empty or invalid csv file
        if ($read_count === false) {
            print_error('csvfileerror', 'enrol_metagroup', $returnurl, $cir->get_error());
        } else if ($read_count == 0) {
            print_error('csvemptyfile', 'error', $returnurl, $cir->get_error());
        }

        $cir->init();

        while ($pieces = $cir->next()) {
            array_push($cform_data, array_map('trim',$pieces));
        }
        $cform = new confirm_form(null, array('data'=>$cform_data));
        $cform->display();

    }
    else{   # form #1
        $mform->display();
    }
}
else{
    if(!empty($ddd)){
        $eligibles = unserialize($ddd);
        foreach($eligibles as $row) {
            if(!enrolment_exists($row[0], $row[1], $row[2])) {
                $enrol->add_instance($row[0], array('customint1'=>$row[1], 'customint2'=>$row[2]));
                $course_list[] = $row[0]->id;
            }
        }
        echo count($course_list). " enrolments successful";
//    for unique course list, call metagroup sync
        $course_list = array_unique($course_list);
        foreach ($course_list as $cid){
            enrol_metagroup_sync($cid);
        }

        $mform->display();
    }

}
echo $OUTPUT->footer();
die();
