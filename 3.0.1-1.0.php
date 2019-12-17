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
// remove coral-erm repo as origin
exec('git remote remove origin');
// add sirsi repo as origin
exec('git remote add origin https://github.com/Sirsidynix/coral.git');
// update code
exec('git fetch');
exec('git pull origin master');

echo "Coral source code updated\n";

echo "Creating core configuration file\n";


// Update the common config file
$fp = ini_file('common');
$config = get_ini_file('common');
$config['installation_details']['version'] = $to_version;
$config['settings']['environment'] = 'prod';
$config['settings']['date_format'] = '%m/%d/%Y';
$config['settings']['datepicker_date_format'] = "mm/dd/yyyy";
write_php_ini($fp, $config);

echo "Core configuration file created\n";


foreach($modules as $m) {
    $mod_name = ucfirst($m);
    echo "Starting $mod_name updates\n";
    if ($config[$m]['installed'] !== 'Y') {
        continue;
    }

    process_sql_files($m);
    $additional_updates = "update_$m";

    if (function_exists($additional_updates)){
        $additional_updates();
    }
    
    echo "$mod_name updates complete\n";
}

// update version in footer
update_footer_version('DEVELOPMENT', '3.0.1 (SirsiDynix 1.0)');
update_footer_version('3.0.0', '3.0.1 (SirsiDynix 1.0)');

echo "Coral successfully updated\n";

