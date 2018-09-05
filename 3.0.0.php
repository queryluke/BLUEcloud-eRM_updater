#!/usr/local/bin/php
<?php

require_once __DIR__.'/config.php';
require_once __DIR__.'/helpers.php';

$from_version = '2.0.0';
$to_version = '3.0.0';

$modules = ['auth','licensing','management','organizations','resources','usage'];

echo "Starting update\n";
echo "Updating Coral source code\n";

// Clean up git
chdir($coral_path);
// remove any untracked files
exec('git clean -df');
// remove any changes
exec('git checkout .');
// get the latest repo changes
exec('git checkout master');
exec('git fetch');
exec('git pull origin master');

echo "Coral source code updated\n";

echo "Creating core configuration file\n";
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
echo "Core configuration file created\n";

// Reports Hotfix
foreach ([['/reports/admin/classes/domain/ParameterFactory.php', 17], ['/reports/admin/classes/report/Report.php', 39]] as $file) {
    $filename = $coral_path . $file[0];
    $lines = file( $filename , FILE_IGNORE_NEW_LINES );
    $lines[$file[1]] = $lines[$file[1]].'->selectDB(Config::$database->name)';
    file_put_contents( $filename , implode( "\n", $lines ) );
}

// EbscoKB title Search hotfix

$filename = $coral_path . '/resources/ajax_htmldata/getEbscoKbTitleDetails.php';
$lines = file( $filename , FILE_IGNORE_NEW_LINES );
$lines[95] = str_replace('echo_','echo _', $lines[95]);
file_put_contents( $filename , implode( "\n", $lines ) );


foreach($modules as $m) {
    $mod_name = ucfirst($m);
    echo "Starting $mod_name updates\n";
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
    echo "$mod_name updates complete\n";
}

// 2.0.0 DB hotfix
$config = get_ini_file('resources');
$mysql_user = $update_user;
$mysql_pass = $update_pass;
$mysql_host = $config['database']['host'];
$sql_file = __DIR__.'/2.0.0_update.sql';
$command = "mysql -u{$mysql_user} -p{$mysql_pass} "
            . "-h {$mysql_host} < {$sql_file}";
shell_exec($command);

echo "Coral successfully updated\n";


// Module specific updates
function update_resources(){
    $fp = ini_file('resources');
    $config = get_ini_file('resources');
    if (empty($config["settings"])) {
        $config["settings"] = [];
    }
    // Populate the variable with a value
    // Warning: do not set $conf_data["general"] = ["random" => "something"] or you will lose other variables. Rather:
    $config["settings"]["ebscoKbEnabled"] = "N";
    $config["settings"]["ebscoKbCustomerId"] = "";
    $config["settings"]["ebscoKbApiKey"] = "";
    write_php_ini($fp, $config);
}

// Module specific updates
function update_usage(){
    $fp = ini_file('usage');
    $config = get_ini_file('usage');
    if (empty($config["settings"])){
        $config["settings"] = [];
    }
    // Populate the variable with a value
    // Warning: do not set $conf_data["general"] = ["random" => "something"] or you will lose other variables. Rather:
    $config["settings"]["reportsModule"] = "Y";
    write_php_ini($fp, $config);
}
