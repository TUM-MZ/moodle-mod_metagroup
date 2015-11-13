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
 * Meta group enrolment plugin settings and presets.
 *
 * @package    enrol_metagroup
 * @author     Petr Skoda
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$newsettings = $settings;

if ($hassiteconfig) {
    $ADMIN->add('enrolments', new admin_category('enrol_metagroup', new lang_string('pluginname', 'enrol_metagroup')));
    $ADMIN->add('enrol_metagroup', $newsettings);
    $ADMIN->add('enrol_metagroup', new admin_externalpage('handlecsv', get_string('handlecsv', 'enrol_metagroup'), $CFG->wwwroot . '/enrol/metagroup/handle_csv.php'));
}


$settings = null;

if ($ADMIN->fulltree) {

    //--- general settings -----------------------------------------------------------------------------------
    $newsettings->add(new admin_setting_heading('enrol_metagroup_settings', '', get_string('pluginname_desc', 'enrol_metagroup')));
    if (!during_initial_install()) {
        $allroles = role_fix_names(get_all_roles(), null, ROLENAME_ORIGINALANDSHORT, true);
        $newsettings->add(new admin_setting_configmultiselect('enrol_metagroup/nosyncroleids', get_string('nosyncroleids', 'enrol_metagroup'), get_string('nosyncroleids_desc', 'enrol_metagroup'), array(), $allroles));

        $newsettings->add(new admin_setting_configcheckbox('enrol_metagroup/syncall', get_string('syncall', 'enrol_metagroup'), get_string('syncall_desc', 'enrol_metagroup'), 1));

        $options = array(
            ENROL_EXT_REMOVED_UNENROL        => get_string('extremovedunenrol', 'core_enrol'),
            ENROL_EXT_REMOVED_SUSPEND        => get_string('extremovedsuspend', 'core_enrol'),
            ENROL_EXT_REMOVED_SUSPENDNOROLES => get_string('extremovedsuspendnoroles', 'core_enrol'),
        );
        $newsettings->add(new admin_setting_configselect('enrol_metagroup/unenrolaction', get_string('extremovedaction', 'enrol'), get_string('extremovedaction_help', 'enrol'), ENROL_EXT_REMOVED_SUSPENDNOROLES, $options));
    }
}
