<?php

function update_version($to_version) {
    $fp = ini_file('common');
    $config = get_ini_file('common');
    $config['installation_details']['version'] = $to_version;
    write_php_ini($fp, $config);
}

function ini_file($module) {
    global $coral_path;
    return $module == 'common' ? "$coral_path/$module/configuration.ini" : "$coral_path/$module/admin/configuration.ini";
}

function get_ini_file($module) {
    return parse_ini_file(ini_file($module), true);
}

function write_php_ini($file, $array)
{
    $res = array();
    foreach($array as $key => $val)
    {
        if(is_array($val))
        {
            $res[] = "[$key]";
            foreach($val as $skey => $sval) $res[] = "$skey = ".(is_numeric($sval) ? $sval : '"'.addcslashes($sval, '"').'"');
        }
        else $res[] = "$key = ".(is_numeric($val) ? $val : '"'.addcslashes($val, '"').'"');
    }
    safefilerewrite($file, implode("\r\n", $res));
}

function safefilerewrite($fileName, $dataToSave)
{
    if (!is_writable($fileName)) {
        throw new Exception("$fileName is not writeable.");
    }

    if ($fp = fopen($fileName, 'w')) {
        $startTime = microtime(TRUE);

        do {
            $canWrite = flock($fp, LOCK_EX);
            // If lock not obtained sleep for 0 - 100 milliseconds, to avoid collision and CPU load
            if (!$canWrite) {
                usleep(round(rand(0, 100) * 1000));
            }
        } while ((!$canWrite) and ((microtime(TRUE) - $startTime) < 5));

        //file was locked so now we can store information
        if ($canWrite) {
            fwrite($fp, $dataToSave);
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }
}

function process_sql_files($module) {
    global $coral_path;
    global $mysql_pass;
    global $mysql_user;
    global $mysql_host;
    global $from_version;
    global $to_version;

    $config = get_ini_file($module);
    $db_name = $config['database']['name'];
    $sql_dir = "$coral_path/$module/install/protected";
    $sql_files_to_process = [];

    foreach(glob("$sql_dir/*", GLOB_ONLYDIR) as $dir) {
        $dir_version = str_replace("$sql_dir/",'', $dir);
        $dir_version_as_int = array_sum(explode('.', $dir_version));
        if (version_compare($dir_version, $from_version) > 0 && version_compare($dir_version, $to_version) <= 0 ){
            $version_sql_files = glob("$dir/*.sql");
            asort($version_sql_files);
            $sql_files_to_process[$dir_version_as_int] = $version_sql_files;
        }
    }

    ksort($sql_files_to_process);
    $final_sql_files = [];
    foreach($sql_files_to_process as $k => $v) {
        $final_sql_files = array_merge($final_sql_files,$v);
    }

    foreach ($final_sql_files as $sql_file)
    {
        $command = "mysql -u{$mysql_user} -p{$mysql_pass} "
            . "-h {$mysql_host} -D {$db_name} < {$sql_file}";

        shell_exec($command);
    }

}