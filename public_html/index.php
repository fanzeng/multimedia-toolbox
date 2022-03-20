<?php
	$document_root = $_SERVER['DOCUMENT_ROOT'];
	if ($document_root === "/var/www/html/multimedia_toolbox/") { // If this is on local machine.
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
		$_SESSION["local_machine"] = True;
		$local_machine = True;
	}

	$log_message_php = "$document_root" . '/apps/utils/log_message.php';
 	include "$log_message_php";
    $path = "$document_root" . "/log/";
	$postfix = basename(__FILE__) . '.SERVER';
	$msg = print_r($_SERVER, TRUE) . "\n";
	log_message($path, $msg, $postfix);
?>