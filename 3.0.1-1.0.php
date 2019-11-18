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


// TODO: Might need an arg for date format
// Create the common config file
$fp = "$coral_path/common/configuration.ini";
fopen($fp, "w");
$config = parse_ini_file($fp, true);
$config['installation_details']['version'] = $to_version;
$config['settings']['datepicker_date_format'] = "dd/mm/yyyy";
write_php_ini($fp, $config);

$common_fp = ini_file('common');
$common_config = get_ini_file('common');
echo "Core configuration file created\n";


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
    
    echo "$mod_name updates complete\n";
}

// update version in footer
update_footer_version('DEVELOPMENT', '3.0.1 (SirsiDynix 1.0)');

echo "Coral successfully updated\n";

