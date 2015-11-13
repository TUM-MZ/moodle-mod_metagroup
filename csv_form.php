<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class csv_form extends moodleform{
    function definition(){

        $mform  = $this->_form;
        $mform->addElement('filepicker', 'csvfile', get_string('csvfile', 'enrol_metagroup'),
            array('accepted_types' => '*.csv'));
        $mform->addRule('csvfile', null, 'required');
        $this->add_action_buttons(false, get_string('import', 'enrol_metagroup'));
    }
}