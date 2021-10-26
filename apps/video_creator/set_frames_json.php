<?php require_once 'frame.php';

session_start();
// echo session_id();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$userDataPath = '../../data/user_data';
$jsonFile = "$userDataPath/" . $_SESSION['userId'] . '/frames.json';
$handle = fopen($jsonFile, 'w');
$postString = json_encode($_POST, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT);
fwrite($handle, $postString);
fclose($handle);
$jsonObj = json_decode($postString, true);
$_SESSION['arrFrames'] = array();
foreach ($jsonObj['arrFrames'] as $frame) {
    $f = new Frame($frame['order'], $frame['srcFilename'], $frame['numRepetition']);
    array_push($_SESSION['arrFrames'], $f);
}
$_SESSION['sourceImageNum'] = count($_SESSION['arrFrames']);
echo 'sessionString=' . var_dump($jsonObj['arrFrames']);
