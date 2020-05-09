#!/usr/local/bin/php
<?php

require_once __DIR__.'/config.php';
require_once __DIR__.'/helpers.php';

$from_version = '3.0.1';
$to_version = '2020.02';

$modules = ['auth','licensing','management','organizations','resources','usage'];

echo "Starting update\n";
echo "Updating Coral source code\n";

// Clean up git
clean_untracked_files();
chdir($coral_path);
// update code
exec('git fetch');
exec('git pull origin master');

echo "Coral source code updated\n";


foreach($modules as $m) {
    $omissions = array(
        'usage' => array(
            // TODO: Update this to the correct SQL file name after usage updates are merged to coral
            '001-000.sql'
        ),
    );

    $mod_name = ucfirst($m);
    echo "Starting $mod_name updates\n";
    if ($config[$m]['installed'] !== 'Y') {
        continue;
    }

    process_sql_files($m, $omissions);
    $additional_updates = "update_$m";

    if (function_exists($additional_updates)){
        $additional_updates();
    }
    
    echo "$mod_name updates complete\n";
}

// update version in footer
update_footer_version('3.0.1 (SirsiDynix 1.0)', '3.0.1 (SirsiDynix 2.0)');

echo "Coral successfully updated\n";

