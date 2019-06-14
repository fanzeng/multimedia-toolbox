<?php
$document_root = $_SERVER['DOCUMENT_ROOT'];
if ($document_root === "/var/www/html/multimedia_toolbox/") { // If this is on local machine.
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    $_SESSION["local_machine"] = True;
    $local_machine = True;
}

function log_message($path, $msg, $postfix) {
    $date = date_create("now", timezone_open('Australia/Brisbane'));
    $basename = date_format($date, 'y-m-d') . '-' . $postfix . '.log';
    $log_file_name = $path . $basename;
    if (!is_dir($path)) {
        mkdir($path, 0700, true);
    }
    if (!file_exists($log_file_name)) {
        fclose(fopen($log_file_name, "w"));
        chmod($log_file_name, 0740);
    }
    $file_handle = fopen($log_file_name, "a");
    $file_contents = date_format($date, 'r') . ': ' . $msg . "\n";
    fwrite($file_handle, $file_contents);
    fclose($file_handle);
}
?>