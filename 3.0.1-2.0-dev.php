#!/usr/local/bin/php
<?php

require_once __DIR__.'/config.php';
require_once __DIR__.'/helpers.php';

$from_version = '3.0.0';
$to_version = '3.0.1';

$modules = ['auth','licensing','management','organizations','resources','usage'];

echo "Starting update\n";
echo "Updating Coral test code\n";

// Clean up git
clean_untracked_files();
chdir($coral_path);

// add queryluke repo as queryluke
exec('git remote add queryluke https://github.com/queryluke/coral.git');
// fetch query luke
exec('git fetch queryluke');
// change to queryluke branch
exec('git checkout queryluke/sirsi-usage-module-updates');

echo "Coral test code added\n";


foreach($modules as $m) {

    $mod_name = ucfirst($m);
    echo "Starting $mod_name updates\n";
    if ($config[$m]['installed'] !== 'Y') {
        continue;
    }

    if ($m == 'usage') {
        process_sql_file($m, '3.0.2', '001-000.sql');
    }
    echo "$mod_name updates complete\n";
}

// update version in footer
update_footer_version('3.0.1 (SirsiDynix 1.0)', '3.0.1 (SirsiDynix 2.0)');

echo "Coral successfully updated\n";

