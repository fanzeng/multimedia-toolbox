<?php
session_start();
// echo session_id();
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	$user_data_dir = "../../data/user_data";
    $json_file = "$user_data_dir/" . $_SESSION["user_id"] . "/framess.json";
    $handle = fopen($json_file, 'w');
    // $json_data = fread($handle, filesize($json_file));
    // $post_string = json_decode($_POST, true);
    $post_string = implode("", $_POST);
    fwrite($handle, $post_string);
	echo "post_string=" . $post_string . "post=" . $_POST;
?>

