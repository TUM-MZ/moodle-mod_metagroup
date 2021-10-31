<?php

define('AJAX_SCRIPT', true);

require_once '../../config.php';
require_once $CFG->libdir . '/grouplib.php';

$courseid = required_param('courseid', PARAM_INT);
require_login($courseid);
$groups = groups_get_all_groups($courseid);
echo json_encode($groups);
exit;
