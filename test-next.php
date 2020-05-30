#!/usr/local/bin/php
<?php

require_once __DIR__.'/config.php';
require_once __DIR__.'/helpers.php';

$from_version = '3.0.0';
$to_version = '3.0.1';

$modules = ['auth','licensing','management','organizations','resources','usage'];

echo "Starting update\n";
echo "Updating Coral source code\n";

// Clean up git
clean_untracked_files();
chdir($coral_path);
// update code
exec('git remote add queryluke https://github.com/queryluke/coral.git');
exec('git fetch queryluke');
exec('git checkout queryluke/sirsi-usage-module-updates');

echo "Coral source code updated\n";


$sql_file_to_process = "$coral_path/usage/install/protected/3.0.2/001-000.sql";
$command = "mysql -u{$update_user} -p{$update_pass} -h 127.0.0.1 -D coral_usage_prod < {$sql_file_to_process}";
shell_exec($command);

// update version in footer
update_footer_version('3.0.1 (SirsiDynix 1.0)', '3.0.1 (SirsiDynix 2.0)');

echo "Coral successfully updated\n";

