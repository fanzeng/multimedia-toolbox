<?php
session_start();
// echo session_id();
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	$user_data_dir = "../../data/user_data";
    $json_file = "$user_data_dir/" . $_SESSION["user_id"] . "/frames.json";
    $handle = fopen($json_file, 'w');
    $post_string =json_encode($_POST, JSON_NUMERIC_CHECK|JSON_PRETTY_PRINT);
    fwrite($handle, $post_string);
	echo "post_string=" . $post_string;
	fclose($handle);
?>

