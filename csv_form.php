<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once('./locallib.php');
require_once($CFG->libdir . '/grouplib.php');

class csv_form extends moodleform{
    function definition(){

        $mform  = $this->_form;
        $mform->addElement('filepicker', 'csvfile', get_string('csvfile', 'enrol_metagroup'),
            array('accepted_types' => '*.csv'));
        $mform->addRule('csvfile', null, 'required');
        $this->add_action_buttons(false, get_string('next', 'enrol_metagroup'));
    }
}

class confirm_form extends moodleform{
    private $eligibles = array();
    function definition(){
        $mform = $this->_form;
        $data = $this->_customdata['data'];
        $mform->addElement('html', '<table class= "confirm_csv">');
        $mform->addElement('html', '<tr>
                                        <th>'.get_string('course', 'enrol_metagroup').'</th>
                                        <th>'.get_string('linkedcourse', 'enrol_metagroup').'</th>
                                        <th>'.get_string('linkedgroup', 'enrol_metagroup').'</th>
                                        <th>'.get_string('remarks', 'enrol_metagroup').'</th>
                                    </tr>');
        foreach($data as $row){
            $mform->addElement('html', '<tr>');
            foreach($row as $cell){
                $mform->addElement('html', '<td>'.$cell.'</td>');
            }
            $mform->addElement('html', '<td>'.$this->get_eligibility($row).'</td>');
            $mform->addElement('html', '</tr>');

        }
        $mform->addElement('html', '</table>');


        $mform->addElement('hidden', 'cform', '1');
        $mform->setType('cform', PARAM_INT);

//        var_dump($this->eligibles);
        $mform->addElement('hidden', 'ddd', serialize($this->eligibles));
        $mform->setType('ddd', PARAM_RAW);
//        $this->set_data($this->eligibles);
        $this->add_action_buttons(true, get_string('import', 'enrol_metagroup'));
//        $this->add_action_buttons(false, get_string('import', 'enrol_metagroup'));

    }


    /**
     * @param $row      row of csv
     * @return string   Eligibility or error.
     */
    function get_eligibility($row){
        if (count($row) != 3){
            return get_string('format_error', 'enrol_metagroup');
        }
        $course = get_course_from_shortname($row[0]);
        if($course){
            $link_course = get_course_from_shortname($row[1]);
            if($link_course){
                $link_group = groups_get_group_by_name($link_course->id, $row[2]);
                if($link_group){
                    if(enrolment_exists($course, $link_course->id, $link_group)){
                        return get_string('existing_error', 'enrol_metagroup');
                    }
                    else{
                        array_push($this->eligibles, array($course, $link_course->id, $link_group));
//                        array_push($this->eligibles, $row);
                        return get_string('eligibile', 'enrol_metagroup');
                    }
                }
                else{
                    return get_string('link_group_error', 'enrol_metagroup');
                }
            }
            else{
                return get_string('link_course_error', 'enrol_metagroup');
            }
        }
        else{
            return get_string('course_error', 'enrol_metagroup');
        }
    }
}