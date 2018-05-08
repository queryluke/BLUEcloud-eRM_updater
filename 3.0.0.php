#!/usr/local/bin/php
<?php

require_once __DIR__.'/config.php';
require_once __DIR__.'/helpers.php';

$from_version = '2.0.0';
$to_version = '3.0.0';

$modules = ['auth','licensing','management','organizations','resources','usage'];

// Clean up git
chdir($coral_path);
// remove any untracked files
exec('git clean -df');
// remove any changes
exec('git checkout .');
// get the latest repo changes
exec('git fetch');
exec('git checkout master');

// Create the common config file
$fp = "$coral_path/common/configuration_sample.ini";
$write_fp = "$coral_path/common/configuration.ini";
fopen($write_fp, "w");
$config = parse_ini_file($fp, true);
$resources_fp = "$coral_path/resources/admin/configuration.ini";
$resources_config = parse_ini_file($resources_fp, true);
$config['installation_details']['version'] = $to_version;
$config['database']['username'] = $resources_config['database']['username'];
$config['database']['password'] = $resources_config['database']['password'];
$config['database']['host'] = $resources_config['database']['host'];
write_php_ini($write_fp, $config);

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

    if($m !== 'auth') {
        replace_string_in_file("$coral_path/$m/templates/header.php",$coral_docs_url,$sirsi_docs_url);
    }
}


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