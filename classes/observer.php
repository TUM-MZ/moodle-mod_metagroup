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
 * Event observer for metagroup enrolment plugin.
 *
 * @package    enrol_metagroup
 * @copyright  2013 Rajesh Taneja <rajesh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/enrol/metagroup/locallib.php');

/**
 * Event observer for enrol_metagroup.
 *
 * @package    enrol_metagroup
 * @copyright  2013 Rajesh Taneja <rajesh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrol_metagroup_observer extends enrol_metagroup_handler {

    /**
     * Triggered via user_enrolment_created event.
     *
     * @param \core\event\user_enrolment_created $event
     * @return bool true on success.
     */
    public static function user_enrolment_created(\core\event\user_enrolment_created $event) {
        if (!enrol_is_enabled('metagroup')) {
            // No more enrolments for disabled plugins.
            return true;
        }

        if ($event->other['enrol'] === 'metagroup') {
            // Prevent circular dependencies - we can not sync metagroup enrolments recursively.
            return true;
        }

        self::sync_course_instances($event->courseid, $event->relateduserid);
        return true;
    }

    /**
     * Triggered via user_enrolment_deleted event.
     *
     * @param \core\event\user_enrolment_deleted $event
     * @return bool true on success.
     */
    public static function user_enrolment_deleted(\core\event\user_enrolment_deleted $event) {
        return true;
        if (!enrol_is_enabled('metagroup')) {
            // This is slow, let enrol_metagroup_sync() deal with disabled plugin.
            return true;
        }

        if ($event->other['enrol'] === 'metagroup') {
            // Prevent circular dependencies - we can not sync metagroup enrolments recursively.
            return true;
        }
        debugging("User deleted");

        self::sync_course_instances($event->courseid, $event->relateduserid);

        return true;
    }

    /**
     * Triggered via user_enrolment_updated event.
     *
     * @param \core\event\user_enrolment_updated $event
     * @return bool true on success
     */
    public static function user_enrolment_updated(\core\event\user_enrolment_updated $event) {
        if (!enrol_is_enabled('metagroup')) {
            // No modifications if plugin disabled.
            return true;
        }

        if ($event->other['enrol'] === 'metagroup') {
            // Prevent circular dependencies - we can not sync metagroup enrolments recursively.
            return true;
        }

        self::sync_course_instances($event->courseid, $event->relateduserid);

        return true;
    }
    /**
    * Triggered via group_member_added event
    * 
    * @param \core\event\group_member_added $event
    * @return bool true on success
    */
    public static function group_member_added(\core\event\group_member_added $event) {
        if (!enrol_is_enabled('metagroup')) {
            // No more enrolments for disabled plugins.
            return true;
        }

        self::sync_course_instances($event->courseid, $event->relateduserid, $event->objectid);
        return true;
    }
    /**
    * Triggered via group_member_removed event
    * 
    * @param \core\event\group_member_removed $event
    * @return bool true on success
    */
    public static function group_member_removed(\core\event\group_member_removed $event) {
        if (!enrol_is_enabled('metagroup')) {
            // No more enrolments for disabled plugins.
            return true;
        }

        debugging(var_dump($event));

        self::sync_course_instances($event->courseid, $event->relateduserid, $event->objectid);
        return true;
    }
    /**
     * Triggered via role_assigned event.
     *
     * @param \core\event\role_assigned $event
     * @return bool true on success.
     */
    public static function role_assigned(\core\event\role_assigned $event) {
        if (!enrol_is_enabled('metagroup')) {
            return true;
        }

        // Prevent circular dependencies - we can not sync metagroup roles recursively.
        if ($event->other['component'] === 'enrol_metagroup') {
            return true;
        }

        // Only course level roles are interesting.
        if (!$parentcontext = context::instance_by_id($event->contextid, IGNORE_MISSING)) {
            return true;
        }
        if ($parentcontext->contextlevel != CONTEXT_COURSE) {
            return true;
        }

        self::sync_course_instances($parentcontext->instanceid, $event->relateduserid);

        return true;
    }

    /**
     * Triggered via role_unassigned event.
     *
     * @param \core\event\role_unassigned $event
     * @return bool true on success
     */
    public static function role_unassigned(\core\event\role_unassigned $event) {
        if (!enrol_is_enabled('metagroup')) {
            // All roles are removed via cron automatically.
            return true;
        }

        // Prevent circular dependencies - we can not sync metagroup roles recursively.
        if ($event->other['component'] === 'enrol_metagroup') {
            return true;
        }

        // Only course level roles are interesting.
        if (!$parentcontext = context::instance_by_id($event->contextid, IGNORE_MISSING)) {
            return true;
        }
        if ($parentcontext->contextlevel != CONTEXT_COURSE) {
            return true;
        }

        self::sync_course_instances($parentcontext->instanceid, $event->relateduserid);

        return true;
    }

    /**
     * Triggered via course_deleted event.
     *
     * @param \core\event\course_deleted $event
     * @return bool true on success
     */
    public static function course_deleted(\core\event\course_deleted $event) {
        global $DB;

        if (!enrol_is_enabled('metagroup')) {
            // This is slow, let enrol_metagroup_sync() deal with disabled plugin.
            return true;
        }

        // Does anything want to sync with this parent?
        if (!$enrols = $DB->get_records('enrol', array('customint1' => $event->objectid, 'enrol' => 'metagroup'),
                'courseid ASC, id ASC')) {
            return true;
        }

        $plugin = enrol_get_plugin('metagroup');
        $unenrolaction = $plugin->get_config('unenrolaction', ENROL_EXT_REMOVED_SUSPENDNOROLES);

        if ($unenrolaction == ENROL_EXT_REMOVED_UNENROL) {
            // Simple, just delete this instance which purges all enrolments,
            // admins were warned that this is risky setting!
            foreach ($enrols as $enrol) {
                $plugin->delete_instance($enrol);
            }
            return true;
        }

        foreach ($enrols as $enrol) {
            $enrol->customint = 0;
            $DB->update_record('enrol', $enrol);

            if ($unenrolaction == ENROL_EXT_REMOVED_SUSPEND or $unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                // This makes all enrolments suspended very quickly.
                $plugin->update_status($enrol, ENROL_INSTANCE_DISABLED);
            }
            if ($unenrolaction == ENROL_EXT_REMOVED_SUSPENDNOROLES) {
                $context = context_course::instance($enrol->courseid);
                role_unassign_all(array('contextid'=>$context->id, 'component'=>'enrol_metagroup', 'itemid'=>$enrol->id));
            }
        }

        return true;
    }
}
