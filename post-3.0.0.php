#!/usr/local/bin/php
<?php

require_once __DIR__.'/config.php';
require_once __DIR__.'/helpers.php';

echo "Starting post update\n";

// Reports Hotfix
foreach ([['/reports/admin/classes/domain/ParameterFactory.php', 17], ['/reports/admin/classes/report/Report.php', 39]] as $file) {
    $filename = $coral_path . $file[0];
    $lines = file( $filename , FILE_IGNORE_NEW_LINES );
    if(strpos($lines[$file[1]], '->selectDB(Config::$database->name)') === false){
      $lines[$file[1]] = $lines[$file[1]].'->selectDB(Config::$database->name)';
    }
    file_put_contents( $filename , implode( "\n", $lines ) );
}

// EbscoKB title Search hotfix

$filename = $coral_path . '/resources/ajax_htmldata/getEbscoKbTitleDetails.php';
$lines = file( $filename , FILE_IGNORE_NEW_LINES );
$lines[95] = str_replace('echo_','echo _', $lines[95]);
file_put_contents( $filename , implode( "\n", $lines ) );

// 2.0.0 DB hotfix
$config = get_ini_file('resources');
$mysql_user = $update_user;
$mysql_pass = $update_pass;
$mysql_host = $config['database']['host'];
$sql_file = __DIR__.'/2.0.0_update.sql';
$command = "mysql -u{$mysql_user} -p{$mysql_pass} "
            . "-h {$mysql_host} < {$sql_file}";
shell_exec($command);

echo "Coral post update run successfully\n";
