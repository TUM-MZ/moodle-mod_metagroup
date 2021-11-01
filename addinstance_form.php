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

defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir . '/formslib.php';
require_once $CFG->libdir . '/datalib.php';

class enrol_metagroup_addinstance_form extends moodleform
{


    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('pluginname', 'enrol_metagroup'));

        $mform->addGroup(
            [
                $mform->createElement('radio', 'field', '', get_string('form:field_name'), 0, $attributes),
                $mform->createElement('radio', 'field', '', get_string('form:field_id'), 1, $attributes),
            ],
            'field_types',
            '',
            [' '],
            false
        );
        $mform->setDefault('field', 1);

        $mform->addElement('select', 'link', get_string('linkedcourse', 'enrol_metagroup'), ['loading'], 'disabled');
        $mform->setType('link', PARAM_INT);
        $mform->addRule('link', null, 'required', null, 'server');

        $mform->addElement('select', 'groups', get_string('form:group'), ['N/A'], 'disabled');
        $mform->setType('groups', PARAM_INT);
        $mform->addRule('groups', null, 'required', null, 'server');

        $mform->addElement('hidden', 'id', null);
        $mform->setType('id', PARAM_INT);
        $mform->addRule('id', null, 'required', null, 'server');

        $this->add_action_buttons(true, get_string('addinstance', 'enrol'));
    }


    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $course = get_course($data['link']);

        if (!$course) {
            $errors['link'] = get_string('required');
            return $errors;
        }

        $coursecontext = context_course::instance($course->id);
        if (!(($course->visible) || has_capability('moodle/course:viewhiddencourses', $coursecontext))) {
            $errors['link'] = get_string('error');
            return $errors;
        }

        if (!has_capability('enrol/metagroup:selectaslinked', $coursecontext)) {
            $errors['link'] = get_string('error');
            return $errors;
        }

        $existing = $DB->get_records('enrol', ['enrol' => 'metagroup', 'courseid' => $this->course->id], '', 'customint1, customint2, id');
        if (
            ($course->id == SITEID) ||
            ($course->id == $this->course->id) ||
            (array_key_exists($course->id, $existing) && ($existing[$course->id]['customint2'] == $data['groups']))
        ) {
            $errors['link'] = get_string('error');
            return $errors;
        }

        return $errors;
    }
}
