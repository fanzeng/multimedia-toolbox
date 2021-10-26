<?php
session_start();
// echo session_id();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$userDataPath = '../../data/user_data';
$jsonFile = "$userDataPath/" . $_SESSION['userId'] . '/frames.json';
$handle = fopen($jsonFile, 'r');
$jsonData = fread($handle, filesize($jsonFile));
echo $jsonData;
