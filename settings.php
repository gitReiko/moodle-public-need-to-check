<?php

// Global check time
$name = 'block_need_to_check/check_time';
$title = get_string('time_for_check_work', 'block_need_to_check');
$description = get_string('time_for_check_work_desc', 'block_need_to_check');
$default = 518400; // 6 days (in timestamp)

$setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_INT);
$settings->add($setting);
