#!/usr/local/bin/php
<?php

include_once 'config.php';
include_once 'helpers.php';

$from_version = '2.0.0';
$to_version = '3.0.0';

$modules = ['auth','licensing','management','organizations','reports','resources','usage'];
$common_fp = ini_file('common');
$common_config = get_ini_file('common');

foreach($modules as $m) {
    if ($common_config[$m]['installed'] !== 'Y') {
        continue;
    }

    process_sql_files($m);
    $additional_updates = "update_$m";

    if (function_exists($additional_updates)){
        $additional_updates();
    }
}

update_version($to_version);


// Module specific updates
function update_resources(){
    $fp = ini_file('resources');
    $config = get_ini_file('resources');
    if (empty($config["settings"]))
        $config["settings"] = [];
    // Populate the variable with a value
    // Warning: do not set $conf_data["general"] = ["random" => "something"] or you will lose other variables. Rather:
    $config["settings"]["ebscoKbEnabled"] = "N";
    $config["settings"]["ebscoKbCustomerId"] = "";
    $config["settings"]["ebscoKbApiKey"] = "";
    write_php_ini($fp, $config);
}