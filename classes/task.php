<?php
namespace enrol_metagroup;
defined('MOODLE_INTERNAL') || die();

class task extends \core\task\scheduled_task
{
    public function get_name() {
        return get_string("pluginname","enrol_metagroup");
    }

    public function execute() {
        global $CFG;

        require_once("$CFG->dirroot/enrol/metagroup/locallib.php");
        enrol_metagroup_sync();
        mtrace("Sync enrol metagroup link");
    }
}