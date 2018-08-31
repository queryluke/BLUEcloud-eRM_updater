#!/usr/local/bin/php
<?php
require_once __DIR__.'/config.php';
require_once __DIR__.'/helpers.php';


$modules = ['auth','licensing','management','organizations','resources','usage'];

echo "Starting update\n";
// Clean up git
chdir($coral_path);
// remove any untracked files
exec('git clean -df');
// remove any changes
exec('git checkout .');
// get the latest repo changes
exec('git checkout master');
exec('git pull origin master');

foreach($modules as $m) {
    $mod_name = ucfirst($mod_name);
    echo "Starting $mod_name updates\n";
    if($m !== 'auth') {
        replace_string_in_file("$coral_path/$m/templates/header.php",$coral_docs_url,$sirsi_docs_url);
    }
    echo "$mod_name updates complete\n";
}

// These can be removed once coral master branch is updated
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

echo "Coral source code updated\n";
